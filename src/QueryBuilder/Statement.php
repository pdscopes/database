<?php

namespace MadeSimple\Database\QueryBuilder;

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
     * @var PDO
     */
    protected $pdo;

    /**
     * Statement constructor.
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->setPdo($pdo);
    }

    /**
     * @param PDO $pdo
     */
    protected function setPdo(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param null|array $parameters Override the parameters already passed to the statement
     *
     * @return PDOStatement|false FALSE on failure
     */
    public abstract function execute(array $parameters = null);

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
                return $this->pdo->quote($value, PDO::PARAM_STR);
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
            $name = is_int($name) ? ++$index : sprintf(':%s', $name);
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