<?php

namespace Tests\Unit\Relationship;

use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\MySQL\Connection;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Relationship\ToOne;
use MadeSimple\Database\Statement\Query\Select;
use Tests\TestCase;

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
     * Test fetch.
     */
    public function testHasFetch()
    {
        $data = ['ID' => 3, 'foreign_key' => '5'];

        $this->mockSelect->shouldReceive('columns')->once()->with('e.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('entity', 'e')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('e.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldReceive('execute')->once()->withNoArgs()->andReturn($this->mockPdoStatement);

        $this->mockPdoStatement->shouldReceive('fetch')->once()->with(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_FIRST)->andReturn($data);



        $entity = new ToOneRelatedEntity($this->mockPool);
        $entity->key = 5;
        $relation = (new ToOne($entity))->has(ToOneEntity::class, 'e', 'foreign_key');
        $related  = $relation->fetch();

        $this->assertInstanceOf(ToOneEntity::class, $related);
        $this->assertEquals(5, $related->foreignKey);
        $this->assertEquals(3, $related->id);
    }

    /**
     * Test fetch.
     */
    public function testBelongsToFetch()
    {
        $data = ['KEY' => 5, 'db_value' => 'VALUE'];

        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.KEY = :KEY', ['KEY' => 5])->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldReceive('execute')->once()->withNoArgs()->andReturn($this->mockPdoStatement);

        $this->mockPdoStatement->shouldReceive('fetch')->once()->with(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_FIRST)->andReturn($data);



        $entity = new ToOneEntity($this->mockPool);
        $entity->foreignKey = 5;
        $relation = (new ToOne($entity))->belongsTo(ToOneRelatedEntity::class, 'r', 'foreign_key');
        $related  = $relation->fetch();

        $this->assertInstanceOf(ToOneRelatedEntity::class, $related);
        $this->assertEquals(5, $related->key);
        $this->assertEquals('VALUE', $related->value);
    }
}
class ToOneEntity extends Entity
{
    public $id;
    public $foreignKey;

    public  function getMap()
    {
        return new EntityMap('entity', ['ID' => 'id'], ['foreign_key' => 'foreignKey']);
    }
}
class ToOneRelatedEntity extends Entity
{
    public $key;
    public $value;

    public  function getMap()
    {
        return new EntityMap('related', ['KEY' => 'key'], ['db_value' => 'value']);
    }
}