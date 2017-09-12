<?php

namespace MadeSimple\Database\Tests\Unit;

use MadeSimple\Database\CompilerInterface;
use MadeSimple\Database\Query;
use MadeSimple\Database\Statement;
use MadeSimple\Database\Tests\MockConnection;
use MadeSimple\Database\Tests\MockConnector;
use MadeSimple\Database\Tests\TestCase;

class ConnectionTest extends TestCase
{
    /**
     * @var \Mockery\Mock|\PDO
     */
    private $mockPdo;

    /**
     * @var \Mockery\Mock|\PDOStatement
     */
    private $mockPdoStatement;

    /**
     * @var MockConnector
     */
    private $mockConnector;

    /**
     * @var \Mockery\Mock|CompilerInterface
     */
    private $mockCompiler;

    protected function setUp()
    {
        parent::setUp();

        $this->mockPdo          = \Mockery::mock(\PDO::class);
        $this->mockPdoStatement = \Mockery::mock(\PDOStatement::class);
        $this->mockConnector    = new MockConnector($this->mockPdo);
        $this->mockCompiler     = \Mockery::mock(CompilerInterface::class);
        $this->mockCompiler->shouldReceive('setConnection')->once();
    }


    /**
     * Test config returns correct value.
     */
    public function testConfig()
    {
        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $this->assertEquals('mock', $connection->config('driver'));
    }

    /**
     * Test config returns default value when not found.
     */
    public function testConfigReturnsDefault()
    {
        $unique     = uniqid();
        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $this->assertEquals($unique, $connection->config('foobar', $unique));
    }

    /**
     * Test connect stores the PDO provided by MockConnector.
     */
    public function testConnect()
    {
        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $connection->connect();
        $this->assertEquals($this->mockPdo, $connection->pdo);
    }

    /**
     * Test connection passes the raw SQL through to PDO object.
     */
    public function testRawQuery()
    {
        $this->mockPdo->shouldReceive('query')->once()->with('SQL STRING')->andReturn($this->mockPdoStatement);

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $this->assertEquals($this->mockPdoStatement, $connection->rawQuery('SQL STRING'));
    }

    /**
     * Test connection returns a Query\Select object.
     */
    public function testSelect()
    {
        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $select     = $connection->select();

        $this->assertInstanceOf(Query\Select::class, $select);
        $this->assertEquals($connection, $select->connection);
    }

    /**
     * Test connection insert.
     */
    public function testInsert()
    {
        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $select     = $connection->insert();

        $this->assertInstanceOf(Query\Insert::class, $select);
        $this->assertEquals($connection, $select->connection);
    }

    /**
     * Test connection update.
     */
    public function testUpdate()
    {
        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $select     = $connection->update();

        $this->assertInstanceOf(Query\Update::class, $select);
        $this->assertEquals($connection, $select->connection);
    }

    /**
     * Test connection delete.
     */
    public function testDelete()
    {
        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $select     = $connection->delete();

        $this->assertInstanceOf(Query\Delete::class, $select);
        $this->assertEquals($connection, $select->connection);
    }




    /**
     * Test connection query.
     */
    public function testQuery()
    {
        $this->mockCompiler->shouldReceive('compileQuerySelect')->once()->andReturn(['SQL', []]);
        $this->mockPdo->shouldReceive('prepare')->once()->with('SQL')->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        list($pdoStatement) = $connection->query(function (Query\Select $select) {});

        $this->assertEquals($this->mockPdoStatement, $pdoStatement);
    }

    /**
     * Test connection throws exception when query is given an anonymous
     * function with the incorrect number of parameters.
     */
    public function testQueryThrowsExceptionIncorrectParameterNumber()
    {
        $this->expectException(\ReflectionException::class);

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $connection->query(function ($a, $b) {});
    }

    /**
     * Test connection throws exception with query is given an anonymous
     * function with the wrong parameter type.
     */
    public function testQueryThrowsExceptionInvalidParameterType()
    {
        $this->expectException(\ReflectionException::class);

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $connection->query(function (string $a) {});
    }

    /**
     * Test connection statement.
     */
    public function testStatement()
    {
        $this->mockCompiler->shouldReceive('compileStatementCreateTable')->once()->andReturn(['SQL', []]);
        $this->mockPdo->shouldReceive('prepare')->once()->with('SQL')->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        list($pdoStatement) = $connection->statement(function (Statement\CreateTable $create) {});

        $this->assertEquals($this->mockPdoStatement, $pdoStatement);
    }

    /**
     * Test connection throws exception when statement is given an anonymous
     * function with the incorrect number of parameters.
     */
    public function testStatementThrowsExceptionIncorrectParameterNumber()
    {
        $this->expectException(\ReflectionException::class);

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $connection->statement(function ($a, $b) {});
    }

    /**
     * Test connection throws exception with statement is given an anonymous
     * function with the wrong parameter type.
     */
    public function testStatementThrowsExceptionInvalidParameterType()
    {
        $this->expectException(\ReflectionException::class);

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $connection->statement(function (string $a) {});
    }




    /**
     * Test connection begin transaction.
     */
    public function testBeginTransaction()
    {
        $this->mockPdo->shouldReceive('beginTransaction')->once()->andReturn(true);
        $this->mockPdo->shouldReceive('commit')->once()->andReturn(true);

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $this->assertTrue($connection->beginTransaction());
        $this->assertTrue($connection->beginTransaction());
        $this->assertTrue($connection->commit());
        $this->assertTrue($connection->beginTransaction());
        $this->assertTrue($connection->commit());
        $this->assertTrue($connection->beginTransaction());
        $this->assertTrue($connection->commit());
        $this->assertTrue($connection->commit());
    }

    /**
     * Test connection in transaction.
     */
    public function testInTransaction()
    {
        $this->mockPdo->shouldReceive('inTransaction')->once()->andReturn(true);
        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $this->assertTrue($connection->inTransaction());
    }

    /**
     * Test connection begin transaction - PDO failure.
     */
    public function testBeginTransactionPdoFailure()
    {
        $this->mockPdo->shouldReceive('beginTransaction')->once()->andReturn(false);

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $this->assertFalse($connection->beginTransaction());
    }

    /**
     * Test connection rollBack.
     */
    public function testRollBack()
    {
        $this->mockPdo->shouldReceive('beginTransaction')->once()->andReturn(true);
        $this->mockPdo->shouldReceive('rollBack')->once()->andReturn(true);

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $this->assertTrue($connection->beginTransaction());
        $this->assertTrue($connection->rollBack());
    }

    /**
     * Test connection rollBack - PDO failure.
     */
    public function testRollBackPdoFailure()
    {
        $this->mockPdo->shouldReceive('beginTransaction')->once()->andReturn(true);
        $this->mockPdo->shouldReceive('rollBack')->once()->andReturn(false);

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $this->assertTrue($connection->beginTransaction());
        $this->assertFalse($connection->rollBack());
    }

    /**
     * Test connection rollBack - no transaction.
     */
    public function testRollBackNoTransaction()
    {
        $this->mockPdo->shouldReceive('rollBack')->never();

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $this->assertFalse($connection->rollBack());
    }

    /**
     * Test connection commit.
     */
    public function testCommit()
    {
        $this->mockPdo->shouldReceive('beginTransaction')->once()->andReturn(true);
        $this->mockPdo->shouldReceive('commit')->once()->andReturn(true);

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $this->assertTrue($connection->beginTransaction());
        $this->assertTrue($connection->commit());
    }

    /**
     * Test connection commit - PDO failure.
     */
    public function testCommitPdoFailure()
    {
        $this->mockPdo->shouldReceive('beginTransaction')->once()->andReturn(true);
        $this->mockPdo->shouldReceive('commit')->once()->andReturn(false);

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $this->assertTrue($connection->beginTransaction());
        $this->assertFalse($connection->commit());
    }

    /**
     * Test connection commit - no transaction.
     */
    public function testCommitNoTransaction()
    {
        $this->mockPdo->shouldReceive('commit')->never();

        $connection = new MockConnection($this->mockConnector, $this->mockCompiler);
        $this->assertFalse($connection->commit());
    }
}