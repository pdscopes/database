<?php

namespace Tests\Unit;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Statement\Query\Select;
use MadeSimple\Database\Repository;
use Tests\TestCase;

class RepositoryTest extends TestCase
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

    protected function setUp()
    {
        parent::setUp();

        $this->mockSelect     = \Mockery::mock(Select::class);
        $this->mockConnection = \Mockery::mock(Connection::class);
        $this->mockPool       = \Mockery::mock(Pool::class);
    }


    /**
     * Test find by.
     */
    public function testFindBy()
    {
        $row = ['ID' => 1, 'db_value' => 'value'];

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('orderBy')->once()->with([])->andReturnSelf();
        $this->mockSelect->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();
        $this->mockSelect->shouldReceive('fetch')->times(2)->with(\PDO::FETCH_ASSOC)->andReturnValues([$row, null]);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $items      = $repository->findBy();

        $this->assertInternalType('array', $items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(RepositoryEntity::class, $items[0]);
    }

    /**
     * Test find by with a column.
     */
    public function testFindByWithColumn()
    {
        $row    = ['ID' => 1, 'db_value' => 'value'];

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('t.column = ?', 'value')->andReturnSelf();
        $this->mockSelect->shouldReceive('orderBy')->once()->with([])->andReturnSelf();
        $this->mockSelect->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();
        $this->mockSelect->shouldReceive('fetch')->times(2)->with(\PDO::FETCH_ASSOC)->andReturnValues([$row, null]);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $items      = $repository->findBy(['column' => 'value']);

        $this->assertInternalType('array', $items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(RepositoryEntity::class, $items[0]);
    }

    /**
     * Test find by with columns.
     */
    public function testFindByWithColumns()
    {
        $row    = ['ID' => 1, 'db_value' => 'value'];

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('t.c1 = ?', 'v1')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('t.c2 = ?', 'v2')->andReturnSelf();
        $this->mockSelect->shouldReceive('orderBy')->once()->with([])->andReturnSelf();
        $this->mockSelect->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();
        $this->mockSelect->shouldReceive('fetch')->times(2)->with(\PDO::FETCH_ASSOC)->andReturnValues([$row, null]);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $items      = $repository->findBy(['c1' => 'v1', 'c2' => 'v2']);

        $this->assertInternalType('array', $items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(RepositoryEntity::class, $items[0]);
    }

    /**
     * Test find by with order.
     */
    public function testFindByWithOrder()
    {
        $row    = ['ID' => 1, 'db_value' => 'value'];

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('orderBy')->once()->with(['o1'])->andReturnSelf();
        $this->mockSelect->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();
        $this->mockSelect->shouldReceive('fetch')->times(2)->with(\PDO::FETCH_ASSOC)->andReturnValues([$row, null]);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $items      = $repository->findBy([], ['o1']);

        $this->assertInternalType('array', $items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(RepositoryEntity::class, $items[0]);
    }



    /**
     * Test find by.
     */
    public function testFindOneBy()
    {
        $row = ['ID' => 1, 'db_value' => 'value'];

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldReceive('orderBy')->once()->with([])->andReturnSelf();
        $this->mockSelect->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();
        $this->mockSelect->shouldReceive('fetch')->once()->with(\PDO::FETCH_ASSOC)->andReturn($row);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $item       = $repository->findOneBy();

        $this->assertInstanceOf(RepositoryEntity::class, $item);
    }

    /**
     * Test find by with a column.
     */
    public function testFindOneByWithColumn()
    {
        $row    = ['ID' => 1, 'db_value' => 'value'];

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('t.column = ?', 'value')->andReturnSelf();
        $this->mockSelect->shouldReceive('orderBy')->once()->with([])->andReturnSelf();
        $this->mockSelect->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();
        $this->mockSelect->shouldReceive('fetch')->once()->with(\PDO::FETCH_ASSOC)->andReturn($row);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $item       = $repository->findOneBy(['column' => 'value']);

        $this->assertInstanceOf(RepositoryEntity::class, $item);
    }

    /**
     * Test find by with columns.
     */
    public function testFindOneByWithColumns()
    {
        $row    = ['ID' => 1, 'db_value' => 'value'];

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('t.c1 = ?', 'v1')->andReturnSelf();
        $this->mockSelect->shouldReceive('andWhere')->once()->with('t.c2 = ?', 'v2')->andReturnSelf();
        $this->mockSelect->shouldReceive('orderBy')->once()->with([])->andReturnSelf();
        $this->mockSelect->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();
        $this->mockSelect->shouldReceive('fetch')->once()->with(\PDO::FETCH_ASSOC)->andReturn($row);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $item       = $repository->findOneBy(['c1' => 'v1', 'c2' => 'v2']);

        $this->assertInstanceOf(RepositoryEntity::class, $item);
    }

    /**
     * Test find by with order.
     */
    public function testFindOneByWithOrder()
    {
        $row    = ['ID' => 1, 'db_value' => 'value'];

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldReceive('orderBy')->once()->with(['o1'])->andReturnSelf();
        $this->mockSelect->shouldReceive('execute')->once()->withNoArgs()->andReturnSelf();
        $this->mockSelect->shouldReceive('fetch')->once()->with(\PDO::FETCH_ASSOC)->andReturn($row);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $item       = $repository->findOneBy([], ['o1']);

        $this->assertInstanceOf(RepositoryEntity::class, $item);
    }
}
class RepositoryEntity extends Entity
{
    public $id;
    public $value;
    public  function getMap()
    {
        return new EntityMap('repo', ['ID' => 'id'], ['db_value' => 'value']);
    }
}