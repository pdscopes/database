<?php

namespace MadeSimple\Database\Tests\Integration\Compiler;

use MadeSimple\Database\Statement\CreateDb;
use MadeSimple\Database\Tests\CompilableMySqlTestCase;

class MySqlCreateDbTest extends CompilableMySqlTestCase
{
    /**
     * Test setting the database name to be created.
     */
    public function testCreateDbDatabase()
    {
        $sql       = 'CREATE DATABASE `name`';
        $statement = (new CreateDb($this->mockConnection))->database('name');
        $this->assertEquals($sql, $statement->toSql());
    }
}