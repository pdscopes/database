<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\DropDb;
use MadeSimple\Database\Tests\CompilableMySqlTestCase;

class DropDbTest extends CompilableMySqlTestCase
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