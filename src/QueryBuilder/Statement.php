<?php

namespace MadeSimple\Database\QueryBuilder;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Exception\ExecutionException;
use PDO;
use PDOStatement;

/**
 * Class Statement
 *
 * @package MadeSimple\Database\QueryBuilder
 * @author  Peter Scopes
 */
abstract class Statement
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * Statement constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->setConnection($connection);
        $this->parameters = [];
    }

    /**
     * @param Connection $connection
     */
    protected function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }


    /**
     * @param null|string $name  Name of the parameter (used in select query)
     * @param mixed       $value Value of the parameter (must be convertible to string)
     *
     * @return static
     */
    public function setParameter($name, $value)
    {
        if (null !== $name) {
            $this->parameters[$name] = $value;
        } else {
            $this->parameters[] = $value;
        }

        return $this;
    }

    /**
     * @param array $parameters Associated mapping of parameter name to value
     *
     * @return static
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter(is_numeric($name) ? null : $name, $value);
        }

        return $this;
    }

    /**
     * @param null|array $parameters Override the parameters already passed to the statement
     *
     * @return PDOStatement|false FALSE on failure
     */
    public function execute(array $parameters = null)
    {
        $parameters = $parameters ? : $this->parameters;

        try {
            if (empty($parameters)) {
                $statement = $this->connection->query($this->toSql());
            } else {
                $statement = $this->connection->prepare($this->toSql());
                $this->bindParameters($statement, $parameters ? : $this->parameters);
                if (false === $statement->execute()) {
                    return false;
                }
            }

            return $statement;
        }
        catch (\PDOException $e) {
            throw new ExecutionException($e, $this->toSql(), $parameters);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toSql();
    }

    /**
     * Convert the select object into the SQL.
     *
     * @return string
     */
    public abstract function toSql();

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function quote($value)
    {
        switch (gettype($value)) {
            case 'array':
                $value = array_map([$this, 'quote'], $value);

                return implode(',', $value);

            case 'integer':
                return $value;

            case 'double':
                return $value;

            case 'boolean':
                return $value;

            case 'NULL':
                return 'NULL';

            default:
                return $this->connection->quote($value, PDO::PARAM_STR);
        }
    }

    /**
     * Binds the current values of the given array of parameters to the given PDOStatement
     *
     * @param PDOStatement $statement
     * @param array        $parameters
     */
    protected function bindParameters(PDOStatement $statement, array $parameters)
    {
        $index = 0;
        foreach ($parameters as $name => $value) {
            $name = is_int($name) ? ++$index : ':' . $name;
            switch (gettype($value)) {
                case 'array':
                    $value = array_map([$this, 'quote'], $value);
                    $value = implode(',', $value);
                    $statement->bindValue($name, $value);
                    break;

                case 'integer':
                    $statement->bindValue($name, $value, PDO::PARAM_INT);
                    break;

                case 'double':
                    $statement->bindValue($name, $value, PDO::PARAM_INT);
                    break;

                case 'boolean':
                    $statement->bindValue($name, $value, PDO::PARAM_BOOL);
                    break;

                case 'NULL':
                    $statement->bindValue($name, $value, PDO::PARAM_NULL);
                    break;

                default:
                    $statement->bindValue($name, $value, PDO::PARAM_STR);
                    break;
            }
        }
    }
}