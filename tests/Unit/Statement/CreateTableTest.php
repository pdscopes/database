<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\ColumnBuilder;
use MadeSimple\Database\Statement\CreateTable;
use MadeSimple\Database\Tests\CompilableTestCase;

class CreateTableTest extends CompilableTestCase
{
    /**
     * Test setting the table name.
     */
    public function testTable()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->table('name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'table' => 'name',
        ], $array);
    }

    /**
     * Test setting the temporary flag.
     */
    public function testTemporary()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->temporary();
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'temporary' => true,
        ], $array);
    }

    /**
     * Test setting "if not exists" flag.
     */
    public function testIfNotExists()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->ifNotExists();
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'ifNotExists' => true,
        ], $array);
    }

    /**
     * Test adding a primary key.
     */
    public function testPrimaryKey()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->primaryKey('id');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'constraints' => [
                [
                    'type'    => 'primaryKey',
                    'columns' => ['id']
                ]
            ],
        ], $array);
    }

    /**
     * Test adding a composite primary key.
     */
    public function testPrimaryKeyComposite()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->primaryKey('id1', 'id2');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'constraints' => [
                [
                    'type'    => 'primaryKey',
                    'columns' => ['id1', 'id2']
                ]
            ],
        ], $array);
    }

    /**
     * Test adding an index.
     */
    public function testIndex()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->index('column');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'constraints' => [
                [
                    'type'    => 'index',
                    'name'    => null,
                    'columns' => ['column']
                ]
            ],
        ], $array);
    }

    /**
     * Test adding a named index.
     */
    public function testIndexNamed()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->index('column', 'name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'constraints' => [
                [
                    'type'    => 'index',
                    'name'    => 'name',
                    'columns' => ['column']
                ]
            ],
        ], $array);
    }

    /**
     * Test adding a composite index.
     */
    public function testIndexComposite()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->index(['column1', 'column2']);
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'constraints' => [
                [
                    'type'    => 'index',
                    'name'    => null,
                    'columns' => ['column1', 'column2']
                ]
            ],
        ], $array);
    }

    /**
     * Test adding a unique index.
     */
    public function testUnique()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->unique('column');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'constraints' => [
                [
                    'type'    => 'unique',
                    'name'    => null,
                    'columns' => ['column']
                ]
            ],
        ], $array);
    }

    /**
     * Test adding a named unique index.
     */
    public function testUniqueNamed()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->unique('column', 'name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'constraints' => [
                [
                    'type'    => 'unique',
                    'name'    => 'name',
                    'columns' => ['column']
                ]
            ],
        ], $array);
    }

    /**
     * Test adding a composite unique index.
     */
    public function testUniqueComposite()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->unique(['column1', 'column2']);
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'constraints' => [
                [
                    'type'    => 'unique',
                    'name'    => null,
                    'columns' => ['column1', 'column2']
                ]
            ],
        ], $array);
    }

    /**
     * Test adding a foreign key.
     */
    public function testForeignKey()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->foreignKey('column', 'table', 'id');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'constraints' => [
                [
                    'type'             => 'foreignKey',
                    'name'             => null,
                    'columns'          => ['column'],
                    'referenceTable'   => 'table',
                    'referenceColumns' => ['id'],
                    'onDelete'         => null,
                    'onUpdate'         => null,
                ]
            ],
        ], $array);
    }

    /**
     * Test adding a foreign key with name.
     */
    public function testForeignKeyWithName()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->foreignKey('column', 'table', 'id', null, null, 'name');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'constraints' => [
                [
                    'type'             => 'foreignKey',
                    'name'             => 'name',
                    'columns'          => ['column'],
                    'referenceTable'   => 'table',
                    'referenceColumns' => ['id'],
                    'onDelete'         => null,
                    'onUpdate'         => null,
                ]
            ],
        ], $array);
    }

    /**
     * Test adding a foreign key with on delete.
     */
    public function testForeignKeyWithOnDelete()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->foreignKey('column', 'table', 'id', 'cascade');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'constraints' => [
                [
                    'type'             => 'foreignKey',
                    'name'             => null,
                    'columns'          => ['column'],
                    'referenceTable'   => 'table',
                    'referenceColumns' => ['id'],
                    'onDelete'         => 'cascade',
                    'onUpdate'         => null,
                ]
            ],
        ], $array);
    }

    /**
     * Test adding a foreign key with on update.
     */
    public function testForeignKeyWithOnUpdate()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->foreignKey('column', 'table', 'id', null, 'cascade');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'constraints' => [
                [
                    'type'             => 'foreignKey',
                    'name'             => null,
                    'columns'          => ['column'],
                    'referenceTable'   => 'table',
                    'referenceColumns' => ['id'],
                    'onDelete'         => null,
                    'onUpdate'         => 'cascade',
                ]
            ],
        ], $array);
    }

    /**
     * Test setting the table engine.
     */
    public function testEngine()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->engine('InnoDB');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'engine' => 'InnoDB',
        ], $array);
    }

    /**
     * Test setting the table charset.
     */
    public function testCharset()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->charset('utf8');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'charset' => 'utf8',
            'collate' => null,
        ], $array);
    }

    /**
     * Test setting the table collation.
     */
    public function testCollation()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->charset(null, 'utf8_ci');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'charset' => null,
            'collate' => 'utf8_ci',
        ], $array);
    }

    /**
     * Test setting the table comment.
     */
    public function testComment()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->comment('comment text');
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertEquals([
            'comment' => 'comment text',
        ], $array);
    }

    /**
     * Test adding a column to the table - without closure.
     */
    public function testColumnWithoutClosure()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->column('column');
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertArrayHasKey('columns', $array);
        $this->assertCount(1, $array['columns']);
        $this->assertArrayHasKey('name', $array['columns'][0]);
        $this->assertArrayHasKey('columnBuilder', $array['columns'][0]);
        $this->assertEquals('column', $array['columns'][0]['name']);
        $this->assertInstanceOf(ColumnBuilder::class, $array['columns'][0]['columnBuilder']);
    }

    /**
     * Test adding a column to the table - with closure.
     */
    public function testColumnWithClosure()
    {
        $statement = (new CreateTable($this->mockConnection));
        $return    = $statement->column('column', function ($create) {});
        $array     = $statement->toArray();

        $this->assertInstanceOf(CreateTable::class, $return);
        $this->assertArrayHasKey('columns', $array);
        $this->assertCount(1, $array['columns']);
        $this->assertArrayHasKey('name', $array['columns'][0]);
        $this->assertArrayHasKey('columnBuilder', $array['columns'][0]);
        $this->assertEquals('column', $array['columns'][0]['name']);
        $this->assertInstanceOf(ColumnBuilder::class, $array['columns'][0]['columnBuilder']);
    }


    /**
     * Test buildSql calls Compiler::compileQueryDelete.
     */
    public function testBuildSql()
    {
        $statement = [];
        $this->mockCompiler->shouldReceive('compileStatementCreateTable')->once()->with($statement)->andReturn(['SQL', []]);

        $query = (new CreateTable($this->mockConnection));
        list($sql, $bindings) = $query->buildSql($statement);
        $this->assertEquals('SQL', $sql);
        $this->assertEquals([], $bindings);
    }
}