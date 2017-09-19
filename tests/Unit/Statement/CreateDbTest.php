<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\CreateDb;
use MadeSimple\Database\Tests\CompilableTestCase;

class CreateDbTest extends CompilableTestCase
{
    /**
     * Test setting the database name to be created.
     */
    public function testDatabase()
    {
        $statement = (new CreateDb($this->mockConnection));
        $return    = $statement->database('name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateDb::class, $return);
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
        $this->mockCompiler->shouldReceive('compileStatementCreateDb')->once()->with($statement)->andReturn(['SQL', []]);

        $query = (new CreateDb($this->mockConnection));
        list($sql, $bindings) = $query->buildSql($statement);
        $this->assertEquals('SQL', $sql);
        $this->assertEquals([], $bindings);
    }
}