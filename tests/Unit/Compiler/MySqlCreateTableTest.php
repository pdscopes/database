<?php

namespace MadeSimple\Database\Tests\Unit\Compiler;

use MadeSimple\Database\Statement\CreateTable;
use MadeSimple\Database\Tests\CompilableMySqlTestCase;

class MySqlCreateTableTest extends CompilableMySqlTestCase
{
    /**
     * Test setting the table name.
     */
    public function testCreateTableTable()
    {
        $sql       = 'CREATE TABLE `name` ( )';
        $statement = (new CreateTable($this->mockConnection))->table('name');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test setting the temporary flag.
     */
    public function testCreateTableTemporary()
    {
        $sql       = 'CREATE TEMPORARY TABLE `name` ( )';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->temporary();
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test setting "if not exists" flag.
     */
    public function testCreateTableIfNotExists()
    {
        $sql       = 'CREATE TABLE IF NOT EXISTS `name` ( )';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->ifNotExists();
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a primary key.
     */
    public function testCreateTablePrimaryKey()
    {
        $sql       = 'CREATE TABLE `name` ( ,PRIMARY KEY (`id`) )';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->primaryKey('id');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a composite primary key.
     */
    public function testCreateTablePrimaryKeyComposite()
    {
        $sql       = 'CREATE TABLE `name` ( ,PRIMARY KEY (`id1`,`id2`) )';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->primaryKey('id1', 'id2');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding an index.
     */
    public function testCreateTableIndex()
    {
        $sql       = 'CREATE TABLE `name` ( ,INDEX (`column`) )';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->index('column');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a named index.
     */
    public function testCreateTableIndexNamed()
    {
        $sql       = 'CREATE TABLE `name` ( ,INDEX `name`(`column`) )';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->index('column', 'name');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a composite index.
     */
    public function testCreateTableIndexComposite()
    {
        $sql       = 'CREATE TABLE `name` ( ,INDEX (`column1`,`column2`) )';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->index(['column1', 'column2']);
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a unique index.
     */
    public function testCreateTableUnique()
    {
        $sql       = 'CREATE TABLE `name` ( ,UNIQUE (`column`) )';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->unique('column');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a named unique index.
     */
    public function testCreateTableUniqueNamed()
    {
        $sql       = 'CREATE TABLE `name` ( ,UNIQUE `name`(`column`) )';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->unique('column', 'name');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a composite unique index.
     */
    public function testCreateTableUniqueComposite()
    {
        $sql       = 'CREATE TABLE `name` ( ,UNIQUE (`column1`,`column2`) )';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->unique(['column1', 'column2']);
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a foreign key.
     */
    public function testCreateTableForeignKey()
    {
        $sql       = 'CREATE TABLE `name` ( ,FOREIGN KEY (`column`) REFERENCES `table`(`id`) )';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->foreignKey('column', 'table', 'id');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a foreign key with name.
     */
    public function testCreateTableForeignKeyWithName()
    {
        $sql       = 'CREATE TABLE `name` ( ,FOREIGN KEY `fk`(`column`) REFERENCES `table`(`id`) )';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->foreignKey('column', 'table', 'id', null, null, 'fk');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a foreign key with on delete.
     */
    public function testCreateTableForeignKeyWithOnDelete()
    {
        $sql       = 'CREATE TABLE `name` ( ,FOREIGN KEY (`column`) REFERENCES `table`(`id`) ON DELETE CASCADE )';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->foreignKey('column', 'table', 'id', 'cascade');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a foreign key with on update.
     */
    public function testCreateTableForeignKeyWithOnUpdate()
    {
        $sql       = 'CREATE TABLE `name` ( ,FOREIGN KEY (`column`) REFERENCES `table`(`id`) ON UPDATE CASCADE )';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->foreignKey('column', 'table', 'id', null, 'cascade');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test setting the table engine.
     */
    public function testCreateTableEngine()
    {
        $sql       = 'CREATE TABLE `name` ( ) ENGINE=InnoDB';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->engine('InnoDB');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test setting the table charset.
     */
    public function testCreateTableCharset()
    {
        $sql       = 'CREATE TABLE `name` ( ) DEFAULT CHARACTER SET=utf8';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->charset('utf8');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test setting the table collation.
     */
    public function testCreateTableCollation()
    {
        $sql       = 'CREATE TABLE `name` ( ) COLLATE=utf8_ci';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->charset(null, 'utf8_ci');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test setting the table comment.
     */
    public function testCreateTableComment()
    {
        $sql       = 'CREATE TABLE `name` ( ) COMMENT=\'comment text\'';
        $statement = (new CreateTable($this->mockConnection))->table('name')
            ->comment('comment text');
        $this->assertEquals($sql, $statement->toSql());
    }


    /**
     * Test adding a column to the table.
     */
    public function testCreateTableColumn()
    {
        $sql       = 'CREATE TABLE `name` ( `column` INT(10) )';
        $statement = (new CreateTable($this->mockConnection))->table('name');
        $statement->column('column')->integer(10);
        $this->assertEquals($sql, $statement->toSql());
    }
}