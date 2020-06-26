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

class Migrate extends Command
{
    use DatabaseConfigurationTrait, DatabaseMigrationTrait, DatabaseSeedTrait, LockableTrait {
        DatabaseConfigurationTrait::initialize as databaseInitialize;
    }

    protected function configure()
    {
        $this->addDatabaseConfigure();
        $this
            ->setName('database:migrate')
            ->setDescription('Install and upgrade your database migrations')
            ->setHelp('This command allows you to install the database migrations table and upgrade your database to the next migration')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path to your database migration files', 'database/migrations')
            ->addOption('seed', 's', InputOption::VALUE_OPTIONAL, 'Path to your database seed files', 'database/seeds')
            ->addOption('step', 't', InputOption::VALUE_OPTIONAL, 'Limit the number of migration files to migrate', false)
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

        // Upgrade
        $this->executeMigrateUpgrade($migrator, $input);

        // Optionally seed the database
        $this->executeSeed($migrator, $input);

        $this->release();
        return 0;
    }
}