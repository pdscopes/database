<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\AlterTable;
use MadeSimple\Database\Tests\CompilableTestCase;

class AlterTableTest extends CompilableTestCase
{
    /**
     * Test setting the table to be altered.
     */
    public function testTable()
    {
        $sql       = 'ALTER TABLE `table`';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a column.
     */
    public function testAddColumn()
    {
        $sql       = 'ALTER TABLE `table` ADD `column` INT(10)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->addColumn('column')->integer(10);
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test modifying a column.
     */
    public function testModifyColumn()
    {
        $sql       = 'ALTER TABLE `table` MODIFY COLUMN `column` INT(10)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->modifyColumn('column')->integer(10);
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test altering a column.
     */
    public function testAlterColumn()
    {
        $sql       = 'ALTER TABLE `table` MODIFY COLUMN `column` INT(10)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->alterColumn('column')->integer(10);
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test dropping a column.
     */
    public function testDropColumn()
    {
        $sql       = 'ALTER TABLE `table` DROP COLUMN `column`';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->dropColumn('column');
        $this->assertEquals($sql, $statement->toSql());
    }


    /**
     * Test adding a foreign key without name.
     */
    public function testAddForeignKeyWithoutName()
    {
        $sql       = 'ALTER TABLE `table` ADD FOREIGN KEY (`column`) REFERENCES `table2`(`column2`)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->addForeignKey('column', 'table2', 'column2');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a foreign key with name.
     */
    public function testAddForeignKeyWithName()
    {
        $sql       = 'ALTER TABLE `table` ADD CONSTRAINT `name` FOREIGN KEY (`column`) REFERENCES `table2`(`column2`)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->addForeignKey('column', 'table2', 'column2', null, null, 'name');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test dropping a foreign key.
     */
    public function testDropForeignKey()
    {
        $sql       = 'ALTER TABLE `table` DROP FOREIGN KEY `name`';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->dropForeignKey('name');
        $this->assertEquals($sql, $statement->toSql());
    }


    /**
     * Test adding a unique index without name.
     */
    public function testAddUniqueWithoutName()
    {
        $sql       = 'ALTER TABLE `table` ADD UNIQUE (`column`)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->addUnique('column');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a unique index with name.
     */
    public function testAddUniqueWithName()
    {
        $sql       = 'ALTER TABLE `table` ADD CONSTRAINT `name` UNIQUE (`column`)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->addUnique('column', 'name');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test dropping a unique index.
     */
    public function testDropUnique()
    {
        $sql       = 'ALTER TABLE `table` DROP INDEX `name`';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->dropUnique('name');
        $this->assertEquals($sql, $statement->toSql());
    }
}