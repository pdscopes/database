<?php

namespace MadeSimple\Database\Tests\Integration\Compiler;

use MadeSimple\Database\Statement\AlterTable;
use MadeSimple\Database\Tests\CompilableMySqlTestCase;

class MySqlAlterTableTest extends CompilableMySqlTestCase
{
    /**
     * Test setting the table to be altered.
     */
    public function testAlterTableTable()
    {
        $sql       = 'ALTER TABLE `table`';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test renaming a table.
     */
    public function testAlterTableRenameTable()
    {
        $sql       = 'ALTER TABLE `table` RENAME TO `table1`';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->renameTable('table1');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a column.
     */
    public function testAlterTableAddColumn()
    {
        $sql       = 'ALTER TABLE `table` ADD `column` INT(10)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->addColumn('column')->integer(10);
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a column first.
     */
    public function testAlterTableAddColumnFirst()
    {
        $sql       = 'ALTER TABLE `table` ADD `column` INT(10) FIRST';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->addColumn('column')->integer(10)->first();
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a column after other_column.
     */
    public function testAlterTableAddColumnAfter()
    {
        $sql       = 'ALTER TABLE `table` ADD `column` INT(10) AFTER `other_column`';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->addColumn('column')->integer(10)->after('other_column');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test modifying a column.
     */
    public function testAlterTableModifyColumn()
    {
        $sql       = 'ALTER TABLE `table` MODIFY COLUMN `column` INT(10)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->modifyColumn('column')->integer(10);
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test altering a column.
     */
    public function testAlterTableAlterColumn()
    {
        $sql       = 'ALTER TABLE `table` MODIFY COLUMN `column` INT(10)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->alterColumn('column')->integer(10);
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test renaming a column.
     */
    public function testAlterTableRenameColumn()
    {
        $sql       = 'ALTER TABLE `table` CHANGE `column1` `column2`';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->renameColumn('column1', 'column2');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test changing a column without datatype.
     */
    public function testAlterTableChangeColumnWithoutDatatype()
    {
        $sql       = 'ALTER TABLE `table` CHANGE `column1` `column2`';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->changeColumn('column1', 'column2');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test changing a column with datatype.
     */
    public function testAlterTableChangeColumnWithDatatype()
    {
        $sql       = 'ALTER TABLE `table` CHANGE `column1` `column2` INT(10)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->changeColumn('column1', 'column2')->integer(10);
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test dropping a column.
     */
    public function testAlterTableDropColumn()
    {
        $sql       = 'ALTER TABLE `table` DROP COLUMN `column`';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->dropColumn('column');
        $this->assertEquals($sql, $statement->toSql());
    }


    /**
     * Test adding a foreign key without name.
     */
    public function testAlterTableAddForeignKeyWithoutName()
    {
        $sql       = 'ALTER TABLE `table` ADD FOREIGN KEY (`column`) REFERENCES `table2`(`column2`)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->addForeignKey('column', 'table2', 'column2');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a foreign key with name.
     */
    public function testAlterTableAddForeignKeyWithName()
    {
        $sql       = 'ALTER TABLE `table` ADD CONSTRAINT `name` FOREIGN KEY (`column`) REFERENCES `table2`(`column2`)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->addForeignKey('column', 'table2', 'column2', null, null, 'name');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test dropping a foreign key.
     */
    public function testAlterTableDropForeignKey()
    {
        $sql       = 'ALTER TABLE `table` DROP FOREIGN KEY `name`';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->dropForeignKey('name');
        $this->assertEquals($sql, $statement->toSql());
    }


    /**
     * Test adding a unique index without name.
     */
    public function testAlterTableAddUniqueWithoutName()
    {
        $sql       = 'ALTER TABLE `table` ADD UNIQUE (`column`)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->addUnique('column');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test adding a unique index with name.
     */
    public function testAlterTableAddUniqueWithName()
    {
        $sql       = 'ALTER TABLE `table` ADD CONSTRAINT `name` UNIQUE (`column`)';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->addUnique('column', 'name');
        $this->assertEquals($sql, $statement->toSql());
    }

    /**
     * Test dropping a unique index.
     */
    public function testAlterTableDropUnique()
    {
        $sql       = 'ALTER TABLE `table` DROP INDEX `name`';
        $statement = (new AlterTable($this->mockConnection))->table('table');
        $statement->dropUnique('name');
        $this->assertEquals($sql, $statement->toSql());
    }
}