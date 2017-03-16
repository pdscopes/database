<?php

namespace Tests\Unit\Relation;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Relation\BelongsToOne;
use MadeSimple\Database\Statement\Query\Select;
use Tests\TestCase;

class BelongsToOneTest extends TestCase
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
     * @var BelongsToOneEntity
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

        $this->entity = new BelongsToOneEntity($this->mockPool);
    }


    /**
     * Test initialise query.
     */
    public function testInitialiseQuery()
    {
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.KEY = :KEY', ['KEY' => 5])->andReturnSelf();

        $this->entity->foreignKey = 5;
        new BelongsToOne($this->entity, BelongsToOneRelatedEntity::class, 'foreign_key', 'e', 'r');
    }

    /**
     * Test fetch.
     */
    public function testFetch()
    {
        $data = ['KEY' => 5, 'db_value' => 'VALUE'];

        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.KEY = :KEY', ['KEY' => 5])->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldReceive('execute')->once()->withNoArgs()->andReturn($this->mockPdoStatement);

        $this->mockPdoStatement->shouldReceive('fetch')->once()->with(\PDO::FETCH_ASSOC)->andReturn($data);


        $this->entity->foreignKey = 5;
        $relation = new BelongsToOne($this->entity, BelongsToOneRelatedEntity::class, 'foreign_key', 'e', 'r');
        $related  = $relation->fetch();

        $this->assertInstanceOf(BelongsToOneRelatedEntity::class, $related);
        $this->assertEquals(5, $related->key);
        $this->assertEquals('VALUE', $related->value);
    }
}
class BelongsToOneEntity extends Entity
{
    public $id;
    public $foreignKey;

    public  function getMap()
    {
        return new EntityMap('entity', ['ID' => 'id'], ['foreign_key' => 'foreignKey']);
    }
}
class BelongsToOneRelatedEntity extends Entity
{
    public $key;
    public $value;

    public  function getMap()
    {
        return new EntityMap('related', ['KEY' => 'key'], ['db_value' => 'value']);
    }
}