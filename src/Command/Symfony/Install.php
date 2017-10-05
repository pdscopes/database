<?php

namespace MadeSimple\Database\Command\Symfony;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Migration\Migrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class Install extends Command
{
    use DatabaseConfigurationTrait, LockableTrait;

    protected function configure()
    {
        $this->addDatabaseConfigure();
        $this
            ->setName('database:install')
            ->setDescription('Install the database migrations table')
            ->setHelp('This command allows you to install the database migrations table')
            ->addUsage('sqlite')
            ->addUsage('-e');
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

        $this->release();
        return 0;
    }
}