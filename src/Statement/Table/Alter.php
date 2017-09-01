<?php

namespace MadeSimple\Database\Statement\Table;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Statement;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Class Alter
 *
 * @package MadeSimple\Database\Statement\Table
 * @author  Peter Scopes
 */
class Alter implements Statement
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
     * @var string
     */
    protected $action;

    /**
     * Alter constructor.
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
     * @param string $action
     */
    public function action($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function toSql()
    {
        $sql = 'ALTER TABLE ' . $this->name;
        $sql .= ' ' . $this->action;
        return $sql;
    }

    /**
     * @param array|null $parameters
     *
     * @return \PDOStatement
     */
    public function execute(array $parameters = null)
    {
        $sql = $this->toSql();
        $this->logger->debug('Executing alter', ['sql' => $sql]);
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