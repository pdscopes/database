<?php

namespace MadeSimple\Database\Tests;

use MadeSimple\Database\CompilerInterface;
use MadeSimple\Database\Connection;

/**
 * Class MockConnection
 *
 * @package Tests
 * @author  Peter Scopes
 */
class MockConnection extends Connection
{
    public function __construct(MockConnector $connector, CompilerInterface $compiler)
    {
        parent::__construct(['driver' => 'mock', 'unique' => uniqid()], $connector, $compiler);
    }
}