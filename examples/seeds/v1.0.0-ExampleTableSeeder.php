<?php

use MadeSimple\Database\Connection;
use \MadeSimple\Database\Migration\SeedInterface;

class ExampleTableSeeder implements SeedInterface
{
    function sow(Connection $connection)
    {
        $faker     = Faker\Factory::create();
        $userNames = [];

        $username = $faker->userName;
        $insert   = $connection->insert()
            ->into('user')
            ->columns('uuid', 'email', 'password')
            ->values($faker->uuid, $username . '@example.com', password_hash('password', PASSWORD_DEFAULT))
            ->query();
        $userOneId = $insert->lastInsertId();
        $userNames[$userOneId] = $username;
        $username = $faker->userName;
        $insert
            ->values($faker->uuid, $username . '@example.com', password_hash('password', PASSWORD_DEFAULT))
            ->query();
        $userTwoId = $insert->lastInsertId();
        $userNames[$userTwoId] = $username;


        $counter  = array_fill_keys(array_keys($userNames), 0);
        $numPosts = rand(3, 11);
        for ($i=0; $i<$numPosts; $i++) {
            $posterId = $faker->randomElement([$userOneId, $userTwoId]);
            $insert = $connection->insert()
                ->into('post')
                ->columns('uuid', 'user_id', 'title', 'content')
                ->values($faker->uuid, $posterId, 'Post ' . (++$counter[$posterId]) . ' by User ' . $userNames[$posterId], $faker->paragraph(2))
                ->query();
            $postId = $insert->lastInsertId();

            $commenterId = $faker->randomElement([$userOneId, $userTwoId]);
            $numComments = rand(0, 5);
            for ($j=0; $j<$numComments; $j++) {
                $commenterId = $commenterId !== $userOneId ? $userOneId : $userTwoId;
                $connection->insert()
                    ->into('comment')
                    ->columns('uuid', 'user_id', 'post_id', 'content')
                    ->values($faker->uuid, $commenterId, $postId, $faker->paragraph(2))
                    ->query();
            }
        }
    }
}