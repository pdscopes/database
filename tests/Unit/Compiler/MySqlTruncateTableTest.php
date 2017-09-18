<?php

namespace MadeSimple\Database\Tests\Unit\Compiler;

use MadeSimple\Database\Statement\TruncateTable;
use MadeSimple\Database\Tests\CompilableMySqlTestCase;

class MySqlTruncateTableTest extends CompilableMySqlTestCase
{
    /**
     * Test truncating a table.
     */
    public function testTruncateTableTable()
    {
        $sql       = 'TRUNCATE TABLE `name`';
        $statement = (new TruncateTable($this->mockConnection))->table('name');
        $this->assertEquals($sql, $statement->toSql());
    }
}