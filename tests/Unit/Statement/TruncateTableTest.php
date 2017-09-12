<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\TruncateTable;
use MadeSimple\Database\Tests\CompilableTestCase;

class TruncateTableTest extends CompilableTestCase
{
    /**
     * Test setting the index name.
     */
    public function testIndex()
    {
        $sql       = 'TRUNCATE TABLE `name`';
        $statement = (new TruncateTable($this->mockConnection))->table('name');
        $this->assertEquals($sql, $statement->toSql());
    }
}