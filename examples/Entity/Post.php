<?php

namespace MadeSimple\Database\Example\Entity;

use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Relationship;

class Post extends Entity
{
    use Relationship\Relational;

    public $id;
    public $uuid;
    public $userId;
    public $title;
    public $content;
    public $createdAt;
    public $updatedAt;

    /**
     * @return EntityMap
     */
    public function getMap()
    {
        return new EntityMap(
            'post',
            ['id'],
            [
                'uuid',
                'user_id' => 'userId',
                'title',
                'content',
                'created_at' => 'createdAt',
                'updated_at' => 'updatedAt',
            ]
        );
    }

    /**
     * @return Relationship\ToOne
     */
    public function user()
    {
        return $this->toOne()->belongsTo(User::class, 'u', 'user_id');
    }

    /**
     * @return Relationship\ToMany
     */
    public function comments()
    {
        return $this->toMany()->has(Comment::class, 'c', 'post_id');
    }
}