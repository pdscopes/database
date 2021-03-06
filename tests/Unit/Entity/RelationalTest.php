<?php

namespace MadeSimple\Database\Tests\Unit\Entity;

use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Relationship;
use MadeSimple\Database\Tests\TestCase;

class RelationalTest extends TestCase
{
    /**
     * Test Relation with no args.
     */
    public function testRelationWithNoArgs()
    {
        /** @var \Mockery\Mock|Entity $mockEntity */
        $mockEntity = \Mockery::mock(Entity::class);
        /** @var \Mockery\Mock|Relationship $mockRelationship */
        $mockRelationship = \Mockery::mock(Relationship::class);
        /** @var \Mockery\Mock|Entity\Relational $mockRelational */
        $mockRelational = \Mockery::mock(RelationTestEntity::class)->makePartial();
        $mockRelational->shouldReceive('foobar')->once()->withNoArgs()->andReturn($mockRelationship);
        $mockRelationship->shouldReceive('fetch')->once()->with()->andReturn($mockEntity);

        // Test the first call
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar'));
        // Test that subsequent calls
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar'));
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar'));
    }

    /**
     * Test Relation with arguments.
     */
    public function testRelationWithArgs()
    {
        /** @var \Mockery\Mock|Entity $mockEntity */
        $mockEntity = \Mockery::mock(Entity::class);
        /** @var \Mockery\Mock|Relationship $mockRelationship */
        $mockRelationship = \Mockery::mock(Relationship::class);
        /** @var \Mockery\Mock|Entity\Relational $mockRelational */
        $mockRelational = \Mockery::mock(RelationTestEntity::class . '[foobar]');
        $mockRelational->shouldReceive('foobar')->once()->with('arg1', 'arg2')->andReturn($mockRelationship);
        $mockRelationship->shouldReceive('fetch')->once()->with()->andReturn($mockEntity);

        // Test the first call
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar', 'arg1', 'arg2'));
        // Test that subsequent calls
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar', 'arg1', 'arg2'));

        // Test subsequent calls with different arguments
        $mockRelational->shouldReceive('foobar')->once()->with()->andReturn($mockRelationship);
        $mockRelationship->shouldReceive('fetch')->once()->with()->andReturn($mockEntity);
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar'));
    }

    /**
     * Test Relation with Entity arguments.
     */
    public function testRelationWithEntityArgs()
    {
        $entity = new RelationTestEntity();
        $entity->id = 11;

        /** @var \Mockery\Mock|Entity $mockEntity */
        $mockEntity = \Mockery::mock(Entity::class);
        /** @var \Mockery\Mock|Relationship $mockRelationship */
        $mockRelationship = \Mockery::mock(Relationship::class);
        /** @var \Mockery\Mock|Entity\Relational $mockRelational */
        $mockRelational = \Mockery::mock(RelationTestEntity::class . '[foobar]');
        $mockRelational->shouldReceive('foobar')->once()->with($entity)->andReturn($mockRelationship);
        $mockRelationship->shouldReceive('fetch')->once()->with()->andReturn($mockEntity);

        // Test the first call
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar', $entity));
        // Test that subsequent calls
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar', $entity));

        // Test subsequent calls with different arguments
        $mockRelational->shouldReceive('foobar')->once()->with()->andReturn($mockRelationship);
        $mockRelationship->shouldReceive('fetch')->once()->with()->andReturn($mockEntity);
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar'));
    }

    /**
     * Test Relate with no args.
     */
    public function testRelateWithNoArgs()
    {
        /** @var \Mockery\Mock|Entity $mockEntity */
        $mockEntity = \Mockery::mock(Entity::class);
        /** @var \Mockery\Mock|Relationship $mockRelationship */
        $mockRelationship = \Mockery::mock(Relationship::class);
        /** @var \Mockery\Mock|Entity\Relational $mockRelational */
        $mockRelational = \Mockery::mock(RelationTestEntity::class)->makePartial();

        // Set the relation
        $mockRelational->relate($mockEntity, 'foobar');

        // Test the first call
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar'));
        // Test that subsequent calls
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar'));
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar'));


        // Test subsequent calls with arguments
        $mockRelational->shouldReceive('foobar')->once()->with('arg1', 'arg2')->andReturn($mockRelationship);
        $mockRelationship->shouldReceive('fetch')->once()->with()->andReturn($mockEntity);
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar', 'arg1', 'arg2'));
    }

    /**
     * Test Relation with arguments.
     */
    public function testRelateWithArgs()
    {
        /** @var \Mockery\Mock|Entity $mockEntity */
        $mockEntity = \Mockery::mock(Entity::class);
        /** @var \Mockery\Mock|Relationship $mockRelationship */
        $mockRelationship = \Mockery::mock(Relationship::class);
        /** @var \Mockery\Mock|Entity\Relational $mockRelational */
        $mockRelational = \Mockery::mock(RelationTestEntity::class . '[foobar]');

        // Set the relation
        $mockRelational->relate($mockEntity, 'foobar', 'arg1', 'arg2');

        // Test the first call
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar', 'arg1', 'arg2'));
        // Test that subsequent calls
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar', 'arg1', 'arg2'));

        // Test subsequent calls with different arguments
        $mockRelational->shouldReceive('foobar')->once()->with()->andReturn($mockRelationship);
        $mockRelationship->shouldReceive('fetch')->once()->with()->andReturn($mockEntity);
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar'));
    }
}
class RelationTestEntity extends Entity
{
    use Entity\Relational;

    public $id;
    public $foreignKey;

    protected static function getMap()
    {
        return new EntityMap('entity', ['ID' => 'id'], ['foreign_key' => 'foreignKey']);
    }

    public function foobar() {}
}