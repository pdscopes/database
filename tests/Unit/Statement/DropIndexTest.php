<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\DropIndex;
use MadeSimple\Database\Tests\CompilableTestCase;

class DropIndexTest extends CompilableTestCase
{
    /**
     * Test setting the index name.
     */
    public function testIndex()
    {
        $statement = (new DropIndex($this->mockConnection));
        $return    = $statement->index('name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(DropIndex::class, $return);
        $this->assertEquals([
            'index' => 'name',
        ], $array);
    }

    /**
     * Test setting the table name.
     */
    public function testTable()
    {
        $statement = (new DropIndex($this->mockConnection));
        $return    = $statement->table('name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(DropIndex::class, $return);
        $this->assertEquals([
            'table' => 'name',
        ], $array);
    }


    /**
     * Test buildSql calls Compiler::compileQueryDelete.
     */
    public function testBuildSql()
    {
        $statement = [];
        $this->mockCompiler->shouldReceive('compileStatementDropIndex')->once()->with($statement)->andReturn(['SQL', []]);

        $query = (new DropIndex($this->mockConnection));
        list($sql, $bindings) = $query->buildSql($statement);
        $this->assertEquals('SQL', $sql);
        $this->assertEquals([], $bindings);
    }
}