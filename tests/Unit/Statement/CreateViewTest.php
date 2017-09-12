<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Query\Select;
use MadeSimple\Database\Statement\CreateView;
use MadeSimple\Database\Tests\CompilableTestCase;

class CreateViewTest extends CompilableTestCase
{
    /**
     * Test setting the view name.
     */
    public function testView()
    {
        $sql       = 'CREATE VIEW `name` AS';
        $statement = (new CreateView($this->mockConnection))->view('name');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test setting the select query.
     */
    public function testAsSelect()
    {
        $sql       = 'CREATE VIEW `name` AS SELECT * FROM `table`';
        $statement = (new CreateView($this->mockConnection))->view('name')
            ->asSelect(function (Select $select) {
                $select->from('table');
            });
        $this->assertEquals($sql, $statement->toSql());
    }
}