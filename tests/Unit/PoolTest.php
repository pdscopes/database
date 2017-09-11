<?php

namespace MadeSimple\Database\Tests\Unit;

use MadeSimple\Database\CompilerInterface;
use MadeSimple\Database\Connection;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Tests\MockConnection;
use MadeSimple\Database\Tests\MockConnector;
use MadeSimple\Database\Tests\TestCase;

class PoolTest extends TestCase
{
    /**
     * @var \Mockery\Mock|\PDO
     */
    private $mockPdo;

    /**
     * @var MockConnector
     */
    private $mockConnector;

    /**
     * @var \Mockery\Mock|CompilerInterface
     */
    private $mockCompiler;

    /**
     * @var \Mockery\Mock|Connection
     */
    private $connection1;

    /**
     * @var \Mockery\Mock|Connection
     */
    private $connection2;

    protected function setUp()
    {
        parent::setUp();

        $this->mockPdo       = \Mockery::mock(\PDO::class);
        $this->mockConnector = new MockConnector($this->mockPdo);
        $this->mockCompiler  = \Mockery::mock(CompilerInterface::class);
        $this->connection1   = new MockConnection($this->mockConnector, $this->mockCompiler);
        $this->connection2   = new MockConnection($this->mockConnector, $this->mockCompiler);
    }

    /**
     * Test Pool construction.
     */
    public function testConstruct()
    {
        $pool = new Pool($this->connection1, $this->connection2);
        $this->assertEquals($this->connection1->config('unique'), $pool->get()->config('unique'));
        $this->assertNotEquals($this->connection2->config('unique'), $pool->get()->config('unique'));
    }

    /**
     * Test construct throws exception.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Pool only accepts Connections
     */
    public function testConstructThrowsInvalidArgumentException()
    {
        new Pool('Not A Connection');
    }

    /**
     * Test pool set default.
     */
    public function testSetDefault()
    {
        $pool = new Pool(['mysql' => $this->connection1, 'sqlite' => $this->connection2]);
        $this->assertEquals($this->connection1->config('unique'), $pool->get()->config('unique'));
        $pool->setDefault('sqlite');
        $this->assertEquals($this->connection2->config('unique'), $pool->get()->config('unique'));
    }

    /**
     * Test pool get.
     */
    public function testGet()
    {
        $pool = new Pool(['mysql' => $this->connection1, 'sqlite' => $this->connection2]);
        $this->assertEquals($this->connection1->config('unique'), $pool->get()->config('unique'));
        $this->assertEquals($this->connection1->config('unique'), $pool->get('mysql')->config('unique'));
        $this->assertEquals($this->connection2->config('unique'), $pool->get('sqlite')->config('unique'));
    }

    /**
     * Test pool set.
     */
    public function testSet()
    {
        $pool = new Pool(['mysql' => $this->connection1]);
        $pool->set('sqlite', $this->connection2);
        $this->assertEquals($this->connection1->config('unique'), $pool->get()->config('unique'));
        $this->assertEquals($this->connection1->config('unique'), $pool->get('mysql')->config('unique'));
        $this->assertEquals($this->connection2->config('unique'), $pool->get('sqlite')->config('unique'));
    }
}