<?php

namespace Tests\Integration;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Migration;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Relationship;
use MadeSimple\Database\Repository;
use MadeSimple\Database\SQLite\Statement\Table\Create;
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
        $this->assertCount(3, $rows);
        $this->assertEquals('user', $rows[0]['name']);
        $this->assertEquals('post', $rows[1]['name']);
        $this->assertEquals('comment', $rows[2]['name']);

        // Test inserts
        $connection->insert()->into('user')
            ->columns('UUID', 'user_name')->values(['123', 'user1'], ['456', 'user2'])
            ->execute();
        $connection->insert()->into('post')
            ->columns('uuid', 'submitter_id', 'title')->values(['abc', '123', 'User1 Post1'], ['def', '456', 'User2 Post1'], ['ghi', '123', 'User1 Post2'])
            ->execute();
        $connection->insert()->into('comment')
            ->columns('id', 'post_uuid', 'data')->values(['zyx', 'abc', 'Comment 1 on Post 1'], ['wvu', 'abc', 'Comment 2 on Post 1'], ['rst', 'ghi', 'Comment 1 on Post 2'])
            ->execute();


        // Read users
        $user = new SQLiteTestUserEntity($pool);
        $this->assertTrue($user->read('456'));
        $this->assertEquals('user2', $user->username);
        $this->assertTrue($user->read('123'));
        $this->assertEquals('user1', $user->username);

        // Find users
        $this->assertTrue($user->find(['UUID' => '456']));
        $this->assertEquals('user2', $user->username);
        $this->assertTrue($user->find(['UUID' => '123']));
        $this->assertEquals('user1', $user->username);


        // Has relationship
        /** @var SQLiteTestPostEntity $post */
        $post = $user->posts()->fetch()[0];
        $this->assertEquals($user->id, $post->submitterId);

        // Has through relationship
        $comments = $user->comments($post->id)->fetch();
        /** @var SQLiteTestCommentEntity $comment */
        $comment  = $comments[0];
        $this->assertCount(2, $comments);
        $this->assertEquals($post->id, $comment->postId);
        $this->assertCount(3, $user->comments()->fetch());


        // Belongs to and belongs to through relationships
        $this->assertEquals($post->id, $comment->post()->fetch()->id);
        $this->assertEquals($user->id, $comment->postUser()->fetch()->id);

        // Repository
        $repository = new Repository($pool, SQLiteTestUserEntity::class);
        $items = $repository->findBy();
        $this->assertCount(2, $items);
        $this->assertEquals('123', $items[0]->id);
        $this->assertEquals('456', $items[1]->id);

        $items = $repository->findBy([], ['UUID desc']);
        $this->assertCount(2, $items);
        $this->assertEquals('456', $items[0]->id);
        $this->assertEquals('123', $items[1]->id);

        /** @var SQLiteTestUserEntity $entity2 */
        $entity2 = $repository->findOneBy(['user_name' => 'user2']);
        $this->assertInstanceOf(SQLiteTestUserEntity::class, $entity2);
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
        $connection->create('user', function (Create $table) {
            $table->column('UUID')->text()->primaryKey();
            $table->column('user_name')->text();
        })->execute();

        $connection->create('post', function (Create $table) {
            $table->column('uuid')->text()->primaryKey();
            $table->column('submitter_id')->integer();
            $table->column('title')->text();
        })->execute();
        $connection->create('comment', function (Create $table) {
            $table->column('id')->integer()->primaryKey();
            $table->column('post_uuid')->text();
            $table->column('data')->none();
        })->execute();
    }

    function dn(Connection $connection)
    {
        $connection->drop()->table('comment')->execute();
        $connection->drop()->table('post')->execute();
        $connection->drop()->table('user')->execute();
    }
}

class SQLiteTestUserEntity extends Entity
{
    public $id;
    public $username;

    public  function getMap()
    {
        return new EntityMap('user', ['UUID' => 'id'], ['user_name' => 'username']);
    }

    public function posts()
    {
        return (new Relationship\ToMany($this))->has(SQLiteTestPostEntity::class, 'p', 'submitter_id');
    }

    public function comments($postUuid = null)
    {
        $relationship = $this->posts()->has(SQLiteTestCommentEntity::class, 'c', 'post_uuid');
        if (null !== $postUuid) {
            $relationship->andWhere('p.uuid = :postUuid', ['postUuid' => $postUuid]);
        }

        return $relationship;
    }
}
class SQLiteTestPostEntity extends Entity
{
    public $id;
    public $submitterId;
    public $title;

    public  function getMap()
    {
        return new EntityMap('post', ['uuid' => 'id'], ['submitter_id' => 'submitterId', 'title' => 'title']);
    }

    public function user()
    {
        return (new Relationship\ToOne($this))->belongsTo(SQLiteTestUserEntity::class, 'u', 'submitter_id');
    }

    public function comments()
    {
        return (new Relationship\ToMany($this))->has(SQLiteTestCommentEntity::class, 'c', 'post_uuid');
    }
}
class SQLiteTestCommentEntity extends Entity
{
    public $uuid;
    public $postId;
    public $text;

    public  function getMap()
    {
        return new EntityMap('comment', ['id' => 'uuid'], ['post_uuid' => 'postId', 'data' => 'text']);
    }

    public function post()
    {
        return (new Relationship\ToOne($this))->belongsTo(SQLiteTestPostEntity::class, 'p', 'post_uuid');
    }

    public function postUser()
    {
        return $this->post()->belongsTo(SQLiteTestUserEntity::class, 'u', 'submitter_id');
    }
}