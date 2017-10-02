<?php

namespace MadeSimple\Database\Tests\Unit\Relationship;

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
        /** @var \Mockery\Mock|Relationship\Relational $mockRelational */
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
        /** @var \Mockery\Mock|Relationship\Relational $mockRelational */
        $mockRelational = \Mockery::mock(RelationTestEntity::class . '[foobar]');
        $mockRelational->shouldReceive('foobar')->once()->with('arg1', 'arg2')->andReturn($mockRelationship);
        $mockRelationship->shouldReceive('fetch')->once()->with()->andReturn($mockEntity);

        // Test the first call
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar', 'arg1', 'arg2'));
        // Test that subsequent calls
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar', 'arg1', 'arg2'));
        $this->assertEquals($mockEntity, $mockRelational->relation('foobar'));
    }
}
class RelationTestEntity extends Entity
{
    use Relationship\Relational;

    public $id;
    public $foreignKey;

    protected static function getMap()
    {
        return new EntityMap('entity', ['ID' => 'id'], ['foreign_key' => 'foreignKey']);
    }

    public function foobar() {}
}