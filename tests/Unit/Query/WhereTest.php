<?php

namespace MadeSimple\Database\Tests\Unit\Query;

use MadeSimple\Database\Query\Column;
use MadeSimple\Database\Query\Raw;
use MadeSimple\Database\Query\Select;
use MadeSimple\Database\Query\WhereBuilder;
use MadeSimple\Database\Tests\CompilableTestCase;

class WhereTest extends CompilableTestCase
{
    /**
     * Test where.
     */
    public function testWhere()
    {
        $query = (new WhereBuilder($this->mockConnection))->where('field', 'op', 1);
        $array = $query->toArray();

        $this->assertInstanceOf(WhereBuilder::class, $query);
        $this->assertEquals([
            'where' => [
                [
                    'column'   => 'field',
                    'operator' => 'op',
                    'value'    => 1,
                    'boolean'  => 'and',
                ]
            ]
        ], $array);
    }

    /**
     * Test where between.
     */
    public function testWhereBetween()
    {
        $query = (new WhereBuilder($this->mockConnection))->where('field', 'between', [1, 9]);
        $array = $query->toArray();

        $this->assertInstanceOf(WhereBuilder::class, $query);
        $this->assertEquals([
            'where' => [
                [
                    'column'   => 'field',
                    'operator' => 'between',
                    'value'    => [1, 9],
                    'boolean'  => 'and',
                ]
            ]
        ], $array);
    }

    /**
     * Test where in.
     */
    public function testWhereIn()
    {
        $query = (new WhereBuilder($this->mockConnection))->where('field', 'in', [1, 2, 3, 4, 5]);
        $array = $query->toArray();

        $this->assertInstanceOf(WhereBuilder::class, $query);
        $this->assertEquals([
            'where' => [
                [
                    'column'   => 'field',
                    'operator' => 'in',
                    'value'    => [1, 2, 3, 4, 5],
                    'boolean'  => 'and',
                ]
            ]
        ], $array);
    }

    /**
     * Test where with closure.
     */
    public function testWhereClosure()
    {
        $closure = function (WhereBuilder $query) {
            $query->where('field', '=', 4);
        };

        $query = (new WhereBuilder($this->mockConnection))->where($closure);
        $array = $query->toArray();

        $this->assertInstanceOf(WhereBuilder::class, $query);
        $this->assertEquals([
            'where' => [
                [
                    'column'   => $closure,
                    'operator' => null,
                    'value'    => null,
                    'boolean'  => 'and',
                ]
            ]
        ], $array);
    }

    /**
     * Test multiple wheres with AND boolean.
     */
    public function testOrWhere()
    {
        $query = (new WhereBuilder($this->mockConnection))->orWhere('field', 'op', 1);
        $array = $query->toArray();

        $this->assertInstanceOf(WhereBuilder::class, $query);
        $this->assertEquals([
            'where' => [
                [
                    'column'   => 'field',
                    'operator' => 'op',
                    'value'    => 1,
                    'boolean'  => 'or',
                ]
            ]
        ], $array);
    }

    /**
     * Test where with a raw value.
     */
    public function testWhereRaw()
    {
        $query = (new WhereBuilder($this->mockConnection))->whereRaw('field', 'op', 'COUNT(field1)');
        $array = $query->toArray();

        $this->assertEquals([
            'where' => [
                [
                    'column'   => 'field',
                    'operator' => 'op',
                    'value'    => 'COUNT(field1)',
                    'boolean'  => 'and',
                ]
            ]
        ], $array);
        $this->assertInstanceOf(Raw::class, $array['where'][0]['value']);
    }

    /**
     * Test or where with a raw value.
     */
    public function testOrWhereRaw()
    {
        $query = (new WhereBuilder($this->mockConnection))->orWhereRaw('field', 'op', 'COUNT(field1)');
        $array = $query->toArray();

        $this->assertEquals([
            'where' => [
                [
                    'column'   => 'field',
                    'operator' => 'op',
                    'value'    => 'COUNT(field1)',
                    'boolean'  => 'or',
                ]
            ]
        ], $array);
        $this->assertInstanceOf(Raw::class, $array['where'][0]['value']);
    }

    /**
     * Test where comparison of two columns.
     */
    public function testWhereColumn()
    {
        $query = (new WhereBuilder($this->mockConnection))->whereColumn('field1', 'op', 'field2');
        $array = $query->toArray();

        $this->assertEquals([
            'where' => [
                [
                    'column'   => 'field1',
                    'operator' => 'op',
                    'value'    => 'field2',
                    'boolean'  => 'and',
                ]
            ]
        ], $array);
        $this->assertInstanceOf(Column::class, $array['where'][0]['value']);
    }

    /**
     * Test or where comparison of two columns.
     */
    public function testOrWhereColumn()
    {
        $query = (new WhereBuilder($this->mockConnection))->orWhereColumn('field1', 'op', 'field2');
        $array = $query->toArray();

        $this->assertEquals([
            'where' => [
                [
                    'column'   => 'field1',
                    'operator' => 'op',
                    'value'    => 'field2',
                    'boolean'  => 'or',
                ]
            ]
        ], $array);
        $this->assertInstanceOf(Column::class, $array['where'][0]['value']);
    }

    /**
     * Test where exists - with closure.
     */
    public function testWhereExistsWithClosure()
    {
        $query = (new WhereBuilder($this->mockConnection))->whereExists(function (Select $select) {});
        $array = $query->toArray();

        $this->assertInstanceOf(WhereBuilder::class, $query);
        $this->assertEquals([
            'where' => [
                [
                    'type'    => 'exists',
                    'select'  => [],
                    'boolean' => 'and',
                    'not'     => false,
                ]
            ]
        ], $array);
    }

    /**
     * Test where exists - with Select object.
     */
    public function testWhereExistsWithSelect()
    {
        $select = new Select($this->mockConnection);
        $query  = (new WhereBuilder($this->mockConnection))->whereExists($select);
        $array  = $query->toArray();

        $this->assertInstanceOf(WhereBuilder::class, $query);
        $this->assertEquals([
            'where' => [
                [
                    'type'    => 'exists',
                    'select'  => $select->toArray(),
                    'boolean' => 'and',
                    'not'     => false,
                ]
            ]
        ], $array);
    }

    /**
     * Test where not exists.
     */
    public function testWhereNotExists()
    {
        $query = (new WhereBuilder($this->mockConnection))->whereNotExists(function (Select $select) {});
        $array = $query->toArray();

        $this->assertInstanceOf(WhereBuilder::class, $query);
        $this->assertEquals([
            'where' => [
                [
                    'type'    => 'exists',
                    'select'  => [],
                    'boolean' => 'and',
                    'not'     => true,
                ]
            ]
        ], $array);
    }

}