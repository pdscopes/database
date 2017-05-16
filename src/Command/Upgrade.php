<?php

namespace MadeSimple\Database\Command;

use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class Upgrade
 *
 * @package MadeSimple\Database\Command
 * @author  Peter Scopes
 */
class Upgrade extends Migrate
{
    use LockableTrait;

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('migrate:upgrade')
            ->setDescription('Upgrade to your next database migration')
            ->setHelp('This command allows you to upgrade your database to the next migration')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path to your database migration files', 'database/migrations')
            ->addOption('steps', 's', InputOption::VALUE_REQUIRED, 'Number of new migrations to perform', 1)
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Upgrade all of your new migrations')
            ->addUsage('sqlite:database.sqlite')
            ->addUsage('DB_DSN DB_USER DB_PASS -e')
            ->addUsage('-p path/to/migration/files sqlite:database.sqlite')
            ->addUsage('-s 1 sqlite:database.sqlite');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Validate input
        if (!is_numeric($input->getOption('steps'))) {
            throw new \InvalidArgumentException('Steps must be an number');
        }
        $fs = new Filesystem();
        if (!$fs->exists($input->getOption('path'))) {
            throw new \InvalidArgumentException('Path must be a directory that exists');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock('migration')) {
            $output->writeln('Migration already in progress.');
            return 0;
        }

        // Connect to the database
        $connection = $this->connect($input);

        // Create the migration table
        $batch = $this->batch($connection);

        if ($batch === false) {
            $output->writeln('Migrations table not installed');
            $this->release();
            return 1;
        }

        // Perform upgrade
        $steps  = $input->getOption('all') ? false : $input->getOption('steps');
        $finder = new Finder();
        $finder->files()->sortByName()->in($input->getOption('path'))->name('*.php');
        foreach ($finder as $file) {
            // Check the file has not already been migrated
            $migrated = $connection->select()->columns('COUNT(*)')->from('migrations')->where('fileName = ?', $file->getFileName())->execute()->fetchColumn();
            if ($migrated) {
                continue;
            }

            if($this->migrateUp($connection, $batch + 1, $file)) {
                $output->writeln('Migrated UP: ' . $file->getFilename());

                if ($steps !== false && --$steps === 0) {
                    break;
                }
            } else {
                $output->writeln('<error>Failed UP: ' . $file->getFilename() . '</error>');
            }
        }

        $this->release();
        return 0;
    }
}