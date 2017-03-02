<?php

namespace Tests;

use MadeSimple\Database\Connection;

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
        $this->columnQuote = '`';
    }
}