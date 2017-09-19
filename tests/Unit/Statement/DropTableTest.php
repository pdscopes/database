<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\DropTable;
use MadeSimple\Database\Tests\CompilableTestCase;

class DropTableTest extends CompilableTestCase
{
    /**
     * Test setting the index name.
     */
    public function testIndex()
    {
        $statement = (new DropTable($this->mockConnection));
        $return    = $statement->table('name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(DropTable::class, $return);
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
        $this->mockCompiler->shouldReceive('compileStatementDropTable')->once()->with($statement)->andReturn(['SQL', []]);

        $query = (new DropTable($this->mockConnection));
        list($sql, $bindings) = $query->buildSql($statement);
        $this->assertEquals('SQL', $sql);
        $this->assertEquals([], $bindings);
    }
}