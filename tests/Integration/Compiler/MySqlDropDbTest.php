<?php

namespace MadeSimple\Database\Tests\Integration\Compiler;

use MadeSimple\Database\Statement\DropDb;
use MadeSimple\Database\Tests\CompilableMySqlTestCase;

class MySqlDropDbTest extends CompilableMySqlTestCase
{
    /**
     * Test setting the database name.
     */
    public function testDropDbDatabase()
    {
        $sql       = 'DROP DATABASE `name`';
        $statement = (new DropDb($this->mockConnection))->database('name');
        $this->assertEquals($sql, $statement->toSql());
    }
}