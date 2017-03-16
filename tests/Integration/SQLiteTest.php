<?php

namespace Tests\Integration;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Migration;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Relation\BelongsToOne;
use MadeSimple\Database\Relation\HasMany;
use MadeSimple\Database\Repository;
use MadeSimple\Database\Statement\Table\Create;
use Tests\TestCase;

class SQLiteTest extends TestCase
{
    public function test()
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $connection = Connection::factory($pdo);
        $pool       = new Pool($connection);
        $migration  = new SQLiteTestMigration();

        // Migrate up
        $migration->up($connection);
        $rows = $connection->select()
            ->from('sqlite_master')->where('type = ?', 'table')
            ->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(2, $rows);
        $this->assertEquals('relatedTable', $rows[0]['name']);
        $this->assertEquals('entityTable', $rows[1]['name']);

        // Test inserts
        $connection->insert()->into('relatedTable')
            ->columns('KEY', 'db_value')->values(['a', 'relative'])->execute();
        $connection->insert()->into('entityTable')
            ->columns('ID', 'foreign_key', 'db_value')->values(['1', 'a', 'value1'], ['2', 'a', 'value2'])->execute();

        // Read entity
        $entity1 = new SQLiteTestEntity($pool);
        $this->assertTrue($entity1->read(null, '1'));
        $this->assertEquals('value1', $entity1->value);

        // Belongs to one relation
        /** @var SQLiteTestRelated $relative */
        $relative = $entity1->related()->fetch();
        $this->assertInstanceOf(SQLiteTestRelated::class, $relative);
        $this->assertEquals('a', $relative->key);
        $this->assertEquals('relative', $relative->value);

        // Has many relation
        $entities = $relative->entities()->fetch();
        $this->assertCount(2, $entities);
        $this->assertEquals('1', $entities[0]->id);
        $this->assertEquals('2', $entities[1]->id);

        // Repository
        $repository = new Repository($pool, SQLiteTestEntity::class);
        $items = $repository->findBy();
        $this->assertCount(2, $items);
        $this->assertEquals('1', $items[0]->id);
        $this->assertEquals('2', $items[1]->id);

        $items = $repository->findBy([], ['ID desc']);
        $this->assertCount(2, $items);
        $this->assertEquals('2', $items[0]->id);
        $this->assertEquals('1', $items[1]->id);

        $entity2 = $repository->findOneBy(['db_value' => 'value2']);
        $this->assertInstanceOf(SQLiteTestEntity::class, $entity2);
        $this->assertEquals('2', $entity2->id);


        // Update entity
        $entity1->value = 'VALUE1';
        $entity1->update();
        $entity2->read(null, '1');
        $this->assertEquals($entity1->value, $entity2->value);

        // Delete Entity
        $entity1->delete();
        $items = $repository->findBy();
        $this->assertCount(1, $items);


        // Migrate down
        $migration->dn($connection);
        $rows = $connection->select()
            ->from('sqlite_master')->where('type = ?', 'table')
            ->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(0, $rows);
    }
}
class SQLiteTestMigration implements Migration
{
    function up(Connection $connection)
    {
        $table = $connection->create(function (Create $table) {
            $table->name('relatedTable');
            $table->column('KEY')->extras('PRIMARY KEY');
            $table->column('db_value');
        });
        $connection->query($table->toSql());

        $table = $connection->create(function (Create $table) {
            $table->name('entityTable');
            $table->column('ID')->extras('PRIMARY KEY');
            $table->column('foreign_key');
            $table->column('db_value');
        });
        $connection->query($table->toSql());
    }

    function dn(Connection $connection)
    {
        $connection->drop()->table('entityTable')->execute();
        $connection->drop()->table('relatedTable')->execute();
    }
}
class SQLiteTestEntity extends Entity
{
    public $id;
    public $foreignKey;
    public $value;

    public  function getMap()
    {
        return new EntityMap('entityTable', ['ID' => 'id'], ['foreign_key' => 'foreignKey', 'db_value' => 'value']);
    }

    public function related()
    {
        return new BelongsToOne($this, SQLiteTestRelated::class, 'foreign_key', 'e', 'r');
    }
}
class SQLiteTestRelated extends Entity
{
    public $key;
    public $value;

    public  function getMap()
    {
        return new EntityMap('relatedTable', ['KEY' => 'key'], ['db_value' => 'value']);
    }

    public function entities()
    {
        return new HasMany($this, SQLiteTestEntity::class, 'foreign_key', 'e', 'r');
    }
}