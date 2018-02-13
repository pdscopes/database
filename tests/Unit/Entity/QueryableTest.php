<?php

namespace MadeSimple\Database\Tests\Unit\Entity;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Query;
use MadeSimple\Database\Tests\TestCase;

/**
 * Class QueryableTest
 *
 * @package MadeSimple\Database\Tests\Unit\Entity
 * @author
 */
class QueryableTest extends TestCase
{
    /**
     * Test Queryable Entity select without an alias.
     */
    public function testSelectWithoutAlias()
    {
        /** @var \Mockery\Mock|Query\Select $mockSelect */
        $mockSelect = \Mockery::mock(Query\Select::class);
        /** @var \Mockery\Mock|Connection $mockConnection */
        $mockConnection = \Mockery::mock(Connection::class);
        /** @var \Mockery\Mock|Pool $mockPool */
        $mockPool = \Mockery::mock(Pool::class);

        $mockPool->shouldReceive('get')->once()->with(null)->andReturn($mockConnection);
        $mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($mockSelect);
        $mockSelect
            ->shouldReceive('columns')->once()->with('*')->andReturnSelf()
            ->shouldReceive('from')->once()->with('entity', null)->andReturnSelf();

        $select = QueryableTestEntity::qSelect($mockPool);


        $this->assertEquals($mockSelect, $select);
    }

    /**
     * Test Queryable Entity select with an alias.
     */
    public function testSelectWithAlias()
    {
        /** @var \Mockery\Mock|Query\Select $mockSelect */
        $mockSelect = \Mockery::mock(Query\Select::class);
        /** @var \Mockery\Mock|Connection $mockConnection */
        $mockConnection = \Mockery::mock(Connection::class);
        /** @var \Mockery\Mock|Pool $mockPool */
        $mockPool = \Mockery::mock(Pool::class);

        $mockPool->shouldReceive('get')->once()->with(null)->andReturn($mockConnection);
        $mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($mockSelect);
        $mockSelect
            ->shouldReceive('columns')->once()->with('*')->andReturnSelf()
            ->shouldReceive('from')->once()->with('entity', 'q')->andReturnSelf();

        $select = QueryableTestEntity::qSelect($mockPool, 'q');


        $this->assertEquals($mockSelect, $select);
    }

    /**
     * Test Queryable Entity insert.
     */
    public function testInsert()
    {
        /** @var \Mockery\Mock|Query\Insert $mockInsert */
        $mockInsert = \Mockery::mock(Query\Insert::class);
        /** @var \Mockery\Mock|Connection $mockConnection */
        $mockConnection = \Mockery::mock(Connection::class);
        /** @var \Mockery\Mock|Pool $mockPool */
        $mockPool = \Mockery::mock(Pool::class);

        $mockPool->shouldReceive('get')->once()->with(null)->andReturn($mockConnection);
        $mockConnection->shouldReceive('insert')->once()->withNoArgs()->andReturn($mockInsert);
        $mockInsert
            ->shouldReceive('into')->once()->with('entity')->andReturnSelf();

        $insert = QueryableTestEntity::qInsert($mockPool);


        $this->assertEquals($mockInsert, $insert);
    }

    /**
     * Test Queryable Entity update.
     */
    public function testUpdate()
    {
        /** @var \Mockery\Mock|Query\Update $mockUpdate */
        $mockUpdate = \Mockery::mock(Query\Update::class);
        /** @var \Mockery\Mock|Connection $mockConnection */
        $mockConnection = \Mockery::mock(Connection::class);
        /** @var \Mockery\Mock|Pool $mockPool */
        $mockPool = \Mockery::mock(Pool::class);

        $mockPool->shouldReceive('get')->once()->with(null)->andReturn($mockConnection);
        $mockConnection->shouldReceive('update')->once()->withNoArgs()->andReturn($mockUpdate);
        $mockUpdate
            ->shouldReceive('table')->once()->with('entity')->andReturnSelf();

        $update = QueryableTestEntity::qUpdate($mockPool);


        $this->assertEquals($mockUpdate, $update);
    }

    /**
     * Test Queryable Entity delete.
     */
    public function testDelete()
    {
        /** @var \Mockery\Mock|Query\Delete $mockDelete */
        $mockDelete = \Mockery::mock(Query\Delete::class);
        /** @var \Mockery\Mock|Connection $mockConnection */
        $mockConnection = \Mockery::mock(Connection::class);
        /** @var \Mockery\Mock|Pool $mockPool */
        $mockPool = \Mockery::mock(Pool::class);

        $mockPool->shouldReceive('get')->once()->with(null)->andReturn($mockConnection);
        $mockConnection->shouldReceive('delete')->once()->withNoArgs()->andReturn($mockDelete);
        $mockDelete
            ->shouldReceive('from')->once()->with('entity')->andReturnSelf();

        $delete = QueryableTestEntity::qDelete($mockPool);


        $this->assertEquals($mockDelete, $delete);
    }
}
class QueryableTestEntity extends Entity
{
    use Entity\Queryable;

    public $id;

    protected static function getMap()
    {
        return new EntityMap('entity', ['id'], []);
    }
}