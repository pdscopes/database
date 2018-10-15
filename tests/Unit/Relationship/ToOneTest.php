<?php

namespace MadeSimple\Database\Tests\Unit\Relationship;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Query\Select;
use MadeSimple\Database\Relationship\ToOne;
use MadeSimple\Database\Tests\TestCase;

class ToOneTest extends TestCase
{
    /**
     * @var \Mockery\Mock|\PDOStatement
     */
    private $mockPdoStatement;

    /**
     * @var \Mockery\Mock|Select
     */
    private $mockSelect;

    /**
     * @var \Mockery\Mock|Connection
     */
    private $mockConnection;

    /**
     * @var \Mockery\Mock|Pool
     */
    private $mockPool;

    protected function setUp()
    {
        parent::setUp();

        $this->mockPdoStatement = \Mockery::mock(\PDOStatement::class);
        $this->mockSelect       = \Mockery::mock(Select::class);
        $this->mockConnection   = \Mockery::mock(Connection::class);
        $this->mockPool         = \Mockery::mock(Pool::class);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
    }



    /**
     * Test "has" relationship fetch.
     */
    public function testHasFetch()
    {
        $fetched = (new ToOneEntity)->populate(['ID' => 3, 'foreign_key' => '5']);

        $this->mockSelect->shouldReceive('columns')->once()->with('e.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('entity', 'e')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('e.foreign_key', '=', 5)->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldReceive('statement')->once()->withNoArgs()->andReturn([$this->mockPdoStatement, 0]);

        $this->mockPdoStatement->shouldReceive('setFetchMode')
            ->once()->with(\PDO::FETCH_CLASS, ToOneEntity::class, [$this->mockPool, true])->andReturnSelf();
        $this->mockPdoStatement->shouldReceive('fetch')->once()->withNoArgs()->andReturn($fetched);



        $entity = new ToOneRelatedEntity($this->mockPool);
        $entity->key = 5;
        $relation = (new ToOne($entity))->has(ToOneEntity::class, 'e', 'foreign_key');
        /** @var ToOneEntity $related */
        $related  = $relation->fetch();

        $this->assertInstanceOf(ToOneEntity::class, $related);
        $this->assertEquals(5, $related->foreignKey);
        $this->assertEquals(3, $related->id);
    }

    /**
     * Test "has" relationship fetch with related.
     */
    public function testHasFetchWithRelated()
    {
        $fetched = (new ToOneRelationalEntity)->populate(['ID' => 3, 'foreign_key' => '5']);

        $this->mockSelect->shouldReceive('columns')->once()->with('e.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('entity', 'e')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('e.foreign_key', '=', 5)->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldReceive('statement')->once()->withNoArgs()->andReturn([$this->mockPdoStatement, 0]);

        $this->mockPdoStatement->shouldReceive('setFetchMode')
            ->once()->with(\PDO::FETCH_CLASS, ToOneRelationalEntity::class, [$this->mockPool, true])->andReturnSelf();
        $this->mockPdoStatement->shouldReceive('fetch')->once()->withNoArgs()->andReturn($fetched);



        $entity = new ToOneRelatedRelationalEntity($this->mockPool);
        $entity->key = 5;
        $relation = (new ToOne($entity))
            ->has(ToOneRelationalEntity::class, 'e', 'foreign_key')
            ->relate($entity, 'entity');
        /** @var ToOneRelationalEntity $related */
        $related  = $relation->fetch();

        $this->assertInstanceOf(ToOneRelationalEntity::class, $related);
        $this->assertEquals(5, $related->foreignKey);
        $this->assertEquals(3, $related->id);
        $this->assertEquals($entity, $related->relation('entity'));
    }

    /**
     * Test "belongs to" relationship fetch.
     */
    public function testBelongsToFetch()
    {
        $fetched = (new ToOneRelatedEntity)->populate(['KEY' => 5, 'db_value' => 'VALUE']);

        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.KEY', '=', 5)->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldReceive('statement')->once()->withNoArgs()->andReturn([$this->mockPdoStatement, 0]);

        $this->mockPdoStatement->shouldReceive('setFetchMode')
            ->once()->with(\PDO::FETCH_CLASS, ToOneRelatedEntity::class, [$this->mockPool, true])->andReturnSelf();
        $this->mockPdoStatement->shouldReceive('fetch')->once()->withNoArgs()->andReturn($fetched);



        $entity = new ToOneEntity($this->mockPool);
        $entity->foreignKey = 5;
        $relation = (new ToOne($entity))->belongsTo(ToOneRelatedEntity::class, 'r', 'foreign_key');
        /** @var ToOneRelatedEntity $related */
        $related  = $relation->fetch();

        $this->assertInstanceOf(ToOneRelatedEntity::class, $related);
        $this->assertEquals(5, $related->key);
        $this->assertEquals('VALUE', $related->value);
    }

    /**
     * Test "belong to" relationship fetch with related.
     */
    public function testBelongsToFetchWithRelated()
    {
        $fetched = (new ToOneRelatedRelationalEntity)->populate(['KEY' => 5, 'db_value' => 'VALUE']);

        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.KEY', '=', 5)->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldReceive('statement')->once()->withNoArgs()->andReturn([$this->mockPdoStatement, 0]);

        $this->mockPdoStatement->shouldReceive('setFetchMode')
            ->once()->with(\PDO::FETCH_CLASS, ToOneRelatedRelationalEntity::class, [$this->mockPool, true])->andReturnSelf();
        $this->mockPdoStatement->shouldReceive('fetch')->once()->withNoArgs()->andReturn($fetched);



        $entity = new ToOneRelationalEntity($this->mockPool);
        $entity->foreignKey = 5;
        $relation = (new ToOne($entity))
            ->belongsTo(ToOneRelatedRelationalEntity::class, 'r', 'foreign_key')
            ->relate($entity, 'entity');
        /** @var ToOneRelatedRelationalEntity $related */
        $related  = $relation->fetch();

        $this->assertInstanceOf(ToOneRelatedRelationalEntity::class, $related);
        $this->assertEquals(5, $related->key);
        $this->assertEquals('VALUE', $related->value);
        $this->assertEquals($entity, $related->relation('entity'));
    }
}
class ToOneEntity extends Entity
{
    public $id;
    public $foreignKey;

    protected static function getMap()
    {
        return new EntityMap('entity', ['ID' => 'id'], ['foreign_key' => 'foreignKey']);
    }
}
class ToOneRelatedEntity extends Entity
{
    public $key;
    public $value;

    protected static function getMap()
    {
        return new EntityMap('related', ['KEY' => 'key'], ['db_value' => 'value']);
    }
}
class ToOneRelationalEntity extends Entity
{
    use Entity\Relational;

    public $id;
    public $foreignKey;

    protected static function getMap()
    {
        return new EntityMap('entity', ['ID' => 'id'], ['foreign_key' => 'foreignKey']);
    }
}
class ToOneRelatedRelationalEntity extends Entity
{
    use Entity\Relational;

    public $key;
    public $value;

    protected static function getMap()
    {
        return new EntityMap('related', ['KEY' => 'key'], ['db_value' => 'value']);
    }
}