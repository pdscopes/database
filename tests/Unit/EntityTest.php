<?php

namespace MadeSimple\Database\Tests\Unit;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Query;
use MadeSimple\Database\Tests\TestCase;

class EntityTest extends TestCase
{
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

        $this->mockConnection = \Mockery::mock(Connection::class);
        $this->mockPool       = \Mockery::mock(Pool::class);
    }


    /**
     * Test construction with no remap.
     */
    public function testConstructNoRemap()
    {
        $entity = new SingleKeyEntity($this->mockPool);

        $this->assertNull($entity->id);
        $this->assertNull($entity->firstName);
        $this->assertNull($entity->lastName);
    }

    /**
     * Test construction with remap.
     *
     * @param array $data
     * @dataProvider populateDataProvider
     */
    public function testConstructWithRemap($data)
    {
        $entity = new SingleKeyEntity();
        foreach ($data as $k => $v) {
            $entity->{$k} = $v;
        }
        $entity->__construct($this->mockPool, true);

        $this->assertEquals($data['ID'], $entity->id);
        $this->assertEquals($data['first_name'], $entity->firstName);
        $this->assertEquals($data['last_name'], $entity->lastName);
    }

    /**
     * Test magic method __sleep only returns the primary keys.
     */
    public function testSleep()
    {
        $entity = (new SingleKeyEntity)->populate(['ID' => 4, 'first_name' => 'firstName', 'last_name' => 'lastName']);

        $this->assertEquals(['ID' => 'id'], $entity->__sleep());
    }

    /**
     * Test that faux magic method wakeup calls Entity::setPool and then Entity::read();
     */
    public function testWakeup()
    {
        /** @var \Mockery\Mock $mockSelect */
        $mockSelect = \Mockery::mock(Query\Insert::class);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($mockSelect);
        $mockSelect->shouldReceive('from')->once()->with('dummy')->andReturnSelf();
        $mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $mockSelect->shouldReceive('where')->once()->with('ID', '=', 5);
        $mockSelect->shouldReceive('query')->once()->withNoArgs()->andReturnSelf();
        $mockSelect->shouldReceive('fetch')->once()->with(\PDO::FETCH_ASSOC)->andReturn([]);

        $entity = new SingleKeyEntity;
        $entity->id = 5;

        $this->assertNull($entity->pool);

        $this->assertInstanceOf(SingleKeyEntity::class, $entity->wakeup($this->mockPool));
        $this->assertEquals($this->mockPool, $entity->pool);
    }

    /**
     * Test that setPool sets the pool and returns the Entity.
     */
    public function testSetPool()
    {
        $entity = new SingleKeyEntity;

        $this->assertNull($entity->pool);

        $this->assertInstanceOf(SingleKeyEntity::class, $entity->setPool($this->mockPool));
        $this->assertEquals($this->mockPool, $entity->pool);
    }

    /**
     * Test populate with data.
     *
     * @param array $data
     * @dataProvider populateDataProvider
     */
    public function testPopulate($data)
    {
        $entity = new SingleKeyEntity($this->mockPool);
        $entity->populate($data);

        $this->assertEquals($data['ID'], $entity->id);
        $this->assertEquals($data['first_name'], $entity->firstName);
        $this->assertEquals($data['last_name'], $entity->lastName);
    }

    /**
     * Test persist creates an entity that does not have a primary key set.
     */
    public function testPersistSinglePrimaryKeyCreate()
    {
        /** @var \Mockery\Mock $mockInsert */
        $mockInsert = \Mockery::mock(Query\Insert::class);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('insert')->once()->withNoArgs()->andReturn($mockInsert);
        $mockInsert->shouldReceive('into')->once()->with('dummy')->andReturnSelf();
        $mockInsert->shouldReceive('columns')->once()->with(['first_name', 'last_name'])->andReturnSelf();
        $mockInsert->shouldReceive('values')->once()->with(['Test', 'Person'])->andReturnSelf();
        $mockInsert->shouldReceive('query')->once()->withNoArgs()->andReturnSelf();
        $mockInsert->shouldReceive('lastInsertId')->once()->withNoArgs()->andReturn(5);

        $entity = new SingleKeyEntity($this->mockPool);
        $entity->firstName = 'Test';
        $entity->lastName  = 'Person';

        $this->assertTrue($entity->persist());
        $this->assertEquals(5, $entity->id);
        $this->assertTrue($entity->createdRecently);
    }

    /**
     * Test persist updates an entity that does have a primary key set.
     */
    public function testPersistSinglePrimaryKeyUpdate()
    {
        /** @var \Mockery\Mock $mockUpdate */
        $mockUpdate = \Mockery::mock(Query\Update::class);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('update')->once()->withNoArgs()->andReturn($mockUpdate);
        $mockUpdate->shouldReceive('table')->once()->with('dummy')->andReturnSelf();
        $mockUpdate->shouldReceive('set')->once()->with(['first_name' => 'Test', 'last_name' => 'Person'])->andReturnSelf();
        $mockUpdate->shouldReceive('where')->once()->with('ID', '=', 5);
        $mockUpdate->shouldReceive('query')->once()->withNoArgs()->andReturnSelf();
        $mockUpdate->shouldReceive('affectedRows')->once()->withNoArgs()->andReturn(1);


        $entity = new SingleKeyEntity($this->mockPool);
        $entity->id        = 5;
        $entity->firstName = 'Test';
        $entity->lastName  = 'Person';

        $this->assertTrue($entity->update());
        $this->assertFalse($entity->createdRecently);
    }

    /**
     * Test persist throws an exception when attempting to persist a composite key entity.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot persist
     */
    public function testPersistCompositePrimaryKey()
    {
        $entity = new CompositeKeyEntity($this->mockPool);
        $entity->userId    = 5;
        $entity->companyId = 7;
        $entity->value     = 'value';

        $entity->persist();
    }

    /**
     * Test create with a single primary key.
     */
    public function testCreateSinglePrimaryKey()
    {
        /** @var \Mockery\Mock $mockInsert */
        $mockInsert = \Mockery::mock(Query\Insert::class);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('insert')->once()->withNoArgs()->andReturn($mockInsert);
        $mockInsert->shouldReceive('into')->once()->with('dummy')->andReturnSelf();
        $mockInsert->shouldReceive('columns')->once()->with(['first_name', 'last_name'])->andReturnSelf();
        $mockInsert->shouldReceive('values')->once()->with(['Test', 'Person'])->andReturnSelf();
        $mockInsert->shouldReceive('query')->once()->withNoArgs()->andReturnSelf();
        $mockInsert->shouldReceive('lastInsertId')->once()->withNoArgs()->andReturn(5);

        $entity = new SingleKeyEntity($this->mockPool);
        $entity->firstName = 'Test';
        $entity->lastName  = 'Person';

        $this->assertTrue($entity->create());
        $this->assertEquals(5, $entity->id);
        $this->assertTrue($entity->createdRecently);
    }

    /**
     * Test create with a composite primary key.
     */
    public function testCreateCompositePrimaryKey()
    {
        /** @var \Mockery\Mock $mockInsert */
        $mockInsert = \Mockery::mock(Query\Insert::class);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('insert')->once()->withNoArgs()->andReturn($mockInsert);
        $mockInsert->shouldReceive('into')->once()->with('dummy_link')->andReturnSelf();
        $mockInsert->shouldReceive('columns')->once()->with(['user_id', 'company_id', 'value'])->andReturnSelf();
        $mockInsert->shouldReceive('values')->once()->with([5, 7, 'value'])->andReturnSelf();
        $mockInsert->shouldReceive('query')->once()->withNoArgs()->andReturnSelf();
        $mockInsert->shouldReceive('lastInsertId')->never();

        $entity = new CompositeKeyEntity($this->mockPool);
        $entity->userId    = 5;
        $entity->companyId = 7;
        $entity->value     = 'value';

        $this->assertTrue($entity->create());
        $this->assertTrue($entity->createdRecently);
    }

    /**
     * Test update with a single primary key
     */
    public function testUpdateSinglePrimaryKey()
    {
        /** @var \Mockery\Mock $mockUpdate */
        $mockUpdate = \Mockery::mock(Query\Update::class);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('update')->once()->withNoArgs()->andReturn($mockUpdate);
        $mockUpdate->shouldReceive('table')->once()->with('dummy')->andReturnSelf();
        $mockUpdate->shouldReceive('set')->once()->with(['first_name' => 'Test', 'last_name' => 'Person'])->andReturnSelf();
        $mockUpdate->shouldReceive('where')->once()->with('ID', '=', 5);
        $mockUpdate->shouldReceive('query')->once()->withNoArgs()->andReturnSelf();
        $mockUpdate->shouldReceive('affectedRows')->once()->withNoArgs()->andReturn(1);


        $entity = new SingleKeyEntity($this->mockPool);
        $entity->id        = 5;
        $entity->firstName = 'Test';
        $entity->lastName  = 'Person';

        $this->assertTrue($entity->update());
        $this->assertFalse($entity->createdRecently);
    }

    /**
     * Test update a subset of properties on an entity with a single primary key.
     */
    public function testUpdateSinglePrimaryKeyPropertiesSubset()
    {
        /** @var \Mockery\Mock $mockUpdate */
        $mockUpdate = \Mockery::mock(Query\Update::class);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('update')->once()->withNoArgs()->andReturn($mockUpdate);
        $mockUpdate->shouldReceive('table')->once()->with('dummy')->andReturnSelf();
        $mockUpdate->shouldReceive('set')->once()->with(['first_name' => 'Test', 'last_name' => 'Person'])->andReturnSelf();
        $mockUpdate->shouldReceive('where')->once()->with('ID', '=', 5);
        $mockUpdate->shouldReceive('query')->once()->withNoArgs()->andReturnSelf();
        $mockUpdate->shouldReceive('affectedRows')->once()->withNoArgs()->andReturn(1);


        $entity = new SingleKeyEntity($this->mockPool);
        $entity->id        = 5;
        $entity->firstName = 'Test';
        $entity->lastName  = 'Person';

        $this->assertTrue($entity->update(['firstName', 'lastName']));
        $this->assertFalse($entity->createdRecently);
    }

    /**
     * Test update with a composite primary key
     */
    public function testUpdateCompositePrimaryKey()
    {
        /** @var \Mockery\Mock $mockUpdate */
        $mockUpdate = \Mockery::mock(Query\Update::class);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('update')->once()->withNoArgs()->andReturn($mockUpdate);
        $mockUpdate->shouldReceive('table')->once()->with('dummy_link')->andReturnSelf();
        $mockUpdate->shouldReceive('set')->once()->with(['value' => 'value'])->andReturnSelf();
        $mockUpdate->shouldReceive('where')->once()->with('user_id', '=', 5);
        $mockUpdate->shouldReceive('where')->once()->with('company_id', '=', 7);
        $mockUpdate->shouldReceive('query')->once()->withNoArgs()->andReturnSelf();
        $mockUpdate->shouldReceive('affectedRows')->once()->withNoArgs()->andReturn(1);


        $entity = new CompositeKeyEntity($this->mockPool);
        $entity->userId    = 5;
        $entity->companyId = 7;
        $entity->value     = 'value';

        $this->assertTrue($entity->update());
        $this->assertFalse($entity->createdRecently);
    }

    /**
     * Test update a subset of properties on an entity with a composite primary key
     */
    public function testUpdateCompositePrimaryKeyPropertiesSubset()
    {
        /** @var \Mockery\Mock $mockUpdate */
        $mockUpdate = \Mockery::mock(Query\Update::class);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('update')->once()->withNoArgs()->andReturn($mockUpdate);
        $mockUpdate->shouldReceive('table')->once()->with('dummy_link')->andReturnSelf();
        $mockUpdate->shouldReceive('set')->once()->with(['value' => 'value'])->andReturnSelf();
        $mockUpdate->shouldReceive('where')->once()->with('user_id', '=', 5);
        $mockUpdate->shouldReceive('where')->once()->with('company_id', '=', 7);
        $mockUpdate->shouldReceive('query')->once()->withNoArgs()->andReturnSelf();
        $mockUpdate->shouldReceive('affectedRows')->once()->withNoArgs()->andReturn(1);


        $entity = new CompositeKeyEntity($this->mockPool);
        $entity->userId    = 5;
        $entity->companyId = 7;
        $entity->value     = 'value';

        $this->assertTrue($entity->update('value'));
        $this->assertFalse($entity->createdRecently);
    }

    /**
     * Test read with a single primary key.
     */
    public function testReadSinglePrimaryKey()
    {
        /** @var \Mockery\Mock $mockSelect */
        $mockSelect = \Mockery::mock(Query\Select::class);

        $row = [
            'ID'         => 5,
            'first_name' => 'Test',
            'last_name'  => 'Person',
        ];

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($mockSelect);
        $mockSelect->shouldReceive('from')->once()->with('dummy')->andReturnSelf();
        $mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $mockSelect->shouldReceive('where')->once()->with('ID', '=', 5);
        $mockSelect->shouldReceive('query')->once()->withNoArgs()->andReturnSelf();
        $mockSelect->shouldReceive('fetch')->once()->with(\PDO::FETCH_ASSOC)->andReturn($row);

        $entity = new SingleKeyEntity($this->mockPool);

        $this->assertInstanceOf(SingleKeyEntity::class, $entity->read(5));
        $this->assertEquals(5, $entity->id);
        $this->assertEquals('Test', $entity->firstName);
        $this->assertEquals('Person', $entity->lastName);
    }

    /**
     * Test read with a composite primary key.
     */
    public function testReadCompositePrimaryKey()
    {
        /** @var \Mockery\Mock $mockSelect */
        $mockSelect = \Mockery::mock(Query\Select::class);

        $row = [
            'user_id'    => 5,
            'company_id' => 7,
            'value'      => 'value',
        ];

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($mockSelect);
        $mockSelect->shouldReceive('from')->once()->with('dummy_link')->andReturnSelf();
        $mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $mockSelect->shouldReceive('where')->once()->with('user_id', '=', 5);
        $mockSelect->shouldReceive('where')->once()->with('company_id', '=', 7);
        $mockSelect->shouldReceive('query')->once()->withNoArgs()->andReturnSelf();
        $mockSelect->shouldReceive('fetch')->once()->with(\PDO::FETCH_ASSOC)->andReturn($row);

        $entity = new CompositeKeyEntity($this->mockPool);

        $this->assertInstanceOf(CompositeKeyEntity::class, $entity->read(['user_id' => 5, 'company_id' => 7]));
        $this->assertEquals(5, $entity->userId);
        $this->assertEquals(7, $entity->companyId);
        $this->assertEquals('value', $entity->value);
    }

    /**
     * Test delete with a single primary key.
     */
    public function testDeleteSinglePrimaryKey()
    {
        /** @var \Mockery\Mock $mockDelete */
        $mockDelete = \Mockery::mock(Query\Delete::class);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('delete')->once()->withNoArgs()->andReturn($mockDelete);
        $mockDelete->shouldReceive('from')->once()->with('dummy')->andReturnSelf();
        $mockDelete->shouldReceive('where')->once()->with('ID', '=', 5)->andReturnSelf();
        $mockDelete->shouldReceive('query')->once()->withNoArgs()->andReturnSelf();
        $mockDelete->shouldReceive('affectedRows')->once()->withNoArgs()->andReturn(1);

        $entity = new SingleKeyEntity($this->mockPool);
        $this->assertTrue($entity->delete(5));
    }

    /**
     * Test delete with a composite primary key.
     */
    public function testDeleteCompositePrimaryKey()
    {
        /** @var \Mockery\Mock $mockDelete */
        $mockDelete = \Mockery::mock(Query\Delete::class);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('delete')->once()->withNoArgs()->andReturn($mockDelete);
        $mockDelete->shouldReceive('from')->once()->with('dummy_link')->andReturnSelf();
        $mockDelete->shouldReceive('where')->once()->with('user_id', '=', 5)->andReturnSelf();
        $mockDelete->shouldReceive('where')->once()->with('company_id', '=', 7)->andReturnSelf();
        $mockDelete->shouldReceive('query')->once()->withNoArgs()->andReturnSelf();
        $mockDelete->shouldReceive('affectedRows')->once()->withNoArgs()->andReturn(1);

        $entity = new CompositeKeyEntity($this->mockPool);
        $this->assertTrue($entity->delete(['user_id' => 5, 'company_id' => 7]));
    }

    /**
     * Test find with non-primary, unique column.
     */
    public function testFind()
    {
        /** @var \Mockery\Mock $mockSelect */
        $mockSelect = \Mockery::mock(Query\Select::class);

        $row = [
            'ID'         => 5,
            'UUID'       => '123',
            'first_name' => 'Test',
            'last_name'  => 'Person',
        ];
        $entity = (new SingleKeyEntity($this->mockPool))->populate($row);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($mockSelect);
        $mockSelect->shouldReceive('columns')->once()->with('*')->andReturnSelf();
        $mockSelect->shouldReceive('from')->once()->with('dummy', 't')->andReturnSelf();
        $mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $mockSelect->shouldReceive('where')->once()->with('t.UUID', '=', '123');
        $mockSelect->shouldReceive('query')->once()->withNoArgs()->andReturnSelf();
        $mockSelect->shouldReceive('fetch')->once()->with(\PDO::FETCH_CLASS, SingleKeyEntity::class, [$this->mockPool, true])->andReturn($entity);

        $entity = SingleKeyEntity::find($this->mockPool, ['UUID' => '123']);

        $this->assertInstanceOf(SingleKeyEntity::class, $entity);
        $this->assertEquals(5, $entity->id);
        $this->assertEquals('Test', $entity->firstName);
        $this->assertEquals('Person', $entity->lastName);
    }


    /**
     * Test to array.
     *
     * @param array $row
     * @param array $array
     * @dataProvider populateDataProvider
     */
    public function testJsonSerialize($row, $array)
    {
        $entity = (new SingleKeyEntity)->populate($row);
        $this->assertEquals($array, $entity->jsonSerialize());
    }

    /**
     * Test to array.
     *
     * @param array $row
     * @param array $array
     * @dataProvider populateDataProvider
     */
    public function testToArray($row, $array)
    {
        $entity = (new SingleKeyEntity)->populate($row);
        $this->assertEquals($array, $entity->toArray());
    }

    /**
     * Test entity properties are casted with converted to an array.
     */
    public function testToArrayCastProperty()
    {
        $entity = (new CastedEntity)->populate([
            'int'     => '5',
            'integer' => '7',
            'bool'    => '1',
            'boolean' => '1',
            'double'  => '1.1',
            'float'   => '1.2',
            'real'    => '1.3',
            'string'  => 5,
            'array'   => 'array',
            'json'    => '{"mapped": "property"}',
        ]);

        $array = $entity->toArray();
        $this->assertTrue(is_array($entity->toArray()));
        $this->assertTrue(is_int($array['int']));
        $this->assertTrue(is_integer($array['integer']));
        $this->assertTrue(is_bool($array['bool']));
        $this->assertTrue(is_bool($array['boolean']));
        $this->assertTrue(is_double($array['double']));
        $this->assertTrue(is_float($array['float']));
        $this->assertTrue(is_real($array['real']));
        $this->assertTrue(is_string($array['string']));
        $this->assertTrue(is_array($array['array']));
        $this->assertTrue(is_array($array['json']));
    }

    /**
     * Test entity cast.
     */
    public function testCast()
    {
        $entity = (new CastedEntity)->populate([
            'int'     => '5',
            'integer' => '7',
            'bool'    => '1',
            'boolean' => '1',
            'double'  => '1.1',
            'float'   => '1.2',
            'real'    => '1.3',
            'string'  => 5,
            'array'   => 'array',
            'json'    => '{"mapped": "property"}',
        ]);

        $this->assertTrue(is_int($entity->cast('int')));
        $this->assertTrue(is_integer($entity->cast('integer')));
        $this->assertTrue(is_bool($entity->cast('bool')));
        $this->assertTrue(is_bool($entity->cast('boolean')));
        $this->assertTrue(is_double($entity->cast('double')));
        $this->assertTrue(is_float($entity->cast('float')));
        $this->assertTrue(is_real($entity->cast('real')));
        $this->assertTrue(is_string($entity->cast('string')));
        $this->assertTrue(is_array($entity->cast('array')));
        $this->assertTrue(is_array($entity->cast('json')));
    }


    public function populateDataProvider()
    {
        return [
            [['ID' => 5, 'first_name' => 'Test', 'last_name' => 'Person'], ['id' => 5, 'firstName' => 'Test', 'lastName' => 'Person']],
            [['ID' => null, 'first_name' => 'Test', 'last_name' => 'Person'], ['id' => null, 'firstName' => 'Test', 'lastName' => 'Person']],
            [['ID' => 5, 'first_name' => null, 'last_name' => 'Person'], ['id' => 5, 'firstName' => null, 'lastName' => 'Person']],
            [['ID' => 5, 'first_name' => 'Test', 'last_name' => null], ['id' => 5, 'firstName' => 'Test', 'lastName' => null]],
        ];
    }
}
class SingleKeyEntity extends Entity
{
    public $id;
    public $firstName;
    public $lastName;

    protected static function getMap()
    {
        return new EntityMap(
            'dummy',
            ['ID' => 'id'],
            ['first_name' => 'firstName', 'last_name' => 'lastName']
        );
    }
}
class CompositeKeyEntity extends Entity
{
    public $userId;
    public $companyId;
    public $value;

    protected static function getMap()
    {
        return new EntityMap(
            'dummy_link',
            ['user_id' => 'userId', 'company_id' => 'companyId'],
            ['value' => 'value']
        );
    }
}
class CastedEntity extends Entity
{
    public $int;
    public $integer;
    public $bool;
    public $boolean;
    public $double;
    public $float;
    public $real;
    public $string;
    public $array;
    public $json;

    protected $casts = [
        'int'     => 'int',
        'integer' => 'integer',
        'bool'    => 'bool',
        'boolean' => 'boolean',
        'double'  => 'double',
        'float'   => 'float',
        'real'    => 'real',
        'string'  => 'string',
        'array'   => 'array',
        'json'    => 'json',
    ];

    protected static function getMap()
    {
        return new EntityMap(
            'casted',
            [],
            [
                'int'     => 'int',
                'integer' => 'integer',
                'bool'    => 'bool',
                'boolean' => 'boolean',
                'double'  => 'double',
                'float'   => 'float',
                'real'    => 'real',
                'string'  => 'string',
                'array'   => 'array',
                'json'    => 'json',
            ]
        );
    }
}