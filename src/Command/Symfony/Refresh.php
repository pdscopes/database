<?php

namespace MadeSimple\Database\Command\Symfony;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Migration\Migrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Refresh extends Command
{
    use DatabaseConfigurationTrait, DatabaseMigrationTrait, DatabaseSeedTrait, LockableTrait {
        DatabaseConfigurationTrait::initialize as databaseInitialize;
    }

    protected function configure()
    {
        $this->addDatabaseConfigure();
        $this
            ->setName('database:refresh')
            ->setDescription('Refresh the database migrations')
            ->setHelp('This command allows you to refresh your database migrations')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path to your database migration files', 'database/migrations')
            ->addOption('seed', 's', InputOption::VALUE_OPTIONAL, 'Path to your database seed files', 'database/seeds')
            ->addUsage('--db-driver sqlite')
            ->addUsage('--no-env');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->databaseInitialize($input, $output);
        $this->migrationInitialize($input, $output);
        $this->seedInitialize($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Create the logger
        $logger = new ConsoleLogger($output);

        if (!$this->lock('migration')) {
            $logger->warning('Migration already in progress.');

            return 0;
        }


        // Connect and create the migrator
        $connection = Connection::factory($this->config, $logger);
        $migrator   = new Migrator($connection, $logger);

        // Install
        $migrator->install();

        // Rollback
        $migrator->rollback();

        // Upgrade
        $this->executeMigrateUpgrade($migrator, $input);

        // Optionally seed the database
        $this->executeSeed($migrator, $input);

        $this->release();

        return 0;
    }
}