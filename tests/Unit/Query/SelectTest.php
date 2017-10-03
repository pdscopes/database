<?php

namespace MadeSimple\Database\Tests\Unit\Query;

use MadeSimple\Database\Query\Column;
use MadeSimple\Database\Query\Select;
use MadeSimple\Database\Tests\CompilableTestCase;

class SelectTest extends CompilableTestCase
{
    /**
     * Test set columns.
     *
     * @param mixed $columns
     *
     * @dataProvider columnsDataProvider
     */
    public function testColumns($columns)
    {
        $query = (new Select($this->mockConnection))->columns($columns);
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('columns', $array);
        $this->assertEquals(['id'], $array['columns']);
    }

    /**
     * Test add columns.
     *
     * @param mixed $columns
     *
     * @dataProvider columnsDataProvider
     */
    public function testAddColumns($columns)
    {
        $query = (new Select($this->mockConnection))->columns('*')->addColumns($columns);
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('columns', $array);
        $this->assertEquals(['*', 'id'], $array['columns']);
    }

    /**
     * Test set from without an alias.
     */
    public function testFromWithoutAlias()
    {
        $query = (new Select($this->mockConnection))->from('table');
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('from', $array);
        $this->assertEquals(['table'], $array['from']);
    }

    /**
     * Test set from with an alias.
     */
    public function testFromWithAlias()
    {
        $query = (new Select($this->mockConnection))->from('table', 't');
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('from', $array);
        $this->assertEquals(['t' => 'table'], $array['from']);
    }

    /**
     * Test add from without an alias.
     */
    public function testAddFromWithoutAlias()
    {
        $query = (new Select($this->mockConnection))->from('table1')->addFrom('table2');
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('from', $array);
        $this->assertEquals(['table1', 'table2'], $array['from']);
    }

    /**
     * Test add from with an alias.
     */
    public function testAddFromWithAlias()
    {
        $query = (new Select($this->mockConnection))->from('table1')->addFrom('table2', 't2');
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('from', $array);
        $this->assertEquals(['table1', 't2' => 'table2'], $array['from']);
    }

    /**
     * Test join without an alias.
     */
    public function testJoinWithoutAlias()
    {
        $query = (new Select($this->mockConnection))->join('join', 'join.tableId', '=', 'table.id');
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('join', $array);
        $this->assertCount(1, $array['join']);
        $this->assertEquals([
            'type'  => 'inner',
            'table' => 'join',
            'alias' => null,
            'statement' => [
                'where' => [
                    [
                        'column'   => 'join.tableId',
                        'operator' => '=',
                        'value'    => 'table.id',
                        'boolean'  => 'and',
                    ]
                ],
            ]
        ], $array['join'][0]);
        $this->assertInstanceOf(Column::class, $array['join'][0]['statement']['where'][0]['value']);
    }

    /**
     * Test join without an alias.
     */
    public function testJoinWithAlias()
    {
        $query = (new Select($this->mockConnection))->join('join', 'j.tableId', '=', 'table.id', 'j');
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('join', $array);
        $this->assertCount(1, $array['join']);
        $this->assertEquals([
            'type'  => 'inner',
            'table' => 'join',
            'alias' => 'j',
            'statement' => [
                'where' => [
                    [
                        'column'   => 'j.tableId',
                        'operator' => '=',
                        'value'    => 'table.id',
                        'boolean'  => 'and',
                    ]
                ],
            ]
        ], $array['join'][0]);
        $this->assertInstanceOf(Column::class, $array['join'][0]['statement']['where'][0]['value']);
    }

    /**
     * Test left join.
     */
    public function testLeftJoin()
    {
        $query = (new Select($this->mockConnection))->leftJoin('join', 'join.tableId', '=', 'table.id');
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('join', $array);
        $this->assertCount(1, $array['join']);
        $this->assertEquals([
            'type'  => 'left',
            'table' => 'join',
            'alias' => null,
            'statement' => [
                'where' => [
                    [
                        'column'   => 'join.tableId',
                        'operator' => '=',
                        'value'    => 'table.id',
                        'boolean'  => 'and',
                    ]
                ],
            ]
        ], $array['join'][0]);
        $this->assertInstanceOf(Column::class, $array['join'][0]['statement']['where'][0]['value']);
    }

    /**
     * Test right join.
     */
    public function testRightJoin()
    {
        $query = (new Select($this->mockConnection))->rightJoin('join', 'join.tableId', '=', 'table.id');
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('join', $array);
        $this->assertCount(1, $array['join']);
        $this->assertEquals([
            'type'  => 'right',
            'table' => 'join',
            'alias' => null,
            'statement' => [
                'where' => [
                    [
                        'column'   => 'join.tableId',
                        'operator' => '=',
                        'value'    => 'table.id',
                        'boolean'  => 'and',
                    ]
                ],
            ]
        ], $array['join'][0]);
        $this->assertInstanceOf(Column::class, $array['join'][0]['statement']['where'][0]['value']);
    }

    /**
     * Test set group by.
     *
     * @param mixed $columns
     *
     * @dataProvider groupByDataProvider
     */
    public function testGroupBySingle($columns)
    {
        $query = (new Select($this->mockConnection))->groupBy($columns);
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('groupBy', $array);
        $this->assertEquals((array) $columns, $array['groupBy']);
    }

    /**
     * Test add group by.
     *
     * @param mixed $columns
     *
     * @dataProvider groupByDataProvider
     */
    public function testGroupByMultiple($columns)
    {
        $query = (new Select($this->mockConnection))->groupBy('field')->groupBy($columns);
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('groupBy', $array);
        $this->assertEquals(array_merge(['field'], (array) $columns), $array['groupBy']);
    }

    /**
     * Test having.
     */
    public function testHaving()
    {
        $query = (new Select($this->mockConnection))->having('field', 'op', 'val');
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('having', $array);
        $this->assertCount(1, $array);
        $this->assertEquals([
            'column'   => 'field',
            'operator' => 'op',
            'value'    => 'val',
            'boolean'  => 'and',
        ], $array['having'][0]);
    }

    /**
     * Test or having.
     */
    public function testOrHaving()
    {
        $query = (new Select($this->mockConnection))->orHaving('field', 'op', 'val');
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('having', $array);
        $this->assertCount(1, $array);
        $this->assertEquals([
            'column'   => 'field',
            'operator' => 'op',
            'value'    => 'val',
            'boolean'  => 'or',
        ], $array['having'][0]);
    }

    /**
     * Test set order by without direction.
     */
    public function testOrderByWithoutDirection()
    {
        $query = (new Select($this->mockConnection))->orderBy('field');
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('orderBy', $array);
        $this->assertCount(1, $array);
        $this->assertEquals([
            'column'    => 'field',
            'direction' => 'asc',
        ], $array['orderBy'][0]);
    }

    /**
     * Test set order by with direction.
     */
    public function testOrderByWithDirection()
    {
        $query = (new Select($this->mockConnection))->orderBy('field', 'dir');
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('orderBy', $array);
        $this->assertCount(1, $array);
        $this->assertEquals([
            'column'    => 'field',
            'direction' => 'dir',
        ], $array['orderBy'][0]);
    }

    /**
     * Test set order by with null direction.
     */
    public function testOrderByWithNullDirection()
    {
        $query = (new Select($this->mockConnection))->orderBy('field', null);
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('orderBy', $array);
        $this->assertCount(1, $array);
        $this->assertEquals([
            'column'    => 'field',
            'direction' => null,
        ], $array['orderBy'][0]);
    }

    /**
     * Test set limit.
     */
    public function testLimit()
    {
        $query = (new Select($this->mockConnection))->limit(15);
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('limit', $array);
        $this->assertEquals(15, $array['limit']);
    }

    /**
     * Test set offset.
     */
    public function testOffset()
    {
        $query = (new Select($this->mockConnection))->offset(15);
        $array = $query->toArray();

        $this->assertInstanceOf(Select::class, $query);
        $this->assertArrayHasKey('offset', $array);
        $this->assertEquals(15, $array['offset']);
    }


    /**
     * Test count removes order by, limit, and offset when performed but
     * does not effect the select query.
     */
    public function testCount()
    {
        $statement = [
            'columns' => ['COUNT(*)'],
            'from'    => ['table'],
        ];
        $this->mockCompiler->shouldReceive('compileQuerySelect')->once()->with($statement)->andReturn(['SQL', []]);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();
        $this->mockPdoStatement->shouldReceive('fetch')->once()->with(\PDO::FETCH_COLUMN, 0)->andReturn(4);
        $this->mockPdo->shouldReceive('prepare')->once()->with('SQL')->andReturn($this->mockPdoStatement);

        $query = (new Select($this->mockConnection))
            ->from('table')->orderBy('field')->limit(15)->offset(15);


        $this->assertEquals(4, $query->count());
        $array  = $query->toArray();

        $this->assertArrayHasKey('from', $array);
        $this->assertArrayHasKey('orderBy', $array);
        $this->assertArrayHasKey('limit', $array);
        $this->assertArrayHasKey('offset', $array);
    }

    /**
     * Test fetch calls PDOStatement::fetch.
     */
    public function testFetch()
    {
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();
        $this->mockPdoStatement->shouldReceive('setFetchMode')->once()->with(\PDO::FETCH_ASSOC)->andReturnSelf();
        $this->mockPdoStatement->shouldReceive('fetch')->once()->withNoArgs()->andReturn('fetched');
        $this->mockPdo->shouldReceive('prepare')->once()->with('SQL')->andReturn($this->mockPdoStatement);


        $query = (new Select($this->mockConnection));
        $query->query('SQL', []);
        $this->assertEquals('fetched', $query->fetch());
    }

    /**
     * Test fetch calls PDOStatement::fetch - with parameters.
     */
    public function testFetchWithParameters()
    {
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();
        $this->mockPdoStatement->shouldReceive('setFetchMode')->once()->with('parameters', 'value')->andReturnSelf();
        $this->mockPdoStatement->shouldReceive('fetch')->once()->withNoArgs()->andReturn('fetched');
        $this->mockPdo->shouldReceive('prepare')->once()->with('SQL')->andReturn($this->mockPdoStatement);


        $query = (new Select($this->mockConnection));
        $query->query('SQL', []);
        $this->assertEquals('fetched', $query->fetch('parameters', 'value'));
    }

    /**
     * Test fetch calls PDOStatement::fetchAll.
     */
    public function testFetchAll()
    {
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();
        $this->mockPdoStatement->shouldReceive('setFetchMode')->once()->with(\PDO::FETCH_ASSOC)->andReturnSelf();
        $this->mockPdoStatement->shouldReceive('fetchAll')->once()->withNoArgs()->andReturn('fetched');
        $this->mockPdo->shouldReceive('prepare')->once()->with('SQL')->andReturn($this->mockPdoStatement);


        $query = (new Select($this->mockConnection));
        $query->query('SQL', []);
        $this->assertEquals('fetched', $query->fetchAll());
    }

    /**
     * Test fetch calls PDOStatement::fetchAll - with parameters.
     */
    public function testFetchAllWithParameters()
    {
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();
        $this->mockPdoStatement->shouldReceive('setFetchMode')->once()->with('parameters', 'value')->andReturnSelf();
        $this->mockPdoStatement->shouldReceive('fetchAll')->once()->withNoArgs()->andReturn('fetched');
        $this->mockPdo->shouldReceive('prepare')->once()->with('SQL')->andReturn($this->mockPdoStatement);


        $query = (new Select($this->mockConnection));
        $query->query('SQL', []);
        $this->assertEquals('fetched', $query->fetchAll('parameters', 'value'));
    }

    /**
     * Test buildSql calls Compiler::compileQuerySelect.
     */
    public function testBuildSql()
    {
        $statement = ['from' => ['table' => 't']];
        $this->mockCompiler->shouldReceive('compileQuerySelect')->once()->with($statement)->andReturn(['SQL', []]);

        $query = (new Select($this->mockConnection));
        list($sql, $bindings) = $query->buildSql($statement);
        $this->assertEquals('SQL', $sql);
        $this->assertEquals([], $bindings);
    }




    public function columnsDataProvider()
    {
        return [
            [['id']],
            ['id'],
        ];
    }

    public function groupByDataProvider()
    {
        return [
            [['id']],
            ['id'],
        ];
    }
}
