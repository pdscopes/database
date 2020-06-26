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

class Seed extends Command
{
    use DatabaseConfigurationTrait, DatabaseSeedTrait, LockableTrait {
        DatabaseConfigurationTrait::initialize as databaseInitialize;
    }

    protected function configure()
    {
        $this->addDatabaseConfigure();
        $this
            ->setName('database:seed')
            ->setDescription('Seed your database with dummy data')
            ->setHelp('This command allows you to populate your database with dummy data')
            ->addOption('seed', 's', InputOption::VALUE_REQUIRED, 'Path to your database seed files', 'database/seeds')
            ->addUsage('--db-driver sqlite')
            ->addUsage('-s path/to/seed/files --db-driver sqlite')
            ->addUsage('-s path/to/seed/files')
            ->addUsage('--no-env');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->databaseInitialize($input, $output);
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

        $this->executeSeed($migrator, $input);

        $this->release();
        return 0;
    }
}