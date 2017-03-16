<?php

namespace Tests\Unit\Relation;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Relation\HasMany;
use MadeSimple\Database\Statement\Query\Select;
use Tests\TestCase;

class HasManyTest extends TestCase
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

    /**
     * @var HasManyEntity
     */
    private $entity;

    protected function setUp()
    {
        parent::setUp();

        $this->mockPdoStatement = \Mockery::mock(\PDOStatement::class);
        $this->mockSelect       = \Mockery::mock(Select::class);
        $this->mockConnection   = \Mockery::mock(Connection::class);
        $this->mockPool         = \Mockery::mock(Pool::class);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);

        $this->entity = new HasManyEntity($this->mockPool);
    }


    /**
     * Test initialise query.
     */
    public function testInitialiseQuery()
    {
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->entity->key = 5;
        new HasMany($this->entity, HasManyRelatedEntity::class, 'foreign_key', 'e', 'r');
    }

    /**
     * Test fetch.
     */
    public function testFetch()
    {
        $data = ['ID' => 3, 'foreign_key' => 5];

        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();
        $this->mockSelect->shouldReceive('execute')->once()->withNoArgs()->andReturn($this->mockPdoStatement);

        $this->mockPdoStatement->shouldReceive('fetch')->times(2)->with(\PDO::FETCH_ASSOC)->andReturnValues([$data, null]);


        $this->entity->key = 5;
        $relation = new HasMany($this->entity, HasManyRelatedEntity::class, 'foreign_key', 'e', 'r');
        $items    = $relation->fetch();

        $this->assertInternalType('array', $items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(HasManyRelatedEntity::class, $items[0]);
        $this->assertEquals(3, $items[0]->id);
        $this->assertEquals(5, $items[0]->foreignKey);
    }
}
class HasManyEntity extends Entity
{
    public $key;
    public $value;

    public  function getMap()
    {
        return new EntityMap('entity', ['KEY' => 'key'], ['db_value' => 'value']);
    }
}
class HasManyRelatedEntity extends Entity
{
    public $id;
    public $foreignKey;

    public  function getMap()
    {
        return new EntityMap('related', ['ID' => 'id'], ['foreign_key' => 'foreignKey']);
    }
}