<?php

require __DIR__ . '/../vendor/autoload.php';

use MadeSimple\Database\Connection;
use MadeSimple\Database\Example\Entity;

$mysqlConfig  = [
    'driver'   => 'mysql',
    'host'     => 'localhost',
    'database' => 'test',
    'username' => '',
    'password' => '',
];
$sqliteConfig = [
    'driver'   => 'sqlite',
    'database' => realpath(__DIR__ . '/database.sqlite'),
];

$logger     = new Monolog\Logger('scratch', [new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::NOTICE)]);
$connection = Connection::factory($sqliteConfig, $logger);
$pool       = new \MadeSimple\Database\Pool($connection);


$users    = new \MadeSimple\Database\EntityCollection(Entity\User::qSelect($pool)->fetchAll());
echo 'Users: ', $users->pluck('email')->toJson(JSON_PRETTY_PRINT), PHP_EOL;


$user = Entity\User::find($pool, ['id' => 1]);
echo <<<EOT
User:         {$user->email}
All Posts:    {$user->relation('posts')->pluck('title')->toJson(JSON_PRETTY_PRINT)}
All Comments: {$user->relation('comments')->pluck('post', 'content')->toJson(JSON_PRETTY_PRINT)}

EOT;
