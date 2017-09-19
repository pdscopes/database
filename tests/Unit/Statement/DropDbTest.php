<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\DropDb;
use MadeSimple\Database\Tests\CompilableTestCase;

class DropDbTest extends CompilableTestCase
{
    /**
     * Test setting the database name.
     */
    public function testDatabase()
    {
        $statement = (new DropDb($this->mockConnection));
        $return    = $statement->database('name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(DropDb::class, $return);
        $this->assertEquals([
            'database' => 'name',
        ], $array);
    }


    /**
     * Test buildSql calls Compiler::compileQueryDelete.
     */
    public function testBuildSql()
    {
        $statement = [];
        $this->mockCompiler->shouldReceive('compileStatementDropDb')->once()->with($statement)->andReturn(['SQL', []]);

        $query = (new DropDb($this->mockConnection));
        list($sql, $bindings) = $query->buildSql($statement);
        $this->assertEquals('SQL', $sql);
        $this->assertEquals([], $bindings);
    }
}