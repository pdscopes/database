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
    use DatabaseConfigurationTrait, LockableTrait;

    protected function configure()
    {
        $this->addDatabaseConfigure();
        $this
            ->setName('migrate:seed')
            ->setDescription('Seed your database with dummy data')
            ->setHelp('This command allows you to populate your database with dummy data')
            ->addOption('seed', 's', InputOption::VALUE_OPTIONAL, 'Path to your database seed files', 'database/seeds')
            ->addUsage('sqlite')
            ->addUsage('-s path/to/seed/files sqlite')
            ->addUsage('-e')
            ->addUsage('-s path/to/seed/files -e');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Validate input
        $fs = new Filesystem();
        if (!$fs->exists($input->getOption('seed'))) {
            throw new \InvalidArgumentException('Path must be a directory that exists');
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