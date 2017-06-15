<?php

namespace MadeSimple\Database\Command;

use MadeSimple\Database\Connection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class InteractsWithDatabase
 *
 * @package MadeSimple\Database\Command
 * @author
 */
trait InteractsWithDatabaseMigrations
{
    /**
     * Add default required arguments.
     */
    protected function configureDatabase()
    {
        $this->addArgument('dbDsn', InputArgument::REQUIRED, 'DSN for the database to run migration)');
        $this->addArgument('dbUser', InputArgument::OPTIONAL, 'Username for the database');
        $this->addArgument('dbPass', InputArgument::OPTIONAL, 'Password for the database');
        $this->addOption('dotenv', 'e', InputOption::VALUE_NONE, 'Load arguments from [.env] file');
        $this->addOption('dotenvFile', 'f', InputOption::VALUE_REQUIRED, 'Location of [.env] file', '.env');
        $this->addOption('noInteract', 'i', InputOption::VALUE_NONE, 'No interact');
    }

    /**
     * Interactively request missing arguments.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        // If no interact is set true
        if ($input->hasOption('noInteract') && $input->getOption('noInteract')) {
            return;
        }

        // Request the password if user name given and not password
        $helper = $this->getHelper('question');
        if ($input->getArgument('dbUser') && !$input->getArgument('dbPass')) {
            $question = new Question('Password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $input->setArgument('dbPass', $helper->ask($input, $output, $question));
        }
    }

    /**
     * Connect to the database.
     *
     * @param InputInterface $input
     *
     * @return Connection
     */
    protected function connect(InputInterface $input)
    {
        // Retrieve input arguments
        $dsn  = $input->getArgument('dbDsn');
        $user = $input->getArgument('dbUser');
        $pass = $input->getArgument('dbPass');

        // If using dotenv file load and retrieve values
        if ($input->getOption('dotenv')) {
            $dotenv = new \Dotenv\Dotenv(getcwd(), $input->getOption('dotenvFile'));
            $dotenv->load();

            $dsn  = getenv($dsn);
            $user = $user === null ? null : getenv($user);
            $pass = $pass === null ? null : getenv($pass);
        }

        // Create the pdo
        $pdo = new \PDO($dsn, $user, $pass);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return Connection::factory($pdo);
    }

    /**
     * Retrieve the batch the migrations table is on or false if the migrations table does not exist.
     *
     * @param Connection $connection
     *
     * @return int|false
     */
    protected function batch(Connection $connection)
    {
        switch ($connection->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            case 'mysql':
                $query = $connection->select()
                    ->columns('COUNT(*)')
                    ->from('information_schema.tables')
                    ->where('table_schema = ?', $connection->query('select database()')->fetchColumn())
                    ->andWhere('table_name = \'migrations\'');
                break;
            case 'sqlite':
                $query = $connection->select()
                    ->columns('COUNT(*)')
                    ->from('sqlite_master')
                    ->where('type = \'table\'')
                    ->andWhere('name = \'migrations\'');
                break;

            default:
                throw new \RuntimeException('Unsupported PDO Driver');
        }

        $exists = $query->execute()->fetchColumn(0);
        if (!$exists) {
            return false;
        }

        // Find the previously highest batch number
        $latestBatch = $connection
            ->select()->columns('batch')->from('migrations')->orderBy('batch DESC')->limit(1)
            ->execute()->fetchColumn(0);

        return (int) $latestBatch;
    }

}