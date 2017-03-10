<?php

namespace Tests\Unit\Query;

use MadeSimple\Database\Statement\Query\Clause;
use Tests\MockConnection;
use Tests\TestCase;

class ClauseTest extends TestCase
{
    /**
     * @var \Mockery\Mock|\PDO
     */
    protected $mockPdo;

    /**
     * @var MockConnection
     */
    protected $mockConnection;

    protected function setUp()
    {
        parent::setUp();

        $this->mockPdo        = \Mockery::mock(\PDO::class);
        $this->mockConnection = new MockConnection($this->mockPdo);
    }

    /**
     * Test create an IN clause.
     */
    public function testInX()
    {
        $values = [1, 2, 3];
        $this->assertEquals('column IN (? , ? , ?)', Clause::inX('column', $values));
    }

    /**
     * Test create an NOT IN clause.
     */
    public function testNotInX()
    {
        $values = [1, 2, 3];
        $this->assertEquals('column NOT IN (? , ? , ?)', Clause::notInX('column', $values));
    }

    /**
     * Test the where clause.
     */
    public function testWhere()
    {
        $clause = new Clause($this->mockConnection);
        $clause->where('foo = ?');
        $clause->where('bar = ?');

        $this->assertEquals('`bar` = ?', $clause->flatten());
    }

    /**
     * Test the andX clause.
     */
    public function testAndX()
    {
        $clause = new Clause($this->mockConnection);
        $clause->where('foo = ?');
        $clause->andX('bar = ?');

        $this->assertEquals('`foo` = ? AND `bar` = ?', $clause->flatten());
    }

    /**
     * Test the orX clause.
     */
    public function testOrX()
    {
        $clause = new Clause($this->mockConnection);
        $clause->where('foo = ?');
        $clause->orX('bar = ?');

        $this->assertEquals('`foo` = ? OR `bar` = ?', $clause->flatten());
    }

    /**
     * Test Clause sub clause.
     */
    public function testClauseSubClause()
    {
        $subClause = new Clause($this->mockConnection);
        $subClause->where('foo = ?');
        $subClause->orX('bar = ?');

        $clause = new Clause($this->mockConnection);
        $clause
            ->where($subClause)
            ->andX('baz = ?');

        $this->assertEquals('(`foo` = ? OR `bar` = ?) AND `baz` = ?', $clause->flatten());
    }

    /**
     * Test Closure sub clause.
     */
    public function testClosureSubClause()
    {
        $clause = new Clause($this->mockConnection);
        $clause
            ->where(function (Clause $clause) {
                return $clause->where('foo = ?')->orX('bar = ?');
            })
            ->andX('baz = ?');

        $this->assertEquals('(`foo` = ? OR `bar` = ?) AND `baz` = ?', $clause->flatten());
    }
}