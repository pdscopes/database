<?php

namespace MadeSimple\Database\Tests\Integration;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Migration;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Relationship;
use MadeSimple\Database\Repository;
use MadeSimple\Database\Statement\CreateTable;
use MadeSimple\Database\Statement\DropTable;
use MadeSimple\Database\Tests\TestCase;

class MySQLTest extends TestCase
{
    public function test()
    {
        $config = [
            'driver'   => 'mysql',
            'host'     => 'localhost',
            'database' => 'test',
            'username' => 'root',
            'password' => '',
        ];
        $connection = Connection::factory($config);
        $pool       = new Pool($connection);

        // Confirm database is empty
        $rows = $connection->rawQuery('SHOW TABLES;')->fetchAll(\PDO::FETCH_NUM);
        $this->assertCount(0, $rows, 'Database must be empty before MySQL integration test');

        $migration  = new MySQLTestMigration();

        // Migrate up
        $migration->up($connection);
        $rows = $connection->rawQuery('SHOW TABLES;')->fetchAll(\PDO::FETCH_NUM);
        $this->assertCount(3, $rows);
        $this->assertEquals('comment', $rows[0][0]);
        $this->assertEquals('post', $rows[1][0]);
        $this->assertEquals('user', $rows[2][0]);

        // Test inserts
        $connection->insert()->into('user')
            ->columns('UUID', 'user_name')
            ->values(['123', 'user1'])
            ->values(['456', 'user2'])
            ->query();
        $connection->insert()->into('post')
            ->columns('uuid', 'submitter_id', 'title')
            ->values(['abc', '123', 'User1 Post1'])
            ->values(['def', '456', 'User2 Post1'])
            ->values(['ghi', '123', 'User1 Post2'])
            ->query();
        $connection->insert()->into('comment')
            ->columns('id', 'post_uuid', 'data')
            ->chunkedQuery([
                ['zyx', 'abc', 'Comment 1 on Post 1'],
                ['wvu', 'abc', 'Comment 2 on Post 1'],
                ['rst', 'ghi', 'Comment 1 on Post 2']
            ]);


        // Read users
        $user = new MySQLTestUserEntity($pool);
        $this->assertInstanceOf(MySQLTestUserEntity::class, $user->read('456'));
        $this->assertEquals('user2', $user->username);
        $this->assertInstanceOf(MySQLTestUserEntity::class, $user->read('123'));
        $this->assertEquals('user1', $user->username);

        // Find users
        $user = MySQLTestUserEntity::find($pool, ['UUID' => '456']);
        $this->assertInstanceOf(MySQLTestUserEntity::class, $user);
        $this->assertEquals('user2', $user->username);
        $user = MySQLTestUserEntity::find($pool, ['UUID' => '123']);
        $this->assertInstanceOf(MySQLTestUserEntity::class, $user);
        $this->assertEquals('user1', $user->username);


        // Has relationship
        /** @var MySQLTestPostEntity $post */
        $post = $user->posts()->fetch()[0];
        $this->assertEquals($user->id, $post->submitterId);

        // Has through relationship
        $comments = $user->comments($post->id)->fetch();
        /** @var MySQLTestCommentEntity $comment */
        $comment  = $comments[0];
        $this->assertCount(2, $comments);
        $this->assertEquals($post->id, $comment->postId);
        $this->assertCount(3, $user->comments()->fetch());


        // Belongs to and belongs to through relationships
        $this->assertEquals($post->id, $comment->post()->fetch()->id);
        $this->assertEquals($user->id, $comment->postUser()->fetch()->id);

        // Repository
        $repository = new Repository($pool, MySQLTestUserEntity::class);
        $items = $repository->findBy();
        $this->assertCount(2, $items);
        $this->assertEquals('123', $items[0]->id);
        $this->assertEquals('456', $items[1]->id);

        $items = $repository->findBy([], ['UUID' => 'desc']);
        $this->assertCount(2, $items);
        $this->assertEquals('456', $items[0]->id);
        $this->assertEquals('123', $items[1]->id);

        /** @var MySQLTestUserEntity $entity2 */
        $entity2 = $repository->findOneBy(['user_name' => 'user2']);
        $this->assertInstanceOf(MySQLTestUserEntity::class, $entity2);
        $this->assertEquals('456', $entity2->id);


        // Update entity
        $entity2->username = 'updated';
        $entity2->update();
        $this->assertEquals($entity2->username, $repository->findOneBy(['user_name' => 'updated'])->username);

        // Delete Entity
        $entity2->delete();
        $items = $repository->findBy();
        $this->assertCount(1, $items);


        // Migrate down
        $migration->dn($connection);
        $rows = $connection->rawQuery('SHOW TABLES;')->rowCount();
        $this->assertEquals(0, $rows);
    }
}
class MySQLTestMigration implements Migration\MigrationInterface
{
    function up(Connection $connection)
    {
        $connection->statement(function (CreateTable $table) {
            $table->table('user')->ifNotExists();
            $table->column('UUID')->varchar(36)->primaryKey();
            $table->column('user_name')->text();
        });

        $connection->statement(function (CreateTable $table) {
            $table->table('post')->ifNotExists();
            $table->column('uuid')->varchar(36)->primaryKey();
            $table->column('submitter_id')->integer(10);
            $table->column('title')->text();
        });

        $connection->statement(function (CreateTable $table) {
            $table->table('comment')->ifNotExists();
            $table->column('id')->varchar(36)->primaryKey();
            $table->column('post_uuid')->text();
            $table->column('data')->text();
        });
    }

    function dn(Connection $connection)
    {
        $connection->statement(function (DropTable $drop) { $drop->table('comment'); });
        $connection->statement(function (DropTable $drop) { $drop->table('post'); });
        $connection->statement(function (DropTable $drop) { $drop->table('user'); });
    }
}

class MySQLTestUserEntity extends Entity
{
    public $id;
    public $username;

    protected static function getMap()
    {
        return new EntityMap('user', ['UUID' => 'id'], ['user_name' => 'username']);
    }

    public function posts()
    {
        return (new Relationship\ToMany($this))->has(MySQLTestPostEntity::class, 'p', 'submitter_id');
    }

    public function comments($postUuid = null)
    {
        $relationship = $this->posts()->has(MySQLTestCommentEntity::class, 'c', 'post_uuid');
        if (null !== $postUuid) {
            $relationship->where('p.uuid', '=', $postUuid);
        }

        return $relationship;
    }
}
class MySQLTestPostEntity extends Entity
{
    public $id;
    public $submitterId;
    public $title;

    protected static function getMap()
    {
        return new EntityMap('post', ['uuid' => 'id'], ['submitter_id' => 'submitterId', 'title' => 'title']);
    }

    public function user()
    {
        return (new Relationship\ToOne($this))->belongsTo(MySQLTestUserEntity::class, 'u', 'submitter_id');
    }

    public function comments()
    {
        return (new Relationship\ToMany($this))->has(MySQLTestCommentEntity::class, 'c', 'post_uuid');
    }
}
class MySQLTestCommentEntity extends Entity
{
    public $uuid;
    public $postId;
    public $text;

    protected static function getMap()
    {
        return new EntityMap('comment', ['id' => 'uuid'], ['post_uuid' => 'postId', 'data' => 'text']);
    }

    public function post()
    {
        return (new Relationship\ToOne($this))->belongsTo(MySQLTestPostEntity::class, 'p', 'post_uuid');
    }

    public function postUser()
    {
        return $this->post()->belongsTo(MySQLTestUserEntity::class, 'u', 'submitter_id');
    }
}