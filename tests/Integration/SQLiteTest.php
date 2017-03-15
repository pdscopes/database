<?php

namespace Tests\Integration;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Migration;
use MadeSimple\Database\Pool;
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
            ->from('sqlite_master')->where('type = ?', 'table')->andWhere('name = ?', 'table1')
            ->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(1, $rows);
        $this->assertEquals('table1', $rows[0]['name']);

        // Test inserts
        $connection->insert()->into('table1')->columns('ID', 'db_value')->values(['1', 'a'], ['2', 'b'])->execute();

        // Read entity
        $entity1 = new SQLiteTestEntity($pool);
        $this->assertTrue($entity1->read(null, '1'));
        $this->assertEquals('a', $entity1->value);

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

        $entity2 = $repository->findOneBy(['db_value' => 'b']);
        $this->assertInstanceOf(SQLiteTestEntity::class, $entity2);
        $this->assertEquals('2', $entity2->id);


        // Update entity
        $entity1->value = 'A';
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
            ->from('sqlite_master')->where('type = ?', 'table')->andWhere('name = \'table1\'')
            ->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(0, $rows);
    }
}
class SQLiteTestMigration implements Migration
{
    function up(Connection $connection)
    {
        $table = $connection->create(function (Create $table) {
            $table->name('table1');
            $table->column('ID')->extras('PRIMARY KEY');
            $table->column('db_value');
        });
        $connection->query($table->toSql());
    }

    function dn(Connection $connection)
    {
        $connection->drop()->table('table1')->execute();
    }
}
class SQLiteTestEntity extends Entity
{
    public $id;
    public $value;

    public  function getMap()
    {
        return new EntityMap('table1', ['ID' => 'id'], ['db_value' => 'value']);
    }
}