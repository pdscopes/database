<?php

namespace Tests\Unit\Relation;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Statement\Query\Select;
use MadeSimple\Database\Relation\ToMany;
use Tests\TestCase;

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
     * @var ToOneEntity
     */
    private $entity;

    protected function setUp()
    {
        parent::setUp();

        $this->mockPdoStatement = \Mockery::mock(\PDOStatement::class);
        $this->mockSelect       = \Mockery::mock(Select::class);
        $this->mockConnection   = \Mockery::mock(Connection::class);

        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);

        $this->entity = new ToOneEntity($this->mockConnection);
    }


    /**
     * Test to many fetch.
     */
    public function testFetch()
    {
        $data = ['ID' => 5, 'db_value' => 'VALUE'];

        $this->mockSelect->shouldReceive('columns')->once()->with('f.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'f')->andReturnSelf();
        $this->mockSelect->shouldReceive('join')->once()->with('entity', 'd.ID = f.id', 'd')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('d.ID = :ID', ['ID' => 5])->andReturnSelf();
        $this->mockSelect->shouldReceive('execute')->once()->withNoArgs()->andReturn($this->mockPdoStatement);

        $this->mockPdoStatement->shouldReceive('fetch')->times(2)->with(\PDO::FETCH_ASSOC)->andReturnValues([$data, null]);

        $this->entity->id = 5;
        $relation = new ToMany($this->entity, ToManyRelatedEntity::class, 'd.ID = f.id', 'd', 'f');
        $items = $relation->fetch();

        $this->assertInternalType('array', $items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(ToManyRelatedEntity::class, $items[0]);
    }
}
class ToManyEntity extends Entity
{
    public $id;
    public $value;

    public  function getMap()
    {
        return new EntityMap('entity', ['ID' => 'id'], ['db_value' => 'value']);
    }
}
class ToManyRelatedEntity extends Entity
{
    public $id;
    public $value;

    public  function getMap()
    {
        return new EntityMap('related', ['ID' => 'id'], ['db_value' => 'value']);
    }
}