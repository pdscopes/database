<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\CreateDb;
use MadeSimple\Database\Tests\CompilableMySqlTestCase;

class CreateDbTest extends CompilableMySqlTestCase
{
    /**
     * Test setting the database name to be created.
     */
    public function testDatabase()
    {
        $sql       = 'CREATE DATABASE `name`';
        $statement = (new CreateDb($this->mockConnection))->database('name');
        $this->assertEquals($sql, $statement->toSql());
    }
}