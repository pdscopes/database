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
    use DatabaseConfigurationTrait, LockableTrait {
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
            ->addUsage('sqlite')
            ->addUsage('-e');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->databaseInitialize($input, $output);

        // Ensure default value for options with optional value
        $input->setOption('path', $input->getOption('path') ?? $this->getDefinition()->getOption('path')->getDefault());
        $input->setOption('seed', $input->getOption('seed') ?? $this->getDefinition()->getOption('seed')->getDefault());

        // Ensure locations exist
        $fs = new Filesystem();
        if (!$fs->exists($input->getOption('path'))) {
            $output->writeln('<error>Migrations path must be a directory that exists</error>');
            exit(1);
        }
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

        // Install
        $migrator->install();

        // Rollback
        $migrator->rollback();

        // Find the necessary files
        $finder = Finder::create()->files()->sortByName()->name('*.php');
        $files  = array_map('realpath', iterator_to_array($finder->in($input->getOption('path'))));

        // Upgrade
        $migrator->upgrade($files);

        // Optionally seed the database
        if ($input->getParameterOption(['--seed', '-s'], false, true) !== false) {
            $finder = Finder::create()->files()->sortByName()->name('*.php');
            $migrator->seed(iterator_to_array($finder->in($input->getOption('seed'))));
        }

        $this->release();

        return 0;
    }
}