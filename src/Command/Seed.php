<?php

namespace MadeSimple\Database\Command;

use MadeSimple\Database\Connection;
use Symfony\Component\Console\Command\Command;
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
class Seed extends Command
{
    use InteractsWithDatabaseMigrations, LockableTrait;

    protected function configure()
    {
        $this->configureDatabase();
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
        $finder = new Finder();
        $finder->files()->sortByName()->in($input->getOption('path'))->name('*.php');
        foreach ($finder as $file) {
            if($this->sowSeed($connection, $file)) {
                $output->writeln('Seeded: ' . $file->getFilename());
            } else {
                $output->writeln('<error>Failed Seed: ' . $file->getFilename() . '</error>');
            }
        }

        $this->release();
        return 0;
    }

    /**
     * Sow seed.
     *
     * @param Connection $connection
     * @param string     $file
     *
     * @return bool
     */
    protected function sowSeed(Connection $connection, $file)
    {
        if (!file_exists($file)) {
            return false;
        }

        require $file;

        $fileName   = basename($file);
        $className  = substr($fileName, strrpos($fileName, '-') + 1, -4);
        $reflection = new \ReflectionClass($className);
        $seed  = $reflection->newInstance();

        if ($seed instanceof \MadeSimple\Database\Seed) {
            $seed->sow($connection);

            return true;
        }

        return false;
    }
}