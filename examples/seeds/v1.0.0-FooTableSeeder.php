<?php

use MadeSimple\Database\Connection;
use \MadeSimple\Database\Seed;

class FooTableSeeder implements Seed
{
    function sow(Connection $connection)
    {
        $username = 'user' . rand(100, 999);
        $connection->insert()
            ->into('user')
            ->columns('id', 'uuid', 'email', 'password')
            ->values(null, bin2hex(random_bytes(18)), $username . '@example.com', password_hash('password', PASSWORD_DEFAULT))
            ->execute();

        $userId   = $connection->lastInsertId();
        $numPosts = rand(1, 5);
        for ($i=0; $i<$numPosts; $i++) {
            $connection->insert()
                ->into('post')
                ->columns('id', 'uuid', 'userId', 'title', 'content')
                ->values(null, 'abc' . ($i+1), $userId, 'Post by User ' . $username, '')
                ->execute();
        }
    }
}