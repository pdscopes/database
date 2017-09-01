<?php

namespace MadeSimple\Database\Statement\Table;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Statement;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Class Create
 *
 * @package MadeSimple\Database\Statement\Table
 * @author  Peter Scopes
 */
abstract class Create implements Statement
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
     * @var Column[]
     */
    protected $columns = [];

    /**
     * Create constructor.
     *
     * @param Connection      $connection
     * @param LoggerInterface $logger
     * @param string          $name
     */
    public function __construct(Connection $connection, LoggerInterface $logger, $name)
    {
        $this->connection = $connection;
        $this->setLogger($logger);

        $this->name($name);
    }

    /**
     * @param string $name
     */
    public function name($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     *
     * @return \MadeSimple\Database\Statement\Table\Column
     */
    public abstract function column($name);

    /**
     * @param array|null $parameters
     *
     * @return \PDOStatement
     */
    public function execute(array $parameters = null)
    {
        $sql = $this->toSql();
        $this->logger->debug('Executing create', ['sql' => $sql]);
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