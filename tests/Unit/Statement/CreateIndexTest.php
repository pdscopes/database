<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\CreateIndex;
use MadeSimple\Database\Tests\CompilableTestCase;

class CreateIndexTest extends CompilableTestCase
{
    /**
     * Test creating an index.
     */
    public function testIndex()
    {
        $sql       = 'CREATE INDEX `name` ON `table`';
        $statement = (new CreateIndex($this->mockConnection))->index('name')->table('table');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test creating a unique index.
     */
    public function testUniqueIndex()
    {
        $sql       = 'CREATE UNIQUE INDEX `name` ON `table`';
        $statement = (new CreateIndex($this->mockConnection))->index('name')->table('table')->unique();
        $this->assertEquals($sql, $statement->toSql());
    }

}