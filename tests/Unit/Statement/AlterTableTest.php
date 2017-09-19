<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\AlterTable;
use MadeSimple\Database\Statement\ColumnBuilder;
use MadeSimple\Database\Tests\CompilableTestCase;

class AlterTableTest extends CompilableTestCase
{
    /**
     * Test setting the table to be altered.
     */
    public function testTable()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->table('table');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'table' => 'table',
        ], $array);
    }

    /**
     * Test renaming table.
     */
    public function testRenameTable()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->renameTable('table1');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type' => 'renameTable',
                    'name' => 'table1',
                ]
            ],
        ], $array);
    }

    /**
     * Test changing engine.
     */
    public function testEngine()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->engine('EngineType');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type'   => 'engine',
                    'engine' => 'EngineType',
                ]
            ],
        ], $array);
    }

    /**
     * Test changing charset - without collation.
     */
    public function testCharsetWithoutCollation()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->charset('utf8');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type'    => 'charset',
                    'charset' => 'utf8',
                ]
            ],
        ], $array);
    }

    /**
     * Test changing charset - with collation.
     */
    public function testCharsetWithCollation()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->charset('utf8', 'utf8_general_ci');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type'    => 'charset',
                    'charset' => 'utf8',
                ],
                [
                    'type'      => 'collate',
                    'collation' => 'utf8_general_ci',
                ]
            ],
        ], $array);
    }

    /**
     * Test adding a column - without closure.
     */
    public function testAddColumnWithoutClosure()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->addColumn('column');
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertArrayHasKey('alterations', $array);
        $this->assertCount(1, $array['alterations']);
        $this->assertArrayHasKey('type', $array['alterations'][0]);
        $this->assertArrayHasKey('name', $array['alterations'][0]);
        $this->assertArrayHasKey('columnBuilder', $array['alterations'][0]);
        $this->assertEquals('addColumn', $array['alterations'][0]['type']);
        $this->assertEquals('column', $array['alterations'][0]['name']);
        $this->assertInstanceOf(ColumnBuilder::class, $array['alterations'][0]['columnBuilder']);
    }

    /**
     * Test adding a column - with closure.
     */
    public function testAddColumnWithClosure()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->addColumn('column', function ($columnBuilder) {});
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertArrayHasKey('alterations', $array);
        $this->assertCount(1, $array['alterations']);
        $this->assertArrayHasKey('type', $array['alterations'][0]);
        $this->assertArrayHasKey('name', $array['alterations'][0]);
        $this->assertArrayHasKey('columnBuilder', $array['alterations'][0]);
        $this->assertEquals('addColumn', $array['alterations'][0]['type']);
        $this->assertEquals('column', $array['alterations'][0]['name']);
        $this->assertInstanceOf(ColumnBuilder::class, $array['alterations'][0]['columnBuilder']);
    }

    /**
     * Test modifying a column - without closure.
     */
    public function testModifyColumnWithoutClosure()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->modifyColumn('column');
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertArrayHasKey('alterations', $array);
        $this->assertCount(1, $array['alterations']);
        $this->assertArrayHasKey('type', $array['alterations'][0]);
        $this->assertArrayHasKey('name', $array['alterations'][0]);
        $this->assertArrayHasKey('columnBuilder', $array['alterations'][0]);
        $this->assertEquals('modifyColumn', $array['alterations'][0]['type']);
        $this->assertEquals('column', $array['alterations'][0]['name']);
        $this->assertInstanceOf(ColumnBuilder::class, $array['alterations'][0]['columnBuilder']);
    }

    /**
     * Test modifying a column - with closure.
     */
    public function testModifyColumnWithClosure()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->modifyColumn('column', function ($columnBuilder) {});
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertArrayHasKey('alterations', $array);
        $this->assertCount(1, $array['alterations']);
        $this->assertArrayHasKey('type', $array['alterations'][0]);
        $this->assertArrayHasKey('name', $array['alterations'][0]);
        $this->assertArrayHasKey('columnBuilder', $array['alterations'][0]);
        $this->assertEquals('modifyColumn', $array['alterations'][0]['type']);
        $this->assertEquals('column', $array['alterations'][0]['name']);
        $this->assertInstanceOf(ColumnBuilder::class, $array['alterations'][0]['columnBuilder']);
    }

    /**
     * Test altering a column - without closure.
     */
    public function testAlterColumnWithoutClosure()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->alterColumn('column');
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertArrayHasKey('alterations', $array);
        $this->assertCount(1, $array['alterations']);
        $this->assertArrayHasKey('type', $array['alterations'][0]);
        $this->assertArrayHasKey('name', $array['alterations'][0]);
        $this->assertArrayHasKey('columnBuilder', $array['alterations'][0]);
        $this->assertEquals('modifyColumn', $array['alterations'][0]['type']);
        $this->assertEquals('column', $array['alterations'][0]['name']);
        $this->assertInstanceOf(ColumnBuilder::class, $array['alterations'][0]['columnBuilder']);
    }

    /**
     * Test altering a column - with closure.
     */
    public function testAlterColumnWithClosure()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->alterColumn('column', function ($columnBuilder) {});
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertArrayHasKey('alterations', $array);
        $this->assertCount(1, $array['alterations']);
        $this->assertArrayHasKey('type', $array['alterations'][0]);
        $this->assertArrayHasKey('name', $array['alterations'][0]);
        $this->assertArrayHasKey('columnBuilder', $array['alterations'][0]);
        $this->assertEquals('modifyColumn', $array['alterations'][0]['type']);
        $this->assertEquals('column', $array['alterations'][0]['name']);
        $this->assertInstanceOf(ColumnBuilder::class, $array['alterations'][0]['columnBuilder']);
    }

    /**
     * Test renaming a column.
     */
    public function testRenameColumn()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->renameColumn('column1', 'column2');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type'        => 'renameColumn',
                    'currentName' => 'column1',
                    'name'        => 'column2',
                ]
            ],
        ], $array);
    }

    /**
     * Test changing a column - without closure.
     */
    public function testChangeColumnWithoutClosure()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->changeColumn('column1', 'column2');
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertArrayHasKey('alterations', $array);
        $this->assertCount(1, $array['alterations']);
        $this->assertArrayHasKey('type', $array['alterations'][0]);
        $this->assertArrayHasKey('currentName', $array['alterations'][0]);
        $this->assertArrayHasKey('name', $array['alterations'][0]);
        $this->assertArrayHasKey('columnBuilder', $array['alterations'][0]);
        $this->assertEquals('renameColumn', $array['alterations'][0]['type']);
        $this->assertEquals('column1', $array['alterations'][0]['currentName']);
        $this->assertEquals('column2', $array['alterations'][0]['name']);
        $this->assertInstanceOf(ColumnBuilder::class, $array['alterations'][0]['columnBuilder']);
    }

    /**
     * Test changing a column - with closure.
     */
    public function testChangeColumnWithClosure()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->changeColumn('column1', 'column2', function ($columnBuilder) {});
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertArrayHasKey('alterations', $array);
        $this->assertCount(1, $array['alterations']);
        $this->assertArrayHasKey('type', $array['alterations'][0]);
        $this->assertArrayHasKey('currentName', $array['alterations'][0]);
        $this->assertArrayHasKey('name', $array['alterations'][0]);
        $this->assertArrayHasKey('columnBuilder', $array['alterations'][0]);
        $this->assertEquals('renameColumn', $array['alterations'][0]['type']);
        $this->assertEquals('column1', $array['alterations'][0]['currentName']);
        $this->assertEquals('column2', $array['alterations'][0]['name']);
        $this->assertInstanceOf(ColumnBuilder::class, $array['alterations'][0]['columnBuilder']);
    }

    /**
     * Test dropping a column.
     */
    public function testDropColumn()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->dropColumn('column');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type' => 'dropColumn',
                    'name' => 'column',
                ]
            ],
        ], $array);
    }


    /**
     * Test adding a primary key - without name.
     */
    public function testAddPrimaryKeyWithoutName()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->addPrimaryKey('column');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type'    => 'addPrimaryKey',
                    'name'    => null,
                    'columns' => ['column'],
                ]
            ],
        ], $array);
    }

    /**
     * Test adding a primary key - with name.
     */
    public function testAddPrimaryKeyWithName()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->addPrimaryKey('column', 'pk_name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type'    => 'addPrimaryKey',
                    'name'    => 'pk_name',
                    'columns' => ['column'],
                ]
            ],
        ], $array);
    }

    /**
     * Test dropping a primary key.
     */
    public function testDropPrimaryKey()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->dropPrimaryKey('pk_name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type' => 'dropPrimaryKey',
                    'name' => 'pk_name',
                ]
            ],
        ], $array);
    }


    /**
     * Test adding a foreign key without name.
     */
    public function testAddForeignKeyWithoutName()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->addForeignKey('column', 'table2', 'column2');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type'             => 'addForeignKey',
                    'name'             => null,
                    'columns'          => ['column'],
                    'referenceTable'   => 'table2',
                    'referenceColumns' => ['column2'],
                    'onDelete'         => null,
                    'onUpdate'         => null,
                ]
            ],
        ], $array);
    }

    /**
     * Test adding a foreign key with name.
     */
    public function testAddForeignKeyWithName()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->addForeignKey('column', 'table2', 'column2', null, null, 'fk_name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type'             => 'addForeignKey',
                    'name'             => 'fk_name',
                    'columns'          => ['column'],
                    'referenceTable'   => 'table2',
                    'referenceColumns' => ['column2'],
                    'onDelete'         => null,
                    'onUpdate'         => null,
                ]
            ],
        ], $array);
    }

    /**
     * Test dropping a foreign key.
     */
    public function testDropForeignKey()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->dropForeignKey('fk_name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type' => 'dropForeignKey',
                    'name' => 'fk_name',
                ]
            ],
        ], $array);
    }


    /**
     * Test adding a unique index without name.
     */
    public function testAddUniqueWithoutName()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->addUnique('column');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type'    => 'addUnique',
                    'columns' => ['column'],
                    'name'    => null,
                ]
            ],
        ], $array);
    }

    /**
     * Test adding a unique index with name.
     */
    public function testAddUniqueWithName()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->addUnique('column', 'name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type'    => 'addUnique',
                    'columns' => ['column'],
                    'name'    => 'name',
                ]
            ],
        ], $array);
    }

    /**
     * Test dropping a unique index.
     */
    public function testDropUnique()
    {
        $statement = (new AlterTable($this->mockConnection));
        $return    = $statement->dropUnique('name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(AlterTable::class, $return);
        $this->assertEquals([
            'alterations' => [
                [
                    'type'    => 'dropUnique',
                    'name'    => 'name',
                ]
            ],
        ], $array);
    }


    /**
     * Test buildSql calls Compiler::compileQueryDelete.
     */
    public function testBuildSql()
    {
        $statement = [];
        $this->mockCompiler->shouldReceive('compileStatementAlterTable')->once()->with($statement)->andReturn(['SQL', []]);

        $query = (new AlterTable($this->mockConnection));
        list($sql, $bindings) = $query->buildSql($statement);
        $this->assertEquals('SQL', $sql);
        $this->assertEquals([], $bindings);
    }
}