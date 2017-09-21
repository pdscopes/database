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

class RollBack extends Command
{
    use DatabaseConfigurationTrait, LockableTrait {
        DatabaseConfigurationTrait::initialize as databaseInitialize;
    }

    protected function configure()
    {
        $this->addDatabaseConfigure();
        $this
            ->setName('migrate:rollback')
            ->setDescription('Rollback to your previous database migration')
            ->setHelp('This command allows you to rollback your database to the previous migration')
            ->addOption('batches', 'b', InputOption::VALUE_REQUIRED, 'Number of batches to rollback', 1)
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Rollback to before your first migration')
            ->addUsage('sqlite')
            ->addUsage('--all sqlite')
            ->addUsage('-e')
            ->addUsage('-e -b 1');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->databaseInitialize($input, $output);

        // Validate input
        if ($input->getOption('batches') !== null && !is_numeric($input->getOption('batches'))) {
            $output->writeln('<error>Batches must be an number</error>');
            exit(1);
        }
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

        // Rollback
        $migrator->rollback($input->getOption('all') ? null : $input->getOption('batches'));


        $this->release();
        return 0;
    }
}