<?php

require __DIR__ . '/../vendor/autoload.php';

use MadeSimple\Database\Connection;
use MadeSimple\Database\Migration\Migrator;

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
$migrator   = new Migrator($connection, $logger);


$migrator->rollback();
$migrator->uninstall();
$migrator->install();
$migrator->upgrade(realpath(__DIR__ . '/migrations/v1.0.0-ExampleInitial.php'));
$migrator->upgrade(realpath(__DIR__ . '/migrations/v1.0.1-ExampleComment.php'));
$migrator->seed(realpath(__DIR__ . '/seeds/v1.0.0-ExampleTableSeeder.php'));