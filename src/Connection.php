<?php

namespace MadeSimple\Database;

use MadeSimple\Database\Connection\MySQL;
use MadeSimple\Database\Connection\SQLite;
use MadeSimple\Database\QueryBuilder as Qb;

/**
 * Class Connection
 *
 * @package MadeSimple\Database
 * @author  Peter Scopes
 */
class Connection
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $columnQuote;

    /**
     * @var int
     */
    protected $transactions;

    /**
     * Connection constructor.
     *
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;

        switch ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            case 'mysql':
                $this->columnQuote = MySQL::$columnQuote;
                break;
            case 'sqlite':
                $this->columnQuote = SQLite::$columnQuote;
                break;
        }
    }

    /**
     * @return Qb\Select
     */
    public function select()
    {
        return new Qb\Select($this);
    }

    /**
     * @return Qb\Insert
     */
    public function insert()
    {
        return new Qb\Insert($this);
    }

    /**
     * @return Qb\Update
     */
    public function update()
    {
        return new Qb\Update($this);
    }

    /**
     * @return Qb\Delete
     */
    public function delete()
    {
        return new Qb\Delete($this);
    }


    /**
     * @return bool
     * @see \PDO::beginTransaction()
     */
    public function beginTransaction()
    {
        if ($this->transactions > 0) {
            $this->transactions++;

            return true;
        }

        if ($this->pdo->beginTransaction()) {
            $this->transactions = 1;

            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @see \PDO::rollBack()
     */
    public function rollBack()
    {
        if ($this->transactions < 1) {
            return false;
        }

        if (--$this->transactions == 0) {
            return $this->pdo->rollBack();
        };

        return true;
    }

    /**
     * @return bool
     * @see \PDO::commit()
     */
    public function commit()
    {
        if ($this->transactions < 1) {
            return false;
        }

        if (--$this->transactions == 0) {
            return $this->pdo->commit();
        };

        return true;
    }

    /**
     * @param string $name [optional]
     *                     Name of the sequence object from which the ID should be returned.
     *
     * @return string
     * @see \PDO::lastInsertId()
     */
    public function lastInsertId($name = null)
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * @param string $statement      This must be a valid SQL statement for the target database server.
     * @param array  $driver_options [optional]
     *                               This array holds one or more key=>value pairs to set attribute values for the
     *                               PDOStatement object that this method returns. You would most commonly use this to
     *                               set the PDO::ATTR_CURSOR value to PDO::CURSOR_SCROLL to request a scrollable
     *                               cursor. Some drivers have driver specific options that may be set at prepare-time.
     *
     * @return \PDOStatement
     * @see \PDO::prepare()
     */
    public function prepare($statement, array $driver_options = array())
    {
        return $this->pdo->prepare($statement, $driver_options);
    }

    /**
     * @param string $statement The SQL statement to prepare and execute.
     *                          Data inside the query should be properly escaped.
     *
     * @return \PDOStatement
     * @see \PDO::query()
     */
    public function query($statement)
    {
        return $this->pdo->query($statement);
    }

    /**
     * @param string $string        The string to be quoted
     * @param int    $parameterType [optional] Provides a data type hint for drivers that have alternate quoting styles.
     *
     * @return string
     * @see \PDO::quote()
     */
    public function quote($string, $parameterType = \PDO::PARAM_STR)
    {
        return $this->pdo->quote($string, $parameterType);
    }


    /**
     * @param string $clause
     *
     * @return string
     */
    public function quoteClause($clause)
    {
        $clause = trim($clause);

        // Check for sub clauses
        if (false !== ($pos = strpos($clause, '('))) {
            $modClause  = $clause;
            $subClauses = [];
            while (false !== ($pos = strpos($modClause, '('))) {
                $depth = 1;
                for ($i = $pos + 1; $i < strlen($modClause) && $depth > 0; $i++) {
                    if ('(' === $modClause{$i}) {
                        $depth++;
                    }
                    if (')' === $modClause{$i}) {
                        $depth--;
                    }
                }

                // Extract the sub clause
                $subClause = substr($modClause, $pos + 1, ($i - $pos) - 2);

                // Is this a function?
                $function = '';
                if ($pos > 0 && ' ' !== $modClause{$pos - 1}) {
                    $pos      = strrpos(substr($modClause, 0, $pos), ' ') + 1;
                    $function = substr($modClause, $pos, ($i - $pos) - (strlen($subClause) + 2));
                }

                $count                    = count($subClauses);
                $modClause                = str_replace($function . '(' . $subClause . ')', '$' . $count, $modClause);
                $subClauses['$' . $count] = $function . '(' . static::quoteClause($subClause) . ')';
            }

            $modClause = static::quoteClause($modClause);
            foreach ($subClauses as $k => $subClause) {
                $modClause = str_replace($k, $subClause, $modClause);
            }

            return $modClause;
        }

        // Handle operators
        $arithmeticOperators = ['+', '-', '*', '/', '%', ','];
        $comparisonOperators = ['>=', '<=', '!=', '<>', '!<', '!>', '=', '>', '<'];
        $logicalOperators    =
            ['AND', '&&', 'OR', '||', 'IS', 'ALL', 'ANY', 'BETWEEN', 'EXISTS', 'IN', 'LIKE', 'NOT', 'UNIQUE'];
        foreach (array_merge($arithmeticOperators, $comparisonOperators, $logicalOperators) as $operator) {
            if (false !== strpos($clause, $operator)) {
                return implode(' ' . $operator .
                    ' ', array_map([$this, 'quoteClause'], explode($operator, $clause))
                );
            }
        }

        // Check that this should be quoted
        $logicalValues = ['NULL', 'TRUE', 'FALSE'];
        if (
            0 === strpos($clause, ':') ||
            0 === strpos($clause, '"') ||
            0 === strpos($clause, '\'') ||
            0 === strpos($clause, '$') ||
            in_array($clause, $logicalValues) ||
            '?' === $clause ||
            is_numeric($clause)
        ) {
            return $clause;
        }

        return static::quoteColumn($clause);
    }

    /**
     * Applies quotes to the column.
     *
     * @param string $column
     *
     * @return string
     */
    public function quoteColumn($column)
    {
        $str = $column;

        // Only consider non function parts
        if ((false !== $lBracket= strrpos($column, '(')) && (false !== $rBracket = strpos($column, ')'))) {
            $str = substr($column, $lBracket + 1, $rBracket - $lBracket - 1);
        } elseif (false !== $lSpace = strrpos($column, ' ')) {
            $str = substr($column, $lSpace + 1);
        }

        // Apply quotes
        $quoted = implode('.', array_map([$this, 'applyQuote'], explode('.', $str)));

        return str_replace($str, $quoted, $column);
    }

    /**
     * @param string $column
     *
     * @return string
     */
    protected function applyQuote($column)
    {
        $column = trim($column, $this->columnQuote);

        return '*' == $column ? '*' : $this->columnQuote . $column . $this->columnQuote;
    }
}