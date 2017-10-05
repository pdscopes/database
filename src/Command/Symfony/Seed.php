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
    use DatabaseConfigurationTrait, LockableTrait {
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
            ->addUsage('sqlite')
            ->addUsage('-s path/to/seed/files sqlite')
            ->addUsage('-e')
            ->addUsage('-s path/to/seed/files -e');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->databaseInitialize($input, $output);

        // Ensure default value for options with optional value
        $input->setOption('seed', $input->getOption('seed') ?? $this->getDefinition()->getOption('seed')->getDefault());

        // Ensure locations exist
        $fs = new Filesystem();
        if ($input->getParameterOption(['--seed', '-s'], false, true) !== false && !$fs->exists($input->getOption('seed'))) {
            $output->writeln('<error>Seed path must be a directory that exists</error>');
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


        // Find the necessary files and seed
        $finder = new Finder();
        $finder->files()->sortByName()->in($input->getOption('seed'))->name('*.php');
        $migrator->seed(iterator_to_array($finder->getIterator()));

        $this->release();
        return 0;
    }
}