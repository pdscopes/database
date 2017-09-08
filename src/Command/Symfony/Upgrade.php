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

/**
 * Class Upgrade
 *
 * @package MadeSimple\Database\Command\Symfony
 * @author  Peter Scopes
 */
class Upgrade extends Command
{
    use DatabaseConfigurationTrait, LockableTrait;

    protected function configure()
    {
        $this->addDatabaseConfigure();
        $this
            ->setName('migrate')
            ->setAliases(['migrate:upgrade'])
            ->setDescription('Upgrade to your next database migration')
            ->setHelp('This command allows you to upgrade your database to the next migration')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Path to your database migration files', 'database/migrations')
            ->addOption('seed', 's', InputOption::VALUE_OPTIONAL, 'Path to your database seed files', 'database/seeds')
            ->addUsage('sqlite')
            ->addUsage('-p path/to/migration/files sqlite')
            ->addUsage('-e')
            ->addUsage('-e -p path/to/migration/files');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        if (!$fs->exists($input->getOption('path'))) {
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


        // Find the necessary files and upgrade
        $finder = new Finder();
        $finder->files()->sortByName()->in($input->getOption('path'))->name('*.php');
        $migrator->upgrade(iterator_to_array($finder->getIterator()));

        // Optionally seed the database
        if ($input->getOption('seed')) {
            $finder = new Finder();
            $finder->files()->sortByName()->in($input->getOption('seed'))->name('*.php');
            $migrator->seed(iterator_to_array($finder->getIterator()));
        }

        $this->release();
        return 0;
    }
}