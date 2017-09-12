<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\DropTable;
use MadeSimple\Database\Tests\CompilableTestCase;

class DropTableTest extends CompilableTestCase
{
    /**
     * Test setting the index name.
     */
    public function testIndex()
    {
        $sql       = 'DROP TABLE `name`';
        $statement = (new DropTable($this->mockConnection))->table('name');
        $this->assertEquals($sql, $statement->toSql());
    }
}