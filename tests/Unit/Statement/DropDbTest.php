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
        $sql       = 'DROP DATABASE `name`';
        $statement = (new DropDb($this->mockConnection))->database('name');
        $this->assertEquals($sql, $statement->toSql());
    }
}