<?php

namespace MadeSimple\Database\Tests\Unit\Statement;

use MadeSimple\Database\Statement\ColumnBuilder;
use MadeSimple\Database\Tests\CompilableTestCase;

class ColumnBuilderTest extends CompilableTestCase
{
    /**
     * Test setting the column datatype - tiny integer.
     */
    public function testSetDataTypeTinyInteger()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->tinyInteger(4);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'     => 'tinyInteger',
                'length'   => 4,
                'unsigned' => false,
                'zerofill' => false,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - small integer.
     */
    public function testSetDataTypeSmallInteger()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->smallInteger(4);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'     => 'smallInteger',
                'length'   => 4,
                'unsigned' => false,
                'zerofill' => false,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - medium integer.
     */
    public function testSetDataTypeMediumInteger()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->mediumInteger(4);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'     => 'mediumInteger',
                'length'   => 4,
                'unsigned' => false,
                'zerofill' => false,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - integer.
     */
    public function testSetDataTypeInteger()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->integer(4);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'     => 'integer',
                'length'   => 4,
                'unsigned' => false,
                'zerofill' => false,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - big integer.
     */
    public function testSetDataTypeBigInteger()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->bigInteger(4);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'     => 'bigInteger',
                'length'   => 4,
                'unsigned' => false,
                'zerofill' => false,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - double.
     */
    public function testSetDataTypeDouble()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->double(4, 2);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'     => 'double',
                'length'   => 4,
                'decimals' => 2,
                'unsigned' => false,
                'zerofill' => false,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - float.
     */
    public function testSetDataTypeFloat()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->float(4, 2);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'     => 'float',
                'length'   => 4,
                'decimals' => 2,
                'unsigned' => false,
                'zerofill' => false,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - decimal.
     */
    public function testSetDataTypeDecimal()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->decimal(4, 2);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'     => 'decimal',
                'length'   => 4,
                'decimals' => 2,
                'unsigned' => false,
                'zerofill' => false,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - date.
     */
    public function testSetDataTypeDate()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->date();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type' => 'date',
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - time.
     */
    public function testSetDataTypeTime()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->time();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type' => 'time',
                'fsp'  => null,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - timestamp.
     */
    public function testSetDataTypeTimestamp()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->timestamp();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type' => 'timestamp',
                'fsp'  => null,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - date time.
     */
    public function testSetDataTypeDateTime()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->dateTime();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type' => 'dateTime',
                'fsp'  => null,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - char.
     */
    public function testSetDataTypeChar()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->char(255);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'    => 'char',
                'length'  => 255,
                'binary'  => false,
                'charset' => null,
                'collate' => null,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - varchar.
     */
    public function testSetDataTypeVarchar()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->varchar(255);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'    => 'varchar',
                'length'  => 255,
                'binary'  => false,
                'charset' => null,
                'collate' => null,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - binary.
     */
    public function testSetDataTypeBinary()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->binary(128);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'   => 'binary',
                'length' => 128,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - tiny blob.
     */
    public function testSetDataTypeTinyBlob()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->tinyBlob();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type' => 'tinyBlob',
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - blob.
     */
    public function testSetDataTypeBlob()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->blob();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type' => 'blob',
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - medium blob.
     */
    public function testSetDataTypeMediumBlob()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->mediumBlob();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type' => 'mediumBlob',
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - long blob.
     */
    public function testSetDataTypeLongBlob()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->longBlob();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type' => 'longBlob',
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - tiny text.
     */
    public function testSetDataTypeTinyText()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->tinyText();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'    => 'tinyText',
                'binary'  => false,
                'charset' => null,
                'collate' => null,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - text.
     */
    public function testSetDataTypeText()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->text();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'    => 'text',
                'binary'  => false,
                'charset' => null,
                'collate' => null,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - medium text.
     */
    public function testSetDataTypeMediumText()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->mediumText();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'    => 'mediumText',
                'binary'  => false,
                'charset' => null,
                'collate' => null,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - long text.
     */
    public function testSetDataTypeLongText()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->longText();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'    => 'longText',
                'binary'  => false,
                'charset' => null,
                'collate' => null,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - enum.
     */
    public function testSetDataTypeEnum()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->enum(['a', 'b']);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type'    => 'enum',
                'values'  => ['a', 'b'],
                'charset' => null,
                'collate' => null,
            ],
        ], $array);
    }

    /**
     * Test setting the column datatype - json.
     */
    public function testSetDataTypeJson()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->json();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'datatype' => [
                'type' => 'json',
            ],
        ], $array);
    }


    /**
     * Test null - default.
     */
    public function testNullTrue()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->null();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'null' => true,
        ], $array);
    }

    /**
     * Test null - false.
     */
    public function testNullFalse()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->null(false);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'null' => false,
        ], $array);
    }

    /**
     * Test not null.
     */
    public function testNotNull()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->notNull();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'null' => false,
        ], $array);
    }

    /**
     * Test default value - string.
     */
    public function testDefaultValueString()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->useCurrent()->default('value');
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'default' => 'value',
        ], $array);
    }

    /**
     * Test default value - non string.
     */
    public function testDefaultValueNonString()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->default(0);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'default' => '0',
        ], $array);
    }

    /**
     * Test default value - null.
     */
    public function testDefaultValueNull()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->default(null);
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'default' => null,
        ], $array);
    }

    /**
     * Test use current.
     */
    public function testUseCurrent()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->default('value')->useCurrent();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'useCurrent' => true,
        ], $array);
    }

    /**
     * Test default null.
     */
    public function testDefaultNull()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->defaultNull();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'default' => null,
        ], $array);
    }

    /**
     * Test auto increment.
     */
    public function testAutoIncrement()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->autoIncrement();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'autoIncrement' => true,
        ], $array);
    }

    /**
     * Test comment.
     */
    public function testComment()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->comment('text');
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'comment' => 'text',
        ], $array);
    }

    /**
     * Test primary key.
     */
    public function testPrimaryKey()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->primaryKey();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'primaryKey' => true,
        ], $array);
    }

    /**
     * Test unique index.
     */
    public function testUnique()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->unique();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'unique' => true,
        ], $array);
    }

    /**
     * Test first.
     */
    public function testFirst()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->after('column')->first();
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'first' => true,
        ], $array);
    }

    /**
     * Test after.
     */
    public function testAfter()
    {
        $statement = (new ColumnBuilder($this->mockConnection));
        $return    = $statement->first()->after('column');
        $array     = $statement->toArray();

        $this->assertInstanceOf(ColumnBuilder::class, $return);
        $this->assertEquals([
            'after' => 'column',
        ], $array);
    }
}