<?php

namespace MadeSimple\Database\Tests;

use MadeSimple\Database\CompilerInterface;

class CompilableTestCase extends TestCase
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
     * @var \Mockery\Mock|CompilerInterface
     */
    protected $mockCompiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockPdo          = \Mockery::mock(\PDO::class);
        $this->mockPdoStatement = \Mockery::mock(\PDOStatement::class);
        $this->mockConnector    = new MockConnector($this->mockPdo);
        $this->mockCompiler     = \Mockery::mock(CompilerInterface::class);
        $this->mockCompiler->shouldReceive('setConnection')->once();
        $this->mockConnection   = new MockConnection($this->mockConnector, $this->mockCompiler);
    }
}