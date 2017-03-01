<?php

namespace MadeSimple\Database\QueryBuilder;

use MadeSimple\Database\Connection;
use PDO;

/**
 * Class Insert
 *
 * @package MadeSimple\Database\QueryBuilder
 * @author  Peter Scopes
 */
class Insert extends Statement
{

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string[]
     */
    protected $columns;

    /**
     * @var mixed[][]
     */
    protected $values;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * Insert constructor.
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);

        $this->columns    = [];
        $this->values     = [];
        $this->parameters = [];
    }

    /**
     * @param string $table Database table to insert into
     *
     * @return Insert
     */
    public function into($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param null|string|string[] $columns Columns to provide values for
     *
     * @return Insert
     */
    public function columns($columns = null)
    {
        $columns       = is_string($columns) ? [$columns] : $columns;
        $this->columns = $columns;

        return $this;
    }

    /**
     * @param array $values
     *
     * @return Insert
     */
    public function values(array $values)
    {
        $this->values[]   = array_fill(0, count($values), '?');
        $this->parameters = array_merge($this->parameters, array_values($values));

        return $this;
    }

    /**
     * {@InheritDoc}
     * If successful, clears the values.
     */
    public function execute(array $parameters = null)
    {
        if ($parameters) {
            throw new \RuntimeException('Unsupported Operation.');
        }

        $statement = $this->pdo->prepare($this->toSql());
        $this->bindParameters($statement, $parameters ? : $this->parameters);
        if (false === $statement->execute()) {
            return false;
        }
        $this->values     = [];
        $this->parameters = [];

        return $statement;
    }

    /**
     * @return string
     */
    public function toSql()
    {
        // Add the table
        $sql = sprintf(/** @lang text */
            'INSERT INTO `%s`', trim($this->table, '`')
        );

        // Add the columns
        if (!empty($this->columns)) {
            $sql .= sprintf(' (%s)', implode(',', array_map([Connection::class, 'quoteColumn'], array_unique($this->columns))));
        }

        // Add the values
        if (!empty($this->values)) {
            $values = array_map(function ($el) {
                return implode(',', $el);
            }, $this->values
            );
            $sql .= sprintf("\nVALUES\n(%s)", implode("),\n(", $values));
        }

        return $sql;
    }
}