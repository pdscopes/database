<?php

namespace MadeSimple\Database\Command;

use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class Seed
 *
 * @package MadeSimple\Database\Command
 * @author  Peter Scopes
 */
class Seed extends Migrate
{
    use LockableTrait;

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('migrate:seed')
            ->setDescription('Seed your database with dummy data')
            ->setHelp('This command allows you to populate your database with dummy data')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path to your database seed files', 'database/seeds')
            ->addUsage('sqlite:database.sqlite')
            ->addUsage('DB_DSN DB_USER DB_PASS -e')
            ->addUsage('-p path/to/seed/files sqlite:database.sqlite')
            ->addUsage('-s 1 sqlite:database.sqlite');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Validate input
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
            if($this->sowSeed($connection, $file)) {
                $output->writeln('Seeded: ' . $file->getFilename());

                if ($steps !== false && --$steps === 0) {
                    break;
                }
            } else {
                $output->writeln('<error>Failed Seed: ' . $file->getFilename() . '</error>');
            }
        }

        $this->release();
        return 0;
    }
}