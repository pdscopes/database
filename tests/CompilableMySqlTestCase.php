<?php

namespace MadeSimple\Database\Tests;

use MadeSimple\Database\Compiler;
use MadeSimple\Database\CompilerInterface;

class CompilableMySqlTestCase extends TestCase
{
    /**
     * @var MockConnection
     */
    protected $mockConnection;

    /**
     * @var \Mockery\Mock|\PDO
     */
    protected $mockPdo;

    /**
     * @var \Mockery\Mock|\PDOStatement
     */
    protected $mockPdoStatement;

    /**
     * @var MockConnector
     */
    protected $mockConnector;

    /**
     * @var CompilerInterface
     */
    protected $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockPdo          = \Mockery::mock(\PDO::class);
        $this->mockPdoStatement = \Mockery::mock(\PDOStatement::class);
        $this->mockConnector    = new MockConnector($this->mockPdo);
        $this->compiler         = new Compiler\MySQL();
        $this->mockConnection   = new MockConnection($this->mockConnector, $this->compiler);
    }
}