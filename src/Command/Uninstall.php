<?php

namespace MadeSimple\Database\Command;

use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Uninstall
 *
 * @package MadeSimple\Database\Command
 * @author  Peter Scopes
 */
class Uninstall extends Migrate
{
    use LockableTrait;

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('migrate:uninstall')
            ->setAliases(['migrate:remove'])
            ->setDescription('Uninstall the database migrations table')
            ->setHelp('This command allows you to uninstall the database migrations table')
            ->addUsage('sqlite:database.sqlite');
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
            $output->writeln('Already uninstalled');
            $this->release();
            return 0;
        }

        if ($batch > 0) {
            $output->writeln('Please rollback before uninstalling');
            $this->release();
            return 1;
        }

        if ($batch == 0) {
            $connection->drop()->table('migrations')->execute();
            $output->writeln('Removed migrations table');
        }

        $this->release();
        return 0;
    }
}