<?php

namespace Tests\Unit;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Pool;
use Tests\TestCase;

class PoolTest extends TestCase
{
    /**
     * @var \Mockery\Mock|\PDO
     */
    private $mockPdo;

    /**
     * @var Connection
     */
    private $connection1;

    /**
     * @var Connection
     */
    private $connection2;

    protected function setUp()
    {
        parent::setUp();

        $this->mockPdo     = \Mockery::mock(\PDO::class);
        $this->connection1 = new \MadeSimple\Database\MySQL\Connection($this->mockPdo);
        $this->connection2 = new \MadeSimple\Database\SQLite\Connection($this->mockPdo);
    }

    /**
     * Test Pool construction.
     */
    public function testConstruct()
    {
        $pool = new Pool($this->connection1, $this->connection2);
        $this->assertEquals($this->connection1, $pool->get());
        $this->assertNotEquals($this->connection2, $pool->get());
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
        $this->assertEquals($this->connection1, $pool->get());
        $pool->setDefault('sqlite');
        $this->assertEquals($this->connection2, $pool->get());
    }

    /**
     * Test pool get.
     */
    public function testGet()
    {
        $pool = new Pool(['mysql' => $this->connection1, 'sqlite' => $this->connection2]);
        $this->assertEquals($this->connection1, $pool->get());
        $this->assertEquals($this->connection1, $pool->get('mysql'));
        $this->assertEquals($this->connection2, $pool->get('sqlite'));
    }

    /**
     * Test pool set.
     */
    public function testSet()
    {
        $pool = new Pool(['mysql' => $this->connection1]);
        $pool->set('sqlite', $this->connection2);
        $this->assertEquals($this->connection1, $pool->get());
        $this->assertEquals($this->connection1, $pool->get('mysql'));
        $this->assertEquals($this->connection2, $pool->get('sqlite'));
    }
}