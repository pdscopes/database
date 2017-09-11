<?php

namespace MadeSimple\Database\Tests;

use PDO;
use MadeSimple\Database\ConnectorInterface;

class MockConnector implements ConnectorInterface
{
    /**
     * @var \Mockery\Mock|PDO
     */
    protected $mockPdo;

    /**
     * MockConnector constructor.
     *
     * @param \Mockery\Mock|PDO $mockPdo
     */
    public function __construct($mockPdo)
    {
        $this->mockPdo = $mockPdo;
    }

    /**
     * @param array $config
     *
     * @return PDO
     */
    public function connect(array $config)
    {
        return $this->mockPdo;
    }
}