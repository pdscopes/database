<?php

namespace MadeSimple\Database\Statement\Table;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Statement;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Class Truncate
 *
 * @package MadeSimple\Database\Statement\Table
 * @author  Peter Scopes
 */
class Truncate implements Statement
{
    use LoggerAwareTrait;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $name;

    /**
     * Truncate constructor.
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
     * @param string $name
     */
    public function table($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function toSql()
    {
        return 'TRUNCATE TABLE ' . $this->name;
    }

    /**
     * @param array|null $parameters
     *
     * @return \PDOStatement
     */
    public function execute(array $parameters = null)
    {
        $sql = $this->toSql();
        $this->logger->debug('Executing truncate', ['sql' => $sql]);
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