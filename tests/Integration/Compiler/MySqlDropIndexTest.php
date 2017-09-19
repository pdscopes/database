<?php

namespace MadeSimple\Database\Tests\Integration\Compiler;

use MadeSimple\Database\Statement\DropIndex;
use MadeSimple\Database\Tests\CompilableMySqlTestCase;

class MySqlDropIndexTest extends CompilableMySqlTestCase
{
    /**
     * Test setting the index name.
     */
    public function testDropIndexIndex()
    {
        $sql       = 'ALTER TABLE `table` DROP INDEX `name`';
        $statement = (new DropIndex($this->mockConnection))->index('name')->table('table');
        $this->assertEquals($sql, $statement->toSql());
    }
}