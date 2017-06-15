<?php

namespace MadeSimple\Database\Command;

use MadeSimple\Database\MySQL;
use MadeSimple\Database\SQLite;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Migrate
 *
 * @package MadeSimple\Database\Command
 * @author  Peter Scopes
 */
class Install extends Command
{
    use InteractsWithDatabaseMigrations, LockableTrait;

    protected function configure()
    {
        $this->configureDatabase();
        $this
            ->setName('migrate:install')
            ->setDescription('Install the database migrations table')
            ->setHelp('This command allows you to install the database migrations table')
            ->addUsage('sqlite:database.sqlite')
            ->addUsage('DB_DSN DB_USER DB_PASS -e');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock('migration')) {
            $output->writeln('Migration already in progress.');
            return 0;
        }

        // Connect to the database
        $connection = $this->connect($input);

        // Check if the migrations table exists
        $batch = $this->batch($connection);

        if ($batch !== false) {
            $output->writeln('Already installed');
            $this->release();
            return 0;
        }

        // Create the migrations table
        switch ($connection->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            case 'mysql':
                $connection->create('migrations', function (MySQL\Statement\Table\Create $table) {
                    $table->ifNotExists(true);
                    $table->column('id')->int(11, true)->null(false)->autoIncrement(true);
                    $table->column('fileName')->char(255)->null(false);
                    $table->column('batch')->int(11, true)->null(false);
                    $table->column('migratedAt')->datetime()->null(false);

                    $table->primaryKey('id');
                    $table->engine('InnoDB');
                })->execute();
                break;
            case 'sqlite':
                $connection->create('migrations', function(SQLite\Statement\Table\Create $table) {
                    $table->column('id')->integer()->primaryKey();
                    $table->column('filename')->text();
                    $table->column('batch')->integer();
                    $table->column('migratedAt')->text();
                })->execute();
                break;

            default:
                throw new \RuntimeException('Unsupported PDO Driver');
        }
        $output->writeln('Created migrations table');

        $this->release();
        return 0;
    }
}