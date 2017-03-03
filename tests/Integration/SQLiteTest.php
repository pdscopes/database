<?php

namespace Tests\Integration;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Migration\Migration;
use MadeSimple\Database\Repository;
use Tests\TestCase;

class SQLiteTest extends TestCase
{
    public function test()
    {
        $pdo        = new \PDO('sqlite::memory:');
        $connection = new Connection($pdo);
        $migration  = new SQLiteTestMigration();

        // Migrate up
        $migration->up($connection);
        $statement = $pdo->query('SELECT * FROM "sqlite_master" WHERE "type"=\'table\'');
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(1, $rows);
        $this->assertEquals('table1', $rows[0]['name']);

        // Test inserts
        $connection->insert()->into('table1')->columns('ID', 'db_value')->values(['1', 'a'], ['2', 'b'])->execute();

        // Read entity
        $entity1 = new SQLiteTestEntity($connection);
        $this->assertTrue($entity1->read(null, '1'));
        $this->assertEquals('a', $entity1->value);

        // Repository
        $repository = new Repository($connection, SQLiteTestEntity::class);
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
        $statement = $pdo->query('SELECT * FROM "sqlite_master" WHERE "type"=\'table\'');
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(0, $rows);
    }
}
class SQLiteTestMigration implements Migration
{
    function up(Connection $connection)
    {
        $connection->query('CREATE TABLE "table1" ("ID" PRIMARY KEY, "db_value")');
    }

    function dn(Connection $connection)
    {
        $connection->query('DROP TABLE "table1"');
    }
}
class SQLiteTestEntity extends Entity
{
    public $id;
    public $value;

    public  function getMap()
    {
        return new EntityMap('table1', ['ID' => 'id'], ['ID' => 'id', 'db_value' => 'value']);
    }
}