<?php

namespace MadeSimple\Database\Tests\Unit;

use MadeSimple\Database\EntityCollection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Tests\TestCase;

class EntityCollectionTest extends TestCase
{
    protected function generateEntityCollection()
    {
        $entities = [];
        for ($i=1; $i<=10; $i++) {
            $entities[] = new EntityCollectionTestEntity(null, [
                'id'     => $i,
                'value' => 'value ' . $i,
                'VALUE' => 'VALUE ' . $i,
            ]);
        }
        return new EntityCollection($entities);
    }

    protected function generateRelationalEntityCollection()
    {
        $entities = [];
        for ($i=1; $i<=10; $i++) {
            $entities[] = new EntityCollectionTestRelationalEntity(null, [
                'id'    => $i,
                'value' => 'value ' . $i,
                'other' => [
                    'field' => 'field ' . $i,
                    'FIELD' => 'FIELD ' . $i,
                ]
            ]);
        }
        return new EntityCollection($entities);
    }


    /**
     * Test Collection::get returns the correct entity.
     */
    public function testGet()
    {
        $collection = $this->generateEntityCollection();

        foreach ($collection->all() as $entity) {
            $this->assertEquals($entity, $collection->get($entity->id));
        }
    }

    /**
     * Test Collection::get returns the default if not matching key is found.
     */
    public function testGetReturnsDefault()
    {
        $collection = $this->generateEntityCollection();

        $this->assertEquals(null, $collection->get(-1));
        $this->assertEquals(false, $collection->get(-1, false));
    }

    /**
     * Test Collection::pluck returns a flattened array if a single column is given.
     */
    public function testPluckSingle()
    {
        $collection = $this->generateEntityCollection();

        $ids     = $collection->pluck('id');
        $value1s = $collection->pluck('value');
        $value2s = $collection->pluck('VALUE');
        foreach ($collection->all() as $k => $entity) {
            $this->assertEquals($entity->id, $ids[$k]);
            $this->assertEquals($entity->value, $value1s[$k]);
            $this->assertEquals($entity->VALUE, $value2s[$k]);
        }
    }

    /**
     * Test Collection::pluck returns an associative array if multiple columns are given.
     */
    public function testPluckMultiple()
    {
        $collection = $this->generateEntityCollection();

        $plucked = $collection->pluck('value', 'VALUE');
        foreach ($collection->all() as $k => $entity) {
            $this->assertArrayHasKey('value', $plucked[$k]);
            $this->assertArrayHasKey('VALUE', $plucked[$k]);
            $this->assertEquals($entity->value, $plucked[$k]['value']);
            $this->assertEquals($entity->VALUE, $plucked[$k]['VALUE']);
        }
    }

    /**
     * Test Collection::pluck returns a flattened array if a single dot notation column is given.
     */
    public function testPluckRelationalSingle()
    {
        $collection = $this->generateRelationalEntityCollection();

        $field1s = $collection->pluck('other.field');
        $field2s = $collection->pluck('other.FIELD');
        foreach ($collection->all() as $k => $entity) {
            /** @var EntityCollectionTestRelationalEntity $entity */
            $this->assertEquals($entity->relation('other')['field'], $field1s[$k]);
            $this->assertEquals($entity->relation('other')['FIELD'], $field2s[$k]);
        }
    }

    /**
     * Test Collection::pluck returns an associative array if a single dot notation column is given.
     */
    public function testPluckRelationalMultiple()
    {
        $collection = $this->generateRelationalEntityCollection();

        $plucked = $collection->pluck('other.field', 'other.FIELD');
        foreach ($collection->all() as $k => $entity) {
            $this->assertArrayHasKey('other', $plucked[$k]);
            $this->assertArrayHasKey('field', $plucked[$k]['other']);
            $this->assertArrayHasKey('FIELD', $plucked[$k]['other']);

            /** @var EntityCollectionTestRelationalEntity $entity */
            $this->assertEquals($entity->relation('other')['field'], $plucked[$k]['other']['field']);
            $this->assertEquals($entity->relation('other')['FIELD'], $plucked[$k]['other']['FIELD']);
        }
    }
}

class EntityCollectionTestEntity extends Entity
{
    public $id;
    public $value;
    public $VALUE;

    protected static function getMap()
    {
        return new EntityMap('table', ['id'], ['value', 'VALUE']);
    }
}

class EntityCollectionTestRelationalEntity extends Entity
{
    use Entity\Relational;

    public $id;
    public $value;

    public function __construct($pool = null, $row = null)
    {
        parent::__construct($pool, $row);

        foreach ($row as $k => $value) {
            if (method_exists($this, $k)) {
                $this->relationships[$k] = $value;
            }
        }
    }

    protected static function getMap()
    {
        return new EntityMap('table', ['id'], ['value']);
    }

    public function other()
    {
        return $this->toOne();
    }
}