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
        $sql       = 'DROP VIEW `name`';
        $statement = (new DropView($this->mockConnection))->view('name');
        $this->assertEquals($sql, $statement->toSql());
    }
}