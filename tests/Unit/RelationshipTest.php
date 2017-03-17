<?php

namespace Tests\Unit;

use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\MySQL\Connection;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Relationship;
use MadeSimple\Database\Statement\Query\Select;
use Tests\TestCase;

class RelationshipTest extends TestCase
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
     * @var \Mockery\Mock|Pool
     */
    private $mockPool;

    /**
     * @var RelationEntity
     */
    private $entity;

    protected function setUp()
    {
        parent::setUp();

        $this->mockSelect     = \Mockery::mock(Select::class);
        $this->mockConnection = \Mockery::mock(Connection::class);
        $this->mockPool       = \Mockery::mock(Pool::class);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);

        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);

        $this->entity = new RelationshipTestEntity($this->mockPool);
        $this->entity->id = 5;
    }


    /**
     * Test settings an initial belongs to relationship.
     */
    public function testBelongsTo()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('e.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('entity', 'e')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('e.ID = :ID', ['ID' => 5])->andReturnSelf();

        $related = new RelationshipTestRelated($this->mockPool);
        $related->foreign = 5;
        (new DummyRelationship($related))->belongsTo(RelationshipTestEntity::class, 'e', 'foreign_key');
    }

    /**
     * Test setting an initial has relationship.
     */
    public function testHas()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
    }

    /**
     * Test setting the columns on the relation.
     */
    public function testColumns()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->mockSelect->shouldReceive('columns')->once()->with(['columns']);

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->columns('columns'));
    }

    /**
     * Test adding columns to the relation.
     */
    public function testAddColumns()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->mockSelect->shouldReceive('addColumns')->once()->with(['columns']);

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->addColumns('columns'));
    }

    /**
     * Test adding a join onto the relation.
     */
    public function testJoin()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->mockSelect->shouldReceive('join')->once()->with('table', 'on', 'alias', 'type');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->join('table', 'on', 'alias', 'type'));
    }

    /**
     * Test adding a left join onto the relation.
     */
    public function testLeftJoin()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->mockSelect->shouldReceive('leftJoin')->once()->with('table', 'on', 'alias');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->leftJoin('table', 'on', 'alias'));
    }

    /**
     * Test adding a right join onto the relation.
     */
    public function testRightJoin()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->mockSelect->shouldReceive('rightJoin')->once()->with('table', 'on', 'alias');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->rightJoin('table', 'on', 'alias'));
    }

    /**
     * Test adding a full join onto the relation.
     */
    public function testFullJoin()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->mockSelect->shouldReceive('fullJoin')->once()->with('table', 'on', 'alias');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->fullJoin('table', 'on', 'alias'));
    }

    /**
     * Test adding a inner join onto the relation.
     */
    public function testInnerJoin()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->mockSelect->shouldReceive('innerJoin')->once()->with('table', 'on', 'alias');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->innerJoin('table', 'on', 'alias'));
    }

    /**
     * Test setting the where clause of the relation.
     */
    public function testWhere()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->mockSelect->shouldReceive('where')->once()->with('clause', 'parameter');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->where('clause', 'parameter'));
    }

    /**
     * Test adding an and where clause to the relation.
     */
    public function testAndWhere()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->mockSelect->shouldReceive('andWhere')->once()->with('clause', 'parameter');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->andWhere('clause', 'parameter'));
    }

    /**
     * Test adding an or where clause to the relation.
     */
    public function testOrWhere()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->mockSelect->shouldReceive('orWhere')->once()->with('clause', 'parameter');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->orWhere('clause', 'parameter'));
    }

    /**
     * Test adding a group by to the relation.
     */
    public function testGroupBy()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->mockSelect->shouldReceive('groupBy')->once()->with(['clause']);

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->groupBy('clause'));
    }

    /**
     * Test adding an add group by to the relation.
     */
    public function testAddGroupBy()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->mockSelect->shouldReceive('addGroupBy')->once()->with(['clause']);

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->addGroupBy('clause'));
    }

    /**
     * Test adding an order by to the relation.
     */
    public function testOrderBy()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->mockSelect->shouldReceive('orderBy')->once()->with(['clause']);

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->orderBy('clause'));
    }

    /**
     * Test adding an add order by to the relation.
     */
    public function testAddOrderBy()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $this->mockSelect->shouldReceive('addOrderBy')->once()->with(['clause']);

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->addOrderBy('clause'));
    }

    /**
     * Test a relation basic query.
     */
    public function testQuery()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('r.foreign_key = :foreign_key', ['foreign_key' => 5])->andReturnSelf();

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertInstanceOf(Select::class, $relation->query());
    }
}

class DummyRelationship extends Relationship
{
    public  function fetch()
    {
        return null;
    }
}
class RelationshipTestEntity extends Entity
{
    public $id;
    public $value;

    public  function getMap()
    {
        return new EntityMap('entity', ['ID' => 'id'], ['db_value' => 'value']);
    }
}
class RelationshipTestRelated extends Entity
{
    public $id;
    public $foreign;

    public  function getMap()
    {
        return new EntityMap('related', ['ID' => 'id'], ['foreign_key' => 'foreign']);
    }
}