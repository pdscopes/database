<?php

namespace MadeSimple\Database\Tests\Unit\Relationship;

use MadeSimple\Database\EntityCollection;
use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Query\Select;
use MadeSimple\Database\Relationship\ToMany;
use MadeSimple\Database\Tests\TestCase;

class ToManyTest extends TestCase
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
     * Test fetch.
     */
    public function testHasFetch()
    {
        $fetched = (new ToManyEntity)->populate(['ID' => 3, 'foreign_key' => '5']);

        $this->mockSelect->shouldReceive('columns')->once()->with('e.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('entity', 'e')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('e.foreign_key', '=', 5)->andReturnSelf();
        $this->mockSelect->shouldReceive('statement')->once()->withNoArgs()->andReturn([$this->mockPdoStatement, 0]);

        $this->mockPdoStatement->shouldReceive('fetchAll')->once()
            ->with(\PDO::FETCH_CLASS, ToManyEntity::class, [$this->mockPool, true])->andReturn([$fetched]);



        $entity = new ToManyRelatedEntity($this->mockPool);
        $entity->key = 5;
        $relation = (new ToMany($entity))->has(ToManyEntity::class, 'e', 'foreign_key');
        $items    = $relation->fetch();

        $this->assertInstanceOf(EntityCollection::class, $items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(ToManyEntity::class, $items[0]);
        $this->assertEquals(5, $items[0]->foreignKey);
        $this->assertEquals(3, $items[0]->id);
    }

    /**
     * Test fetch.
     */
    public function testBelongsToFetch()
    {
        $fetched = (new ToManyRelatedEntity)->populate(['KEY' => 5, 'db_value' => 'VALUE']);

        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.KEY', '=', 5)->andReturnSelf();
        $this->mockSelect->shouldReceive('statement')->once()->withNoArgs()->andReturn([$this->mockPdoStatement, 0]);

        $this->mockPdoStatement->shouldReceive('fetchAll')->once()
            ->with(\PDO::FETCH_CLASS, ToManyRelatedEntity::class, [$this->mockPool, true])->andReturn([$fetched]);



        $entity = new ToManyEntity($this->mockPool);
        $entity->foreignKey = 5;
        $relation = (new ToMany($entity))->belongsTo(ToManyRelatedEntity::class, 'r', 'foreign_key');
        $items    = $relation->fetch();

        $this->assertInstanceOf(EntityCollection::class, $items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(ToManyRelatedEntity::class, $items[0]);
        $this->assertEquals(5, $items[0]->key);
        $this->assertEquals('VALUE', $items[0]->value);
    }

    /**
     * Test has count.
     */
    public function testHasCount()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('e.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('entity', 'e')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('e.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('count')->once()->withNoArgs()->andReturn(3);

        $entity = new ToManyRelatedEntity($this->mockPool);
        $entity->key = 5;
        $relation = (new ToMany($entity))->has(ToManyEntity::class, 'e', 'foreign_key');

        $this->assertEquals(3, $relation->count());
    }

    /**
     * Test belongs to count.
     */
    public function testBelongsToCount()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.KEY', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('count')->once()->withNoArgs()->andReturn(3);

        $entity = new ToManyEntity($this->mockPool);
        $entity->foreignKey = 5;

        $relation = (new ToMany($entity))->belongsTo(ToManyRelatedEntity::class, 'r', 'foreign_key');

        $this->assertEquals(3, $relation->count());
    }
}
class ToManyEntity extends Entity
{
    public $id;
    public $foreignKey;

    protected static function getMap()
    {
        return new EntityMap('entity', ['ID' => 'id'], ['foreign_key' => 'foreignKey']);
    }
}
class ToManyRelatedEntity extends Entity
{
    public $key;
    public $value;

    protected static function getMap()
    {
        return new EntityMap('related', ['KEY' => 'key'], ['db_value' => 'value']);
    }
}