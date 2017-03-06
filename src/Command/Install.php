<?php

namespace MadeSimple\Database\Command;

use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Migrate
 *
 * @package MadeSimple\Database\Command
 * @author  Peter Scopes
 */
class Install extends Migrate
{
    use LockableTrait;

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('migrate:install')
            ->setDescription('Install the database migrations table')
            ->setHelp('This command allows you to install the database migrations table')
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

        if ($batch !== false) {
            $output->writeln('Already installed');
            $this->release();
            return 0;
        }

        // Create the migrations table
        switch ($connection->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            case 'mysql':
                $table = $connection->create(function (\MadeSimple\Database\MySQL\Statement\Table\Create $table) {
                    $table->name('migrations');
                    $table->column('id')->type('int(11)')->extras('unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)');
                    $table->column('fileName')->type('char(255)')->extras('NOT NULL');
                    $table->column('batch')->type('int(11)')->extras('unsigned NOT NULL');
                    $table->column('migratedAt')->type('datetime')->extras('NOT NULL');
                    $table->extras('ENGINE=InnoDB');
                });
                break;
            case 'sqlite':
                $table = $connection->create(function (\MadeSimple\Database\SQLite\Statement\Table\Create $table) {
                    $table->name('migrations');
                    $table->column('id')->extras('PRIMARY KEY');
                    $table->column('fileName');
                    $table->column('batch');
                    $table->column('migratedAt');
                });
                break;

            default:
                throw new \RuntimeException('Unsupported PDO Driver');
        }
        $connection->query($table->toSql());
        $output->writeln('Created migrations table');

        $this->release();
        return 0;
    }
}