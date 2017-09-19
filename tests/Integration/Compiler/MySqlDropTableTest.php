<?php

namespace MadeSimple\Database\Tests\Integration\Compiler;

use MadeSimple\Database\Statement\DropTable;
use MadeSimple\Database\Tests\CompilableMySqlTestCase;

class MySqlDropTableTest extends CompilableMySqlTestCase
{
    /**
     * Test setting the index name.
     */
    public function testDropTableIndex()
    {
        $sql       = 'DROP TABLE `name`';
        $statement = (new DropTable($this->mockConnection))->table('name');
        $this->assertEquals($sql, $statement->toSql());
    }
}