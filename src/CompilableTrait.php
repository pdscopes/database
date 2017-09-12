<?php

namespace MadeSimple\Database;

use PDO;
use PDOException;
use PDOStatement;
use Psr\Log\LoggerAwareTrait;

trait CompilableTrait
{
    use CompilerAwareTrait, LoggerAwareTrait;

    /**
     * @var array
     */
    protected $statement;

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var null|PDOStatement
     */
    protected $pdoStatement;

    /**
     * Create a new and clean instance.
     *
     * @return static
     */
    public function newQuery()
    {
        return new static($this->connection);
    }

    /**
     * @param PDO $pdo
     */
    public function setPdo(PDO $pdo)
    {
        $this->pdo = $pdo;
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
    public function toSql()
    {
        return $this->buildSql($this->statement)[0];
    }

    /**
     * Builds the SQL and bindings and returns them.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    public abstract function buildSql(array $statement);


    /**
     * Execute the query and store the resulting PDOStatement.
     *
     * @param null|string $sql
     * @param array|null  $bindings
     *
     * @return static
     */
    public function query($sql = null, array $bindings = null)
    {
        if (null === $sql) {
            list($sql, $bindings) = $this->buildSql($this->statement);
            $this->tidyAfterExecution();
        }
        list($this->pdoStatement) = $this->statement($sql, $bindings);

        return $this;
    }

    /**
     * Execute the query and return the resulting PDOStatement and execution time.
     *
     * @param null|string $sql
     * @param array|null  $bindings
     *
     * @return array [PDOStatement, float]
     */
    public function statement($sql = null, array $bindings = null)
    {
        $start = microtime(true);
        if (null === $sql) {
            list($sql, $bindings) = $this->buildSql($this->statement);
            $this->tidyAfterExecution();
        }
        try {
            $pdoStatement = $this->pdo->prepare($sql);
            foreach ($bindings as $key => $value) {
                $pdoStatement->bindValue(
                    is_int($key) ? $key + 1 : $key,
                    $value,
                    is_int($value) | is_bool($value) ? PDO::PARAM_INT : PDO::PARAM_STR
                );
            }
            $this->logger->debug('Executing SQL: "' . $sql . '"');
            $pdoStatement->execute();

            return [$pdoStatement, microtime(true) - $start];
        }
        catch (PDOException $exception) {
            throw new \RuntimeException('PDO Exception: "' . $sql . '"', 1, $exception);
        }
    }


    /**
     * Called during query and statement. Use to tidy the builder ready for the
     * next next query or statement is called.
     *
     * @see CompilableTrait::query()
     * @see CompilableTrait::statement()
     */
    protected function tidyAfterExecution()
    {
    }
}