<?php

namespace MadeSimple\Database\Statement;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Statement;
use PDO;
use PDOStatement;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Class Statement
 *
 * @package MadeSimple\Database\Statement
 * @author  Peter Scopes
 */
abstract class Query implements Statement
{
    use LoggerAwareTrait;

    /**
     * @var Connection
     */
    public $connection;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * Query constructor.
     *
     * @param Connection|null $connection
     * @param LoggerInterface $logger
     */
    public function __construct(Connection $connection = null, LoggerInterface $logger)
    {
        $this->setConnection($connection);
        $this->setLogger($logger);
        $this->parameters = [];
    }

    /**
     * @param Connection $connection
     */
    public function setConnection(Connection $connection = null)
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
        $sql        = $this->toSql();
        $this->logger->debug('Executing query', ['sql' => $sql]);

        try {
            if (empty($parameters)) {
                $statement = $this->connection->query($sql);
            } else {
                $statement = $this->connection->prepare($sql);
                $this->bindParameters($statement, $parameters ? : $this->parameters);
                if (false === $statement->execute()) {
                    return false;
                }
            }

            return $statement;
        }
        catch (\PDOException $e) {
            throw new ExecutionException($e, $sql, $parameters);
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