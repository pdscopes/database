<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\DropIndex;
use MadeSimple\Database\Tests\CompilableTestCase;

class DropIndexTest extends CompilableTestCase
{
    /**
     * Test setting the index name.
     */
    public function testIndex()
    {
        $sql       = 'ALTER TABLE `table` DROP INDEX `name`';
        $statement = (new DropIndex($this->mockConnection))->index('name')->table('table');
        $this->assertEquals($sql, $statement->toSql());
    }
}