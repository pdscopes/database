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
        $sql       = 'CREATE OR REPLACE VIEW `name` AS';
        $statement = (new UpdateView($this->mockConnection))->view('name');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test setting the select query.
     */
    public function testAsSelect()
    {
        $sql       = 'CREATE OR REPLACE VIEW `name` AS SELECT * FROM `table`';
        $statement = (new UpdateView($this->mockConnection))->view('name')
            ->asSelect(function (Select $select) {
                $select->from('table');
            });
        $this->assertEquals($sql, $statement->toSql());
    }
}