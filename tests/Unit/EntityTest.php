<?php

namespace Tests\Unit;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use Tests\TestCase;

class EntityTest extends TestCase
{
    /**
     * @var \Mockery\Mock|Connection
     */
    private $mockConnection;

    protected function setUp()
    {
        parent::setUp();

        $this->mockConnection = \Mockery::mock(Connection::class);
    }


    /**
     * Test construction with no data.
     */
    public function testConstructNoData()
    {
        $entity = new SingleKeyEntity($this->mockConnection);

        $this->assertNull($entity->id);
        $this->assertNull($entity->firstName);
        $this->assertNull($entity->lastName);
    }

    /**
     * Test construction with data.
     *
     * @param array $data
     * @dataProvider populateDataProvider
     */
    public function testConstructWithData($data)
    {
        $entity = new SingleKeyEntity($this->mockConnection, $data);

        $this->assertEquals($data['ID'], $entity->id);
        $this->assertEquals($data['first_name'], $entity->firstName);
        $this->assertEquals($data['last_name'], $entity->lastName);
    }

    /**
     * Test populate with data.
     *
     * @param array $data
     * @dataProvider populateDataProvider
     */
    public function testPopulate($data)
    {
        $entity = new SingleKeyEntity($this->mockConnection);
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
        $this->mockConnection->shouldReceive('insert')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('into')->once()->with('dummy')->andReturnSelf();
        $this->mockConnection->shouldReceive('columns')->once()->with(['ID', 'first_name', 'last_name'])->andReturnSelf();
        $this->mockConnection->shouldReceive('values')->once()->with([null, 'Test', 'Person'])->andReturnSelf();
        $this->mockConnection->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('lastInsertId')->once()->withNoArgs()->andReturn(5);

        $entity = new SingleKeyEntity($this->mockConnection);
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
        $this->mockConnection->shouldReceive('update')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('table')->once()->with('dummy')->andReturnSelf();
        $this->mockConnection->shouldReceive('columns')->once()->with(['ID', 'first_name', 'last_name'])->andReturnSelf();
        $this->mockConnection->shouldReceive('setParameters')->once()->with([5, 'Test', 'Person'])->andReturnSelf();
        $this->mockConnection->shouldReceive('andWhere')->once()->with('ID = ?', 5);
        $this->mockConnection->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();


        $entity = new SingleKeyEntity($this->mockConnection);
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
        $entity = new CompositeKeyEntity($this->mockConnection);
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
        $this->mockConnection->shouldReceive('insert')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('into')->once()->with('dummy')->andReturnSelf();
        $this->mockConnection->shouldReceive('columns')->once()->with(['ID', 'first_name', 'last_name'])->andReturnSelf();
        $this->mockConnection->shouldReceive('values')->once()->with([null, 'Test', 'Person'])->andReturnSelf();
        $this->mockConnection->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('lastInsertId')->once()->withNoArgs()->andReturn(5);

        $entity = new SingleKeyEntity($this->mockConnection);
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
        $this->mockConnection->shouldReceive('insert')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('into')->once()->with('dummy_link')->andReturnSelf();
        $this->mockConnection->shouldReceive('columns')->once()->with(['user_id', 'company_id', 'value'])->andReturnSelf();
        $this->mockConnection->shouldReceive('values')->once()->with([5, 7, 'value'])->andReturnSelf();
        $this->mockConnection->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('lastInsertId')->never();

        $entity = new CompositeKeyEntity($this->mockConnection);
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
        $this->mockConnection->shouldReceive('update')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('table')->once()->with('dummy')->andReturnSelf();
        $this->mockConnection->shouldReceive('columns')->once()->with(['ID', 'first_name', 'last_name'])->andReturnSelf();
        $this->mockConnection->shouldReceive('setParameters')->once()->with([5, 'Test', 'Person'])->andReturnSelf();
        $this->mockConnection->shouldReceive('andWhere')->once()->with('ID = ?', 5);
        $this->mockConnection->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();


        $entity = new SingleKeyEntity($this->mockConnection);
        $entity->id        = 5;
        $entity->firstName = 'Test';
        $entity->lastName  = 'Person';

        $this->assertTrue($entity->update());
        $this->assertFalse($entity->createdRecently);
    }

    /**
     * Test update with a composite primary key
     */
    public function testUpdateCompositePrimaryKey()
    {
        $this->mockConnection->shouldReceive('update')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('table')->once()->with('dummy_link')->andReturnSelf();
        $this->mockConnection->shouldReceive('columns')->once()->with(['user_id', 'company_id', 'value'])->andReturnSelf();
        $this->mockConnection->shouldReceive('setParameters')->once()->with([5, 7, 'value'])->andReturnSelf();
        $this->mockConnection->shouldReceive('andWhere')->once()->with('user_id = ?', 5);
        $this->mockConnection->shouldReceive('andWhere')->once()->with('company_id = ?', 7);
        $this->mockConnection->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();


        $entity = new CompositeKeyEntity($this->mockConnection);
        $entity->userId    = 5;
        $entity->companyId = 7;
        $entity->value     = 'value';

        $this->assertTrue($entity->update());
        $this->assertFalse($entity->createdRecently);
    }

    /**
     * Test read with a single primary key.
     */
    public function testReadSinglePrimaryKey()
    {
        $row = [
            'ID'         => 5,
            'first_name' => 'Test',
            'last_name'  => 'Person',
        ];

        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('columns')->once()->with('*')->andReturnSelf();
        $this->mockConnection->shouldReceive('from')->once()->with('dummy', 't')->andReturnSelf();
        $this->mockConnection->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockConnection->shouldReceive('andWhere')->once()->with('t.ID = ?', 5);
        $this->mockConnection->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('fetch')->once()->with(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_FIRST)->andReturn($row);

        $entity = new SingleKeyEntity($this->mockConnection);

        $this->assertTrue($entity->read(null, 5));
        $this->assertEquals(5, $entity->id);
        $this->assertEquals('Test', $entity->firstName);
        $this->assertEquals('Person', $entity->lastName);
    }

    /**
     * Test read with a composite primary key.
     */
    public function testReadCompositePrimaryKey()
    {
        $row = [
            'user_id'    => 5,
            'company_id' => 7,
            'value'      => 'value',
        ];

        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('columns')->once()->with('*')->andReturnSelf();
        $this->mockConnection->shouldReceive('from')->once()->with('dummy_link', 't')->andReturnSelf();
        $this->mockConnection->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockConnection->shouldReceive('andWhere')->once()->with('t.user_id = ?', 5);
        $this->mockConnection->shouldReceive('andWhere')->once()->with('t.company_id = ?', 7);
        $this->mockConnection->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('fetch')->once()->with(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_FIRST)->andReturn($row);

        $entity = new CompositeKeyEntity($this->mockConnection);

        $this->assertTrue($entity->read(null, ['user_id' => 5, 'company_id' => 7]));
        $this->assertEquals(5, $entity->userId);
        $this->assertEquals(7, $entity->companyId);
        $this->assertEquals('value', $entity->value);
    }

    /**
     * Test delete with a single primary key.
     */
    public function testDeleteSinglePrimaryKey()
    {
        $this->mockConnection->shouldReceive('delete')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('from')->once()->with('dummy')->andReturnSelf();
        $this->mockConnection->shouldReceive('andWhere')->once()->with('ID = ?', 5)->andReturnSelf();
        $this->mockConnection->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();

        $entity = new SingleKeyEntity($this->mockConnection);
        $this->assertTrue($entity->delete(null, 5));
    }

    /**
     * Test delete with a composite primary key.
     */
    public function testDeleteCompositePrimaryKey()
    {
        $this->mockConnection->shouldReceive('delete')->once()->withNoArgs()->andReturnSelf();
        $this->mockConnection->shouldReceive('from')->once()->with('dummy_link')->andReturnSelf();
        $this->mockConnection->shouldReceive('andWhere')->once()->with('user_id = ?', 5)->andReturnSelf();
        $this->mockConnection->shouldReceive('andWhere')->once()->with('company_id = ?', 7)->andReturnSelf();
        $this->mockConnection->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();

        $entity = new CompositeKeyEntity($this->mockConnection);
        $this->assertTrue($entity->delete(null, ['user_id' => 5, 'company_id' => 7]));
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
        $entity = new SingleKeyEntity($this->mockConnection, $row);
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
        $entity = new SingleKeyEntity($this->mockConnection, $row);
        $this->assertEquals($array, $entity->toArray());
    }

    /**
     * Test entity properties are casted with converted to an array.
     */
    public function testCastProperty()
    {
        $entity = new CastedEntity($this->mockConnection, [
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

    public  function getMap()
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

    public function getMap()
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

    public function getMap()
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