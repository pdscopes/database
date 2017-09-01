<?php

namespace MadeSimple\Database\Statement\Table;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Statement;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Class Drop
 *
 * @package MadeSimple\Database\Statement\Table
 * @author  Peter Scopes
 */
class Drop implements Statement
{
    use LoggerAwareTrait;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $table;

    /**
     * Drop constructor.
     *
     * @param Connection      $connection
     * @param LoggerInterface $logger
     */
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->setLogger($logger);
    }

    /**
     * Creates SQL: DROP TABLE $name
     *
     * @param string $name
     *
     * @return static
     */
    public function table($name)
    {
        $this->table = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function toSql()
    {
        return 'DROP TABLE ' . $this->connection->quoteClause($this->table);
    }

    /**
     * @param array|null $parameters
     *
     * @return \PDOStatement
     */
    public function execute(array $parameters = null)
    {
        $sql = $this->toSql();
        $this->logger->debug('Executing drop', ['sql' => $sql]);
        return $this->connection->query($sql);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toSql();
    }
}