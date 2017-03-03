<?php

namespace Tests\Unit;

use MadeSimple\Database\QueryBuilder as Qb;
use Tests\MockConnection;
use Tests\TestCase;

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

    protected function setUp()
    {
        parent::setUp();

        $this->mockPdo          = \Mockery::mock(\PDO::class);
        $this->mockPdoStatement = \Mockery::mock(\PDOStatement::class);
    }


    /**
     * Test connection select.
     */
    public function testSelect()
    {
        $select = (new MockConnection($this->mockPdo))->select();
        $this->assertInstanceOf(Qb\Select::class, $select);
    }

    /**
     * Test connection insert.
     */
    public function testInsert()
    {
        $select = (new MockConnection($this->mockPdo))->insert();
        $this->assertInstanceOf(Qb\Insert::class, $select);
    }

    /**
     * Test connection update.
     */
    public function testUpdate()
    {
        $select = (new MockConnection($this->mockPdo))->update();
        $this->assertInstanceOf(Qb\Update::class, $select);
    }

    /**
     * Test connection delete.
     */
    public function testDelete()
    {
        $select = (new MockConnection($this->mockPdo))->delete();
        $this->assertInstanceOf(Qb\Delete::class, $select);
    }


    /**
     * Test connection begin transaction.
     */
    public function testBeginTransaction()
    {
        $this->mockPdo->shouldReceive('beginTransaction')->once()->andReturn(true);
        $this->mockPdo->shouldReceive('commit')->once()->andReturn(true);

        $connection = new MockConnection($this->mockPdo);
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
     * Test connection begin transaction - PDO failure.
     */
    public function testBeginTransactionPdoFailure()
    {
        $this->mockPdo->shouldReceive('beginTransaction')->once()->andReturn(false);

        $connection = new MockConnection($this->mockPdo);
        $this->assertFalse($connection->beginTransaction());
    }

    /**
     * Test connection rollBack.
     */
    public function testRollBack()
    {
        $this->mockPdo->shouldReceive('beginTransaction')->once()->andReturn(true);
        $this->mockPdo->shouldReceive('rollBack')->once()->andReturn(true);

        $connection = new MockConnection($this->mockPdo);
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

        $connection = new MockConnection($this->mockPdo);
        $this->assertTrue($connection->beginTransaction());
        $this->assertFalse($connection->rollBack());
    }

    /**
     * Test connection rollBack - no transaction.
     */
    public function testRollBackNoTransaction()
    {
        $this->mockPdo->shouldReceive('rollBack')->never();

        $connection = new MockConnection($this->mockPdo);
        $this->assertFalse($connection->rollBack());
    }

    /**
     * Test connection commit.
     */
    public function testCommit()
    {
        $this->mockPdo->shouldReceive('beginTransaction')->once()->andReturn(true);
        $this->mockPdo->shouldReceive('commit')->once()->andReturn(true);

        $connection = new MockConnection($this->mockPdo);
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

        $connection = new MockConnection($this->mockPdo);
        $this->assertTrue($connection->beginTransaction());
        $this->assertFalse($connection->commit());
    }

    /**
     * Test connection commit - no transaction.
     */
    public function testCommitNoTransaction()
    {
        $this->mockPdo->shouldReceive('commit')->never();

        $connection = new MockConnection($this->mockPdo);
        $this->assertFalse($connection->commit());
    }


    /**
     * Test connection last insert id - without procedure name.
     */
    public function testLastInsertIdWithoutName()
    {
        $this->mockPdo->shouldReceive('lastInsertId')->once()->with(null)->andReturn('insert_id');

        $connection = new MockConnection($this->mockPdo);
        $this->assertEquals('insert_id', $connection->lastInsertId());
    }

    /**
     * Test connection last insert id - with procedure name.
     */
    public function testLastInsertIdNameGiven()
    {
        $this->mockPdo->shouldReceive('lastInsertId')->once()->with('name')->andReturn('insert_id');

        $connection = new MockConnection($this->mockPdo);
        $this->assertEquals('insert_id', $connection->lastInsertId('name'));
    }

    /**
     * Test connection prepare.
     */
    public function testPrepare()
    {
        $this->mockPdo->shouldReceive('prepare')->once()->with('statement', [])->andReturn($this->mockPdoStatement);

        $connection = new MockConnection($this->mockPdo);
        $this->assertEquals($this->mockPdoStatement, $connection->prepare('statement'));
    }

    /**
     * Test connection query.
     */
    public function testQuery()
    {
        $this->mockPdo->shouldReceive('query')
            ->once()->with('statement')->andReturn($this->mockPdoStatement);

        $connection = new MockConnection($this->mockPdo);
        $this->assertEquals($this->mockPdoStatement, $connection->query('statement'));
    }

    /**
     * Test connection quote.
     */
    public function testQuote()
    {
        $this->mockPdo->shouldReceive('quote')
            ->once()->with('string', \PDO::PARAM_STR)->andReturn('quoted');

        $connection = new MockConnection($this->mockPdo);
        $this->assertEquals('quoted', $connection->quote('string'));
    }

    /**
     * Test different clause quotations.
     *
     * @param string $expected
     * @param string $clause
     *
     * @dataProvider quoteClauseDataProvider
     */
    public function testQuoteClause($expected, $clause)
    {
        $connection = new MockConnection($this->mockPdo);

        $this->assertEquals($expected, $connection->quoteClause($clause));
    }

    /**
     * Test different clause quotations.
     *
     * @param string $expected
     * @param string $clause
     *
     * @dataProvider quoteColumnDataProvider
     */
    public function testQuoteColumn($expected, $clause)
    {
        $connection = new MockConnection($this->mockPdo);

        $this->assertEquals($expected, $connection->quoteColumn($clause));
    }


    public function quoteClauseDataProvider()
    {
        return [
            ['`foo` IS NULL', 'foo IS NULL'],
            ['`foo` IS TRUE', 'foo IS TRUE'],
            ['`foo` IS FALSE', 'foo IS FALSE'],

            ['`foo` IS  NOT NULL', 'foo IS NOT NULL'],
            ['`foo` IS  NOT TRUE', 'foo IS NOT TRUE'],
            ['`foo` IS  NOT FALSE', 'foo IS NOT FALSE'],

            ['`foo` LIKE ?', 'foo LIKE ?'],
            ['`foo` IN (?)', 'foo IN (?)'],
            ['`foo` IN (? , ?)', 'foo IN (?, ?)'],
            ['`foo` IN (? , ? , ?)', 'foo IN (?, ?, ?)'],
            ['`foo` IN (? , ?)', 'foo IN (?,?)'],
            ['`foo` IN (:a , :b)', 'foo IN (:a, :b)'],
            ['`foo` IN (:a , :b)', 'foo IN (:a,:b)'],

            ['`foo` = 1', 'foo = 1'],
            ['`foo` = ?', 'foo = ?'],
            ['`foo` = :bar', 'foo = :bar'],

            ['`foo` = ? AND `bar` = ?', 'foo = ? AND bar = ?'],
            ['(`foo` = ? AND `bar` = ?)', '(foo = ? AND bar = ?)'],
            ['(`foo` = ?) AND (`bar` = ?)', '(foo = ?) AND (bar = ?)'],
            ['`foo` = ? OR `bar` = ?', 'foo = ? OR bar = ?'],
            ['(`foo` = ? OR `bar` = ?)', '(foo = ? OR bar = ?)'],
            ['(`foo` = ?) OR (`bar` = ?)', '(foo = ?) OR (bar = ?)'],

            ['(`foo` = ? AND `baz` = ?) OR (`bar` = ?)', '(foo = ? AND baz = ?) OR (bar = ?)'],
            ['(`foo` = ?) OR (`bar` = ? AND `qux` = ?)', '(foo = ?) OR (bar = ? AND qux = ?)'],

            ['((`foo` = ? AND `baz` = ?) OR `bar` = ? AND `qux` = ?)', '((foo = ? AND baz = ?) OR bar = ? AND qux = ?)'],
        ];
    }

    public function quoteColumnDataProvider()
    {
        return [
            ['`foo`', 'foo'],
            ['`table`.`foo`', 'table.foo'],

            ['DISTINCT `table`.`foo`', 'DISTINCT table.foo'],
            ['DISTINCT(`table`.`foo`)', 'DISTINCT(table.foo)'],
        ];
    }
}