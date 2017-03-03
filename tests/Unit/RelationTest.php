<?php

namespace Tests\Unit;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\QueryBuilder\Select;
use MadeSimple\Database\Relation;
use Tests\TestCase;

class RelationTest extends TestCase
{
    /**
     * @var \Mockery\Mock|Select
     */
    private $mockSelect;

    /**
     * @var \Mockery\Mock|Connection
     */
    private $mockConnection;

    /**
     * @var RelationEntity
     */
    private $entity;

    protected function setUp()
    {
        parent::setUp();

        $this->mockSelect     = \Mockery::mock(Select::class);
        $this->mockConnection = \Mockery::mock(Connection::class);

        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);

        $this->entity = new RelationEntity($this->mockConnection);
    }


    /**
     * Test relation construction.
     */
    public function testConstruct()
    {
        new DummyRelation($this->entity, RelatedEntity::class, 'd.ID = f.id', 'd', 'f');
    }

    /**
     * Test adding a join onto the relation.
     */
    public function testJoin()
    {
        $this->mockSelect->shouldReceive('join')->once()->with('table', 'on', 'alias', 'type');

        $relation = new DummyRelation($this->entity, RelatedEntity::class, 'd.ID = f.id', 'd', 'f');
        $this->assertEquals($relation, $relation->join('table', 'on', 'alias', 'type'));
    }

    /**
     * Test adding a left join onto the relation.
     */
    public function testLeftJoin()
    {
        $this->mockSelect->shouldReceive('join')->once()->with('table', 'on', 'alias', Select::JOIN_LEFT);

        $relation = new DummyRelation($this->entity, RelatedEntity::class, 'd.ID = f.id', 'd', 'f');
        $this->assertEquals($relation, $relation->leftJoin('table', 'on', 'alias'));
    }

    /**
     * Test adding a right join onto the relation.
     */
    public function testRightJoin()
    {
        $this->mockSelect->shouldReceive('join')->once()->with('table', 'on', 'alias', Select::JOIN_RIGHT);

        $relation = new DummyRelation($this->entity, RelatedEntity::class, 'd.ID = f.id', 'd', 'f');
        $this->assertEquals($relation, $relation->rightJoin('table', 'on', 'alias'));
    }

    /**
     * Test adding a full join onto the relation.
     */
    public function testFullJoin()
    {
        $this->mockSelect->shouldReceive('join')->once()->with('table', 'on', 'alias', Select::JOIN_FULL);

        $relation = new DummyRelation($this->entity, RelatedEntity::class, 'd.ID = f.id', 'd', 'f');
        $this->assertEquals($relation, $relation->fullJoin('table', 'on', 'alias'));
    }

    /**
     * Test adding a inner join onto the relation.
     */
    public function testInnerJoin()
    {
        $this->mockSelect->shouldReceive('join')->once()->with('table', 'on', 'alias', Select::JOIN_INNER);

        $relation = new DummyRelation($this->entity, RelatedEntity::class, 'd.ID = f.id', 'd', 'f');
        $this->assertEquals($relation, $relation->innerJoin('table', 'on', 'alias'));
    }

    /**
     * Test setting the where clause of the relation.
     */
    public function testWhere()
    {
        $this->mockSelect->shouldReceive('where')->once()->with('clause', 'parameter');

        $relation = new DummyRelation($this->entity, RelatedEntity::class, 'd.ID = f.id', 'd', 'f');
        $this->assertEquals($relation, $relation->where('clause', 'parameter'));
    }

    /**
     * Test adding an and where clause to the relation.
     */
    public function testAndWhere()
    {
        $this->mockSelect->shouldReceive('andWhere')->once()->with('clause', 'parameter');

        $relation = new DummyRelation($this->entity, RelatedEntity::class, 'd.ID = f.id', 'd', 'f');
        $this->assertEquals($relation, $relation->andWhere('clause', 'parameter'));
    }

    /**
     * Test adding an or where clause to the relation.
     */
    public function testOrWhere()
    {
        $this->mockSelect->shouldReceive('orWhere')->once()->with('clause', 'parameter');

        $relation = new DummyRelation($this->entity, RelatedEntity::class, 'd.ID = f.id', 'd', 'f');
        $this->assertEquals($relation, $relation->orWhere('clause', 'parameter'));
    }

    /**
     * Test a relation basic query.
     */
    public function testQuery()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('f.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'f')->andReturnSelf();
        $this->mockSelect->shouldReceive('join')->once()->with('entity', 'd.ID = f.id', 'd')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('d.ID = :ID', ['ID' => 5])->andReturnSelf();

        $this->entity->id = 5;
        $relation = new DummyRelation($this->entity, RelatedEntity::class, 'd.ID = f.id', 'd', 'f');
        $this->assertInstanceOf(Select::class, $relation->query());
    }
}
class DummyRelation extends Relation
{
    public  function fetch()
    {

    }
}
class RelationEntity extends Entity
{
    public $id;
    public $value;

    public  function getMap()
    {
        return new EntityMap('entity', ['ID' => 'id'], ['ID' => 'id', 'db_value' => 'value']);
    }
}
class RelatedEntity extends Entity
{
    public $id;
    public $value;

    public  function getMap()
    {
        return new EntityMap('related', ['ID' => 'id'], ['ID' => 'id', 'db_value' => 'value']);
    }
}