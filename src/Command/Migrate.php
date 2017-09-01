<?php

namespace MadeSimple\Database\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Migrate
 *
 * @package MadeSimple\Database\Command
 * @author  Peter Scopes
 */
class Migrate extends Command
{
    use InteractsWithDatabaseMigrations, LockableTrait;

    /**
     * Add default required arguments.
     */
    protected function configure()
    {
        $this->configureDatabase();
        $this
            ->setName('migrate')
            ->setDescription('Install and upgrade your database migrations')
            ->setHelp('This command allows you to install and upgrade your database migrations')
            ->addOption('migrationsPath', 'm', InputOption::VALUE_REQUIRED, 'Path to your database migration files', 'database/database')
            ->addOption('seed', 'd', InputOption::VALUE_NONE, 'Seed your database with dummy data')
            ->addOption('seedsPath', 's', InputOption::VALUE_REQUIRED, 'Path to your database seed files', 'database/seeds')
            ->addUsage('sqlite:database.sqlite');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Install
        $this->executeSubCommand($input, $output, 'migrate:install');

        // Upgrade
        $this->executeSubCommand($input, $output, 'migrate:upgrade', ['path' => $input->getOption('migrationsPath')]);

        // Seed
        if ($input->getOption('seed')) {
            $this->executeSubCommand($input, $output, 'migrate:seed', ['path' => $input->getOption('seedsPath')]);
        }

        return 0;
    }

    protected function executeSubCommand(InputInterface $input, OutputInterface $output, $command, array $options = [])
    {
        $arguments = ['command' => $command];

        foreach (['dbDsn', 'dbUser', 'dbPass'] as $argument) {
            if ($input->hasArgument($argument)) {
                $arguments[$argument] = $input->getArgument($argument);
            }
        }
        $options += [
            'dotenv'     => $input->getOption('dotenv'),
            'dotenvFile' => $input->getOption('dotenvFile'),
        ];
        foreach ($options as $k => $v) {
            $arguments['--' . $k] = $v;
        }
        $arguments['--noInteract'] = true;


        $this->getApplication()->find($command)->run(new ArrayInput($arguments), $output);
    }
}