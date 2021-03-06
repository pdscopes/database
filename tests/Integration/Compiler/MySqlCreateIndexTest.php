<?php

namespace MadeSimple\Database\Tests\Integration\Compiler;

use MadeSimple\Database\Statement\CreateIndex;
use MadeSimple\Database\Tests\CompilableMySqlTestCase;

class MySqlCreateIndexTest extends CompilableMySqlTestCase
{
    /**
     * Test creating an index.
     */
    public function testCreateIndexIndex()
    {
        $sql       = 'CREATE INDEX `name` ON `table` (`column`)';
        $statement = (new CreateIndex($this->mockConnection))->index('column', 'name')->table('table');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test creating a unique index.
     */
    public function testCreateIndexUniqueIndex()
    {
        $sql       = 'CREATE UNIQUE INDEX `name` ON `table` (`column`)';
        $statement = (new CreateIndex($this->mockConnection))->index('column', 'name')->table('table')->unique();
        $this->assertEquals($sql, $statement->toSql());
    }
}