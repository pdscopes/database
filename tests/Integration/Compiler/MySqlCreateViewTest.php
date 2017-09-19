<?php

namespace MadeSimple\Database\Tests\Integration\Compiler;

use MadeSimple\Database\Query\Select;
use MadeSimple\Database\Statement\CreateView;
use MadeSimple\Database\Tests\CompilableMySqlTestCase;

class MySqlCreateViewTest extends CompilableMySqlTestCase
{
    /**
     * Test setting the view name.
     */
    public function testCreateViewView()
    {
        $sql       = 'CREATE VIEW `name` AS';
        $statement = (new CreateView($this->mockConnection))->view('name');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test setting the select query.
     */
    public function testCreateViewAsSelect()
    {
        $sql       = 'CREATE VIEW `name` AS SELECT * FROM `table`';
        $statement = (new CreateView($this->mockConnection))->view('name')
            ->asSelect(function (Select $select) {
                $select->from('table');
            });
        $this->assertEquals($sql, $statement->toSql());
    }
}