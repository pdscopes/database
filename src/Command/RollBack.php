<?php

namespace MadeSimple\Database\Command;

use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class RollBack
 *
 * @package MadeSimple\Database\Command
 * @author  Peter Scopes
 */
class RollBack extends Migrate
{
    use LockableTrait;

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('migrate:rollback')
            ->setDescription('Rollback to your previous database migration')
            ->setHelp('This command allows you to rollback your database to the previous migration')
            ->addOption('batches', 'b', InputOption::VALUE_REQUIRED, 'Number of batches to rollback', 1)
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Rollback to before your first migration')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path to your database migration files', 'database/migrations')
            ->addUsage('sqlite:database.sqlite')
            ->addUsage('--all path/to/migration/files sqlite:database.sqlite')
            ->addUsage('--batches 1 sqlite:database.sqlite');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Validate input
        if ($input->getOption('batches') !== null && !is_numeric($input->getOption('batches'))) {
            throw new \InvalidArgumentException('Batches must be an number');
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
            $output->writeln('Migrations not installed');
            $this->release();
            return 1;
        }

        if ($batch === 0) {
            $output->writeln('Already at clean installation');
            $this->release();
            return 0;
        }

        $batches = $input->getOption('all') ? $batch : $input->getOption('batches');

        // Perform installation
        $path = $input->getOption('path');
        while ($batches > 0) {
            $output->writeln('Rolling back batch: ' . $batch);
            $rows = $connection->select()->from('migrations')->where('batch = ?', $batch)->execute()->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                if ($this->migrateDn($connection, $batch, rtrim($path, '/') . '/' . $row['fileName'])) {
                    $output->writeln('Migrated DN: ' . $row['fileName']);
                }
            }

            $batch--;
            $batches--;
        }


        $this->release();
        return 0;
    }
}