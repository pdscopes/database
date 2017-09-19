<?php

namespace MadeSimple\Database\Tests\Integration\Compiler;

use MadeSimple\Database\Statement\DropView;
use MadeSimple\Database\Tests\CompilableMySqlTestCase;

class MySqlDropViewTest extends CompilableMySqlTestCase
{
    /**
     * Test setting the index name.
     */
    public function testDropViewIndex()
    {
        $sql       = 'DROP VIEW `name`';
        $statement = (new DropView($this->mockConnection))->view('name');
        $this->assertEquals($sql, $statement->toSql());
    }
}