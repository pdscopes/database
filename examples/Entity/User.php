<?php

namespace MadeSimple\Database\Example\Entity;

use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Relationship;

class User extends Entity
{
    use Relationship\Relational;

    public $id;
    public $uuid;
    public $email;
    public $password;
    public $createdAt;
    public $updatedAt;

    /**
     * @return EntityMap
     */
    protected static function getMap()
    {
        return new EntityMap(
            'user',
            ['id'],
            [
                'uuid',
                'email',
                'password',
                'created_at' => 'createdAt',
                'updated_at' => 'updatedAt',
            ]
        );
    }

    /**
     * @return Relationship\ToMany
     */
    public function posts()
    {
        return $this->toMany()->has(Post::class, 'p', 'user_id');
    }

    public function comments()
    {
        return $this->toMany()->has(Comment::class, 'c', 'user_id');
    }

    /**
     * @param null|int $postId
     *
     * @return Relationship\ToMany
     */
    public function postComments($postId = null)
    {
        $relationship = $this->posts()->has(Comment::class, 'c', 'post_id');
        if (null !== $postId) {
            $relationship->where('p.id', '=', $postId);
        }

        return $relationship;
    }
}