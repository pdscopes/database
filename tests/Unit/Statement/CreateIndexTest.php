<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\CreateIndex;
use MadeSimple\Database\Tests\CompilableTestCase;

class CreateIndexTest extends CompilableTestCase
{
    /**
     * Test setting the name of the index.
     */
    public function testIndex()
    {
        $statement = (new CreateIndex($this->mockConnection));
        $return    = $statement->index('name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateIndex::class, $return);
        $this->assertEquals([
            'index' => 'name',
        ], $array);
    }

    /**
     * Test setting the table to create the index on.
     */
    public function testTable()
    {
        $statement = (new CreateIndex($this->mockConnection));
        $return    = $statement->table('name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateIndex::class, $return);
        $this->assertEquals([
            'table' => 'name',
        ], $array);
    }

    /**
     * Test creating a unique index.
     */
    public function testUniqueIndex()
    {
        $statement = (new CreateIndex($this->mockConnection));
        $return    = $statement->unique();
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateIndex::class, $return);
        $this->assertEquals([
            'unique' => true,
        ], $array);
    }


    /**
     * Test buildSql calls Compiler::compileQueryDelete.
     */
    public function testBuildSql()
    {
        $statement = [];
        $this->mockCompiler->shouldReceive('compileStatementCreateIndex')->once()->with($statement)->andReturn(['SQL', []]);

        $query = (new CreateIndex($this->mockConnection));
        list($sql, $bindings) = $query->buildSql($statement);
        $this->assertEquals('SQL', $sql);
        $this->assertEquals([], $bindings);
    }
}