<?php

namespace Tests\Unit\QueryBuilder;

use MadeSimple\Database\QueryBuilder\Clause;
use Tests\TestCase;

class ClauseTest extends TestCase
{
    /**
     * Test create an IN clause.
     */
    public function testInX()
    {
        $values = [1, 2, 3];
        $this->assertEquals('column IN (? , ? , ?)', Clause::inX('column', $values));
    }

    /**
     * Test the where clause.
     */
    public function testWhere()
    {
        $clause = new Clause();
        $clause->where('foo = ?');
        $clause->where('bar = ?');

        $this->assertEquals('bar = ?', $clause->flatten());
    }

    /**
     * Test the andX clause.
     */
    public function testAndX()
    {
        $clause = new Clause();
        $clause->where('foo = ?');
        $clause->andX('bar = ?');

        $this->assertEquals('foo = ? AND bar = ?', $clause->flatten());
    }

    /**
     * Test the orX clause.
     */
    public function testOrX()
    {
        $clause = new Clause();
        $clause->where('foo = ?');
        $clause->orX('bar = ?');

        $this->assertEquals('foo = ? OR bar = ?', $clause->flatten());
    }

    /**
     * Test Clause sub clause.
     */
    public function testClauseSubClause()
    {
        $subClause = new Clause();
        $subClause->where('foo = ?');
        $subClause->orX('bar = ?');

        $clause = new Clause();
        $clause
            ->where($subClause)
            ->andX('baz = ?');

        $this->assertEquals('(foo = ? OR bar = ?) AND baz = ?', $clause->flatten());
    }

    /**
     * Test Closure sub clause.
     */
    public function testClosureSubClause()
    {
        $clause = new Clause();
        $clause
            ->where(function (Clause $clause) {
                return $clause->where('foo = ?')->orX('bar = ?');
            })
            ->andX('baz = ?');

        $this->assertEquals('(foo = ? OR bar = ?) AND baz = ?', $clause->flatten());
    }
}