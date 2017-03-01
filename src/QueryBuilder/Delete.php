<?php

namespace MadeSimple\Database\QueryBuilder;

use PDO;

/**
 * Class Delete
 *
 * @package MadeSimple\Database\QueryBuilder
 * @author  Peter Scopes
 */
class Delete extends Statement
{
    /**
     * @var string[]
     */
    protected $table;

    /**
     * @var string[]
     */
    protected $where;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * Delete constructor.
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);

        $this->table      = [];
        $this->where      = [];
        $this->parameters = [];
    }


    /**
     * @param string      $table Database table name
     * @param null|string $alias Alias for the table name
     *
     * @return Delete
     */
    public function from($table, $alias = null)
    {
        $this->table = [(is_null($alias) ? $table : $alias), $table];

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return Delete
     */
    public function column($name, $value)
    {
        $this->where(sprintf('%s = ?', $name), $value);

        return $this;
    }

    /**
     * @param string      $clause    A where clause
     * @param array|mixed $parameter A single, array of, or associated mapping of parameters
     *
     * @return Delete
     */
    public function where($clause, $parameter = null)
    {
        $this->where[] = $clause;
        if (!is_null($parameter)) {
            $this->setParameters(!is_array($parameter) ? [$parameter] : $parameter);
        }

        return $this;
    }

    /**
     * @param array $parameters Associated mapping of parameter name to value
     *
     * @return Delete
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            $parameters[$name] = $value;//$this->quote($value);
        }
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    /**
     * @param array $columns Associated map
     *
     * @return Delete
     */
    public function columns($columns)
    {
        foreach ($columns as $name => $value) {
            $this->where(sprintf('%s = ?', $name), $value);
        }

        return $this;
    }

    /**
     * Given clauses are OR'd together.
     *
     * @param string[]    $clauses   Array of where clauses
     * @param array|mixed $parameter A single, array of, or associated mapping of parameters
     *
     * @return Delete
     */
    public function orWhere(array $clauses, $parameter = null)
    {
        $this->where[] = sprintf('((%s))', implode(') OR (', $clauses));
        if (!is_null($parameter)) {
            $this->setParameters(!is_array($parameter) ? [$parameter] : $parameter);
        }

        return $this;
    }

    /**
     * e.g.:
     * ['AND', [
     *      ['OR', [
     *          'inner OR clause 1',
     *          'inner OR clause 2',
     *      ]],
     *      'outer AND clause'
     * ]]
     *
     * ((((inner OR clause 1) OR (inner OR clause 2))) AND (outer AND clause))
     *
     * @param string $conjunction
     * @param array  $tree
     *
     * @return Delete
     */
    public function treeWhere($conjunction, array $tree)
    {
        $this->where[] = $this->collapseTree([$conjunction, $tree]);

        return $this;
    }

    /**
     * @param array $tree
     *
     * @return string
     */
    protected function collapseTree(array $tree)
    {
        list($conjunction, $clauses) = $tree;

        foreach ($clauses as $k => $clause) {
            if (is_array($clause) && in_array(reset($clause), ['OR', 'AND'])) {
                $clauses[$k] = $this->collapseTree($clause);
            }
        }

        return sprintf('((%s))', implode(sprintf(') %s (', $conjunction), $clauses));
    }

    /**
     * @param string $name  Name of the parameter (used in select query)
     * @param mixed  $value Value of the parameter (must be convertible to string)
     *
     * @return Delete
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * {@InheritDoc}
     * If successful, clears the parameters.
     */
    public function execute(array $parameters = null)
    {
        $statement = null;
        if (empty($this->parameters) && empty($parameters)) {
            $statement = $this->pdo->query($this->toSql());
        } else {
            $statement = $this->pdo->prepare($this->toSql());
            $this->bindParameters($statement, $parameters ? : $this->parameters);
            if (false === $statement->execute()) {
                return false;
            }
        }
        $this->parameters = [];

        return $statement;
    }

    /**
     * @return string
     */
    public function toSql()
    {
        // Set the table
        $sql = $this->table[0] == $this->table[1] ?
            sprintf(/**@lang text */
                "DELETE FROM `%s`\n", trim($this->table[1], '`')
            ) :
            sprintf(/**@lang text */
                "DELETE %2\$s FROM `%1\$s` AS `%2\$s`\n", trim($this->table[1], '`'), trim($this->table[0], '`')
            );

        // If where
        if (!empty($this->where)) {
            $sql .= sprintf("WHERE\n    %s\n", implode("\nAND ", $this->where));
        }

        return $sql;
    }
}