<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\TruncateTable;
use MadeSimple\Database\Tests\CompilableTestCase;

class TruncateTableTest extends CompilableTestCase
{
    /**
     * Test setting the index name.
     */
    public function testIndex()
    {
        $statement = (new TruncateTable($this->mockConnection));
        $return    = $statement->table('name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(TruncateTable::class, $return);
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
        $this->mockCompiler->shouldReceive('compileStatementTruncateTable')->once()->with($statement)->andReturn(['SQL', []]);

        $query = (new TruncateTable($this->mockConnection));
        list($sql, $bindings) = $query->buildSql($statement);
        $this->assertEquals('SQL', $sql);
        $this->assertEquals([], $bindings);
    }
}