<?php

namespace MadeSimple\Database\Tests\Unit;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Query\Select;
use MadeSimple\Database\Relationship;
use MadeSimple\Database\Tests\TestCase;

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
     * @var Entity
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
        $this->mockSelect->shouldReceive('where')->once()->with('e.ID', '=', 5)->andReturnSelf();

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
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
    }

    /**
     * Test setting the columns on the relation.
     */
    public function testColumns()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('columns')->once()->with('columns');

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
            $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('addColumns')->once()->with('columns');

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
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

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
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

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
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('rightJoin')->once()->with('table', 'on', 'alias');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->rightJoin('table', 'on', 'alias'));
    }

    /**
     * Test setting the where clause of the relation.
     */
    public function testWhere()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('where')->once()->with('column', '!=', 'value');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->where('column', '!=', 'value'));
    }

    /**
     * Test adding an or where clause to the relation.
     */
    public function testOrWhere()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('orWhere')->once()->with('column', '!=', 'value');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->orWhere('column', '!=', 'value'));
    }

    /**
     * Test setting the where raw clause of the relation.
     */
    public function testWhereRaw()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('whereRaw')->once()->with('column', '!=', 'value');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->whereRaw('column', '!=', 'value'));
    }

    /**
     * Test adding an or where raw clause to the relation.
     */
    public function testOrWhereRaw()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('orWhereRaw')->once()->with('column', '!=', 'value');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->orWhereRaw('column', '!=', 'value'));
    }

    /**
     * Test setting the where column clause of the relation.
     */
    public function testWhereColumn()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('whereColumn')->once()->with('column', '!=', 'value');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->whereColumn('column', '!=', 'value'));
    }

    /**
     * Test adding an or where column clause to the relation.
     */
    public function testOrWhereColumn()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('orWhereColumn')->once()->with('column', '!=', 'value');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->orWhereColumn('column', '!=', 'value'));
    }

    /**
     * Test setting the where exists clause of the relation.
     */
    public function testWhereExists()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('whereExists')->once()->with(\Mockery::type(\Closure::class));

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->whereExists(function() {}));
    }

    /**
     * Test adding a where not exists clause to the relation.
     */
    public function testWhereNotExists()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('whereNotExists')->once()->with(\Mockery::type(\Closure::class));

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->whereNotExists(function() {}));
    }

    /**
     * Test adding a group by to the relation.
     */
    public function testGroupBy()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('groupBy')->once()->with('column');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->groupBy('column'));
    }

    /**
     * Test setting the having clause of the relation.
     */
    public function testHaving()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('having')->once()->with('column', '!=', 'value');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->having('column', '!=', 'value'));
    }

    /**
     * Test adding an or having clause to the relation.
     */
    public function testOrHaving()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('orHaving')->once()->with('column', '!=', 'value');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->orHaving('column', '!=', 'value'));
    }

    /**
     * Test adding an order by to the relation.
     */
    public function testOrderBy()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

        $this->mockSelect->shouldReceive('orderBy')->once()->with('column');

        $relation = (new DummyRelationship($this->entity))->has(RelationshipTestRelated::class, 'r', 'foreign_key');
        $this->assertEquals($relation, $relation->orderBy('column'));
    }

    /**
     * Test a relation basic query.
     */
    public function testQuery()
    {
        $this->mockSelect->shouldReceive('columns')->once()->with('r.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('related', 'r')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('r.foreign_key', '=', 5)->andReturnSelf();

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

    public static function getMap()
    {
        return new EntityMap('entity', ['ID' => 'id'], ['db_value' => 'value']);
    }
}
class RelationshipTestRelated extends Entity
{
    public $id;
    public $foreign;

    public static function getMap()
    {
        return new EntityMap('related', ['ID' => 'id'], ['foreign_key' => 'foreign']);
    }
}