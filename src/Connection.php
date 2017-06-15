<?php

namespace MadeSimple\Database;

/**
 * Class Connection
 *
 * @package MadeSimple\Database
 * @author  Peter Scopes
 */
abstract class Connection
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
     * @param \PDO $pdo
     *
     * @return Connection
     */
    public static function factory(\PDO $pdo)
    {
        switch ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            case 'mysql':
                return new MySQL\Connection($pdo);
            case 'sqlite':
                return new SQLite\Connection($pdo);

            default:
                throw new \InvalidArgumentException('Unsupported PDO driver');
        }
    }

    /**
     * Connection constructor.
     *
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Retrieve a database connection attribute.
     *
     * @param int $attribute
     *
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        return $this->pdo->getAttribute($attribute);
    }

    /**
     * Set an attribute.
     *
     * @param int   $attribute
     * @param mixed $value
     *
     * @return bool
     */
    public function setAttribute($attribute, $value)
    {
        return $this->pdo->setAttribute($attribute, $value);
    }

    /**
     * @param string   $name
     * @param \Closure $callable
     *
     * @return Statement
     */
    public abstract function create($name, \Closure $callable);

    /**
     * @param \Closure|null $callable
     *
     * @return Statement\Table\Alter
     */
    public function alter($callable = null)
    {
        $alter = new Statement\Table\Alter($this);
        if ($callable instanceof \Closure) {
            call_user_func_array($callable, [$alter]);
        }
        return $alter;
    }

    /**
     * @return Statement\Table\Truncate
     */
    public function truncate()
    {
        return new Statement\Table\Truncate($this);
    }

    /**
     * @return Statement\Table\Drop
     */
    public function drop()
    {
        return new Statement\Table\Drop($this);
    }

    /**
     * @return Statement\Query\Select
     */
    public function select()
    {
        return new Statement\Query\Select($this);
    }

    /**
     * @return Statement\Query\Insert
     */
    public function insert()
    {
        return new Statement\Query\Insert($this);
    }

    /**
     * @return Statement\Query\Update
     */
    public function update()
    {
        return new Statement\Query\Update($this);
    }

    /**
     * @return Statement\Query\Delete
     */
    public function delete()
    {
        return new Statement\Query\Delete($this);
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
     * @see \PDO::inTransaction()
     */
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
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
     * Execute an SQL statement and return the number of affected rows.
     *
     * @param string $statement The SQL statement to prepare and execute.
     *                          Data inside the query should be properly escaped.
     *
     * @return int
     * @see \PDO::exec()
     */
    public function exec($statement)
    {
        return $this->pdo->exec($statement);
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
        try {
            return $this->pdo->query($statement);
        } catch (\PDOException $e) {
            throw new \PDOException('Failed to execute: ' . $statement, 0, $e);
        }
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
        $words    = str_word_count($clause, 2, ':_0123456789'/*.$this->columnQuote*/);
        $position = 0;
        $quoted   = '';
        foreach ($words as $location => $word) {
            if (':' === $word{0} || '\'' === $word{0} || '"' === $word{0}) {
                continue;
            }
            $quoted .= substr($clause, $position, ($location - $position));
            $quoted .= strtoupper($word) === $word ? $word : $this->applyQuote($word);
            $position = $location + strlen($word);
        }
        $quoted .= substr($clause, $position);

        return $quoted;
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