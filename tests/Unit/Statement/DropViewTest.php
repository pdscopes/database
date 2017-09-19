<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\DropView;
use MadeSimple\Database\Tests\CompilableTestCase;

class DropViewTest extends CompilableTestCase
{
    /**
     * Test setting the index name.
     */
    public function testIndex()
    {
        $statement = (new DropView($this->mockConnection));
        $return    = $statement->view('name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(DropView::class, $return);
        $this->assertEquals([
            'view' => 'name',
        ], $array);
    }


    /**
     * Test buildSql calls Compiler::compileQueryDelete.
     */
    public function testBuildSql()
    {
        $statement = [];
        $this->mockCompiler->shouldReceive('compileStatementDropView')->once()->with($statement)->andReturn(['SQL', []]);

        $query = (new DropView($this->mockConnection));
        list($sql, $bindings) = $query->buildSql($statement);
        $this->assertEquals('SQL', $sql);
        $this->assertEquals([], $bindings);
    }
}