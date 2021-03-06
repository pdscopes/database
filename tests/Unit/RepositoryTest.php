<?php

namespace MadeSimple\Database\Tests\Unit;

use MadeSimple\Database\EntityCollection;
use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Query\Select;
use MadeSimple\Database\Repository;
use MadeSimple\Database\Tests\TestCase;

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

    protected function setUp(): void
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
        $fetched = (new RepositoryEntity)->populate(['ID' => 1, 'db_value' => 'value']);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('t.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldNotReceive('where');
        $this->mockSelect->shouldNotReceive('orderBy');
        $this->mockSelect->shouldReceive('fetchAll')->once()
            ->with(\PDO::FETCH_CLASS, RepositoryEntity::class, [$this->mockPool, true])->andReturn([$fetched]);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $items      = $repository->findBy();

        $this->assertInstanceOf(EntityCollection::class, $items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(RepositoryEntity::class, $items[0]);
    }

    /**
     * Test find by with a column.
     */
    public function testFindByWithColumn()
    {
        $fetched = (new RepositoryEntity)->populate(['ID' => 1, 'db_value' => 'value']);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('t.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('t.column', '=', 'value')->andReturnSelf();
        $this->mockSelect->shouldNotReceive('orderBy');
        $this->mockSelect->shouldReceive('fetchAll')->once()
            ->with(\PDO::FETCH_CLASS, RepositoryEntity::class, [$this->mockPool, true])->andReturn([$fetched]);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $items      = $repository->findBy(['column' => 'value']);

        $this->assertInstanceOf(EntityCollection::class, $items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(RepositoryEntity::class, $items[0]);
    }

    /**
     * Test find by with column in.
     */
    public function testFindByWithColumnIn()
    {
        $fetched = (new RepositoryEntity)->populate(['ID' => 1, 'db_value' => 'value']);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('t.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('t.c1', '=', ['v1', 'v2'])->andReturnSelf();
        $this->mockSelect->shouldNotReceive('orderBy');
        $this->mockSelect->shouldReceive('fetchAll')->once()
            ->with(\PDO::FETCH_CLASS, RepositoryEntity::class, [$this->mockPool, true])->andReturn([$fetched]);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $items      = $repository->findBy(['c1' => ['v1', 'v2']]);

        $this->assertInstanceOf(EntityCollection::class, $items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(RepositoryEntity::class, $items[0]);
    }

    /**
     * Test find by with columns.
     */
    public function testFindByWithColumns()
    {
        $fetched = (new RepositoryEntity)->populate(['ID' => 1, 'db_value' => 'value']);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('t.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('t.c1', '=', 'v1')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('t.c2', '=', 'v2')->andReturnSelf();
        $this->mockSelect->shouldNotReceive('orderBy');
        $this->mockSelect->shouldReceive('fetchAll')->once()
            ->with(\PDO::FETCH_CLASS, RepositoryEntity::class, [$this->mockPool, true])->andReturn([$fetched]);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $items      = $repository->findBy(['c1' => 'v1', 'c2' => 'v2']);

        $this->assertInstanceOf(EntityCollection::class, $items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(RepositoryEntity::class, $items[0]);
    }

    /**
     * Test find by with order.
     */
    public function testFindByWithOrder()
    {
        $fetched = (new RepositoryEntity)->populate(['ID' => 1, 'db_value' => 'value']);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('t.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('orderBy')->once()->with('o1', 'asc')->andReturnSelf();
        $this->mockSelect->shouldReceive('fetchAll')->once()
            ->with(\PDO::FETCH_CLASS, RepositoryEntity::class, [$this->mockPool, true])->andReturn([$fetched]);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $items      = $repository->findBy([], ['o1']);

        $this->assertInstanceOf(EntityCollection::class, $items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(RepositoryEntity::class, $items[0]);
    }



    /**
     * Test find by.
     */
    public function testFindOneBy()
    {
        $fetched = (new RepositoryEntity)->populate(['ID' => 1, 'db_value' => 'value']);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('t.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldNotReceive('where');
        $this->mockSelect->shouldNotReceive('orderBy');
        $this->mockSelect->shouldReceive('setFetchMode')->once()
            ->with(\PDO::FETCH_CLASS, RepositoryEntity::class, [$this->mockPool, true])->andReturnSelf();
        $this->mockSelect->shouldReceive('fetch')->once()->withNoArgs()->andReturn($fetched);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $item       = $repository->findOneBy();

        $this->assertInstanceOf(RepositoryEntity::class, $item);
    }

    /**
     * Test find by with a column.
     */
    public function testFindOneByWithColumn()
    {
        $fetched = (new RepositoryEntity)->populate(['ID' => 1, 'db_value' => 'value']);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('t.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('t.column', '=', 'value')->andReturnSelf();
        $this->mockSelect->shouldNotReceive('orderBy');
        $this->mockSelect->shouldReceive('setFetchMode')->once()
            ->with(\PDO::FETCH_CLASS, RepositoryEntity::class, [$this->mockPool, true])->andReturnSelf();
        $this->mockSelect->shouldReceive('fetch')->once()->withNoArgs()->andReturn($fetched);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $item       = $repository->findOneBy(['column' => 'value']);

        $this->assertInstanceOf(RepositoryEntity::class, $item);
    }

    /**
     * Test find by with columns.
     */
    public function testFindOneByWithColumns()
    {
        $fetched = (new RepositoryEntity)->populate(['ID' => 1, 'db_value' => 'value']);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('t.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('t.c1', '=', 'v1')->andReturnSelf();
        $this->mockSelect->shouldReceive('where')->once()->with('t.c2', '=', 'v2')->andReturnSelf();
        $this->mockSelect->shouldNotReceive('orderBy');
        $this->mockSelect->shouldReceive('setFetchMode')->once()
            ->with(\PDO::FETCH_CLASS, RepositoryEntity::class, [$this->mockPool, true])->andReturnSelf();
        $this->mockSelect->shouldReceive('fetch')->once()->withNoArgs()->andReturn($fetched);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $item       = $repository->findOneBy(['c1' => 'v1', 'c2' => 'v2']);

        $this->assertInstanceOf(RepositoryEntity::class, $item);
    }

    /**
     * Test find by with order.
     */
    public function testFindOneByWithOrder()
    {
        $fetched = (new RepositoryEntity)->populate(['ID' => 1, 'db_value' => 'value']);

        $this->mockPool->shouldReceive('get')->once()->with(null)->andReturn($this->mockConnection);
        $this->mockConnection->shouldReceive('select')->once()->withNoArgs()->andReturn($this->mockSelect);
        $this->mockSelect->shouldReceive('columns')->once()->with('t.*')->andReturnSelf();
        $this->mockSelect->shouldReceive('from')->once()->with('repo', 't')->andReturnSelf();
        $this->mockSelect->shouldReceive('limit')->once()->with(1)->andReturnSelf();
        $this->mockSelect->shouldNotReceive('where');
        $this->mockSelect->shouldReceive('orderBy')->once()->with('o1', 'asc')->andReturnSelf();
        $this->mockSelect->shouldReceive('setFetchMode')->once()
            ->with(\PDO::FETCH_CLASS, RepositoryEntity::class, [$this->mockPool, true])->andReturnSelf();
        $this->mockSelect->shouldReceive('fetch')->once()->withNoArgs()->andReturn($fetched);

        $repository = new Repository($this->mockPool, RepositoryEntity::class);
        $item       = $repository->findOneBy([], ['o1']);

        $this->assertInstanceOf(RepositoryEntity::class, $item);
    }
}
class RepositoryEntity extends Entity
{
    public $id;
    public $value;
    protected static function getMap()
    {
        return new EntityMap('repo', ['ID' => 'id'], ['db_value' => 'value']);
    }
}