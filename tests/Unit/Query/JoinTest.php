<?php

namespace MadeSimple\Database\Tests\Unit\Query;

use MadeSimple\Database\Query\Column;
use MadeSimple\Database\Query\JoinBuilder;
use MadeSimple\Database\Tests\CompilableTestCase;

class JoinTest extends CompilableTestCase
{
    /**
     * Test on.
     */
    public function testOn()
    {
        $query = (new JoinBuilder($this->mockConnection))->on('field1', 'op', 'field2');
        $array = $query->toArray();

        $this->assertInstanceOf(JoinBuilder::class, $query);
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
     * Test where.
     */
    public function testWhere()
    {
        $query = (new JoinBuilder($this->mockConnection))->where('field1', 'op', 'field2');
        $array = $query->toArray();

        $this->assertInstanceOf(JoinBuilder::class, $query);
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
     * Test or where.
     */
    public function testOrWhere()
    {
        $query = (new JoinBuilder($this->mockConnection))->orWhere('field1', 'op', 'field2');
        $array = $query->toArray();

        $this->assertInstanceOf(JoinBuilder::class, $query);
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
     * Test where parameter.
     */
    public function testWhereParameter()
    {
        $query = (new JoinBuilder($this->mockConnection))->whereParameter('field1', 'op', 4);
        $array = $query->toArray();

        $this->assertInstanceOf(JoinBuilder::class, $query);
        $this->assertEquals([
            'where' => [
                [
                    'column'   => 'field1',
                    'operator' => 'op',
                    'value'    => 4,
                    'boolean'  => 'and',
                ]
            ]
        ], $array);
    }

    /**
     * Test or where parameter.
     */
    public function testOrWhereParameter()
    {
        $query = (new JoinBuilder($this->mockConnection))->orWhereParameter('field1', 'op', 4);
        $array = $query->toArray();

        $this->assertInstanceOf(JoinBuilder::class, $query);
        $this->assertEquals([
            'where' => [
                [
                    'column'   => 'field1',
                    'operator' => 'op',
                    'value'    => 4,
                    'boolean'  => 'or',
                ]
            ]
        ], $array);
    }
}