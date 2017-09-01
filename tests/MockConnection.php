<?php

namespace Tests;

use MadeSimple\Database\Connection;
use Psr\Log\NullLogger;

/**
 * Class MockConnection
 *
 * @package Tests
 * @author  Peter Scopes
 */
class MockConnection extends Connection
{
    public function __construct(\PDO $pdo)
    {
        $this->pdo         = $pdo;
        $this->logger      = new NullLogger;
        $this->columnQuote = '`';
    }

    public  function create($name, \Closure $callable)
    {
        // TODO: Implement table() method.
    }
}