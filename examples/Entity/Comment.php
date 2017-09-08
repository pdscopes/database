<?php

namespace MadeSimple\Database\Example\Entity;

use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Relationship;

class Comment extends Entity
{
    use Relationship\Relational;

    public $id;
    public $uuid;
    public $userId;
    public $postId;
    public $content;
    public $createdAt;
    public $updatedAt;

    /**
     * @return EntityMap
     */
    public function getMap()
    {
        return new EntityMap(
            'comment',
            ['id'],
            [
                'uuid',
                'user_id' => 'userId',
                'post_id' => 'postId',
                'content',
                'created_at' => 'createdAt',
                'updated_at' => 'updatedAt',
            ]
        );
    }

    /**
     * @return Relationship\ToOne
     */
    public function post()
    {
        return $this->toOne()->belongsTo(Post::class, 'p', 'post_id');
    }

    /**
     * @return Relationship\ToOne
     */
    public function user()
    {
        return $this->toOne()->belongsTo(User::class, 'u', 'user_id');
    }

    /**
     * @return Relationship\ToOne
     */
    public function postUser()
    {
        return $this->post()->belongsTo(User::class, 'u', 'user_id');
    }
}