<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Query\Select;
use MadeSimple\Database\Statement\UpdateView;
use MadeSimple\Database\Tests\CompilableTestCase;

class UpdateViewTest extends CompilableTestCase
{
    /**
     * Test setting the view name.
     */
    public function testView()
    {
        $statement = (new UpdateView($this->mockConnection));
        $return    = $statement->view('name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(UpdateView::class, $return);
        $this->assertEquals([
            'view' => 'name',
        ], $array);
    }

    /**
     * Test setting the select query - with closure.
     */
    public function testAsSelectWithClosure()
    {
        $statement = (new UpdateView($this->mockConnection));
        $return    = $statement->asSelect(function ($select) {});
        $array     = $statement->toArray();

        $this->assertInstanceOf(UpdateView::class, $return);
        $this->assertEquals([
            'select' => [],
        ], $array);
    }

    /**
     * Test setting the select query - with select.
     */
    public function testAsSelectWithSelect()
    {
        $select    = new Select($this->mockConnection);
        $statement = (new UpdateView($this->mockConnection));
        $return    = $statement->asSelect($select);
        $array     = $statement->toArray();

        $this->assertInstanceOf(UpdateView::class, $return);
        $this->assertEquals([
            'select' => $select->toArray(),
        ], $array);
    }


    /**
     * Test buildSql calls Compiler::compileQueryDelete.
     */
    public function testBuildSql()
    {
        $statement = [];
        $this->mockCompiler->shouldReceive('compileStatementUpdateView')->once()->with($statement)->andReturn(['SQL', []]);

        $query = (new UpdateView($this->mockConnection));
        list($sql, $bindings) = $query->buildSql($statement);
        $this->assertEquals('SQL', $sql);
        $this->assertEquals([], $bindings);
    }
}