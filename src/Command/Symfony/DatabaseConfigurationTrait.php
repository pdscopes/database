<?php

namespace MadeSimple\Database\Command\Symfony;

use Dotenv\Dotenv;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

trait DatabaseConfigurationTrait
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * Adds the database configuration to the command.
     */
    protected function addDatabaseConfigure()
    {
        $this->addArgument('dbDriver', InputArgument::OPTIONAL, 'Driver for the database to run migration');
        $this->addArgument('dbUser', InputArgument::OPTIONAL, 'Username for the database');

        $this->addOption('dotenv', 'e', InputOption::VALUE_NONE, 'Load configuration from [.env] file');
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
        if ($input->getOption('dotenv')) {
            $dotenv = new Dotenv(getcwd(), $input->getOption('dotenvFile'));
            $dotenv->load();
            $dotenv->required('DATABASE_DRIVER')->allowedValues(['mysql', 'sqlite']);
            $this->config['driver'] = getenv('DATABASE_DRIVER');
            switch ($this->config['driver']) {
                case 'mysql':
                    $dotenv->required(['DATABASE_HOST', 'DATABASE_NAME', 'DATABASE_USERNAME', 'DATABASE_PASSWORD']);
                    $this->config['host']     = getenv('DATABASE_HOST');
                    $this->config['database'] = getenv('DATABASE_NAME');
                    $this->config['username'] = getenv('DATABASE_USERNAME');
                    $this->config['password'] = getenv('DATABASE_PASSWORD');
                    break;

                case 'sqlite':
                    $dotenv->required(['DATABASE_LOCATION']);
                    $this->config['database'] = getenv('DATABASE_LOCATION');
                    break;
            }
            return ;
        }

        if ($input->getOption('noInteract')) {
            throw new \RuntimeException('Cannot use no interact without -e option');
        }

        // Request missing data
        $helper = $this->getHelper('question');
        $this->config['driver'] = $input->getArgument('dbDriver');
        if (!$input->getArgument('dbDriver')) {
            $question = new Question('Database driver: ');
            $this->config['driver'] = $helper->ask($input, $output, $question);
        }

        switch ($this->config['driver']) {
            case 'mysql':
                $question = new Question('Database Host: ');
                $this->config['host'] = $helper->ask($input, $output, $question);
                $question = new Question('Database Name: ');
                $this->config['database'] = $helper->ask($input, $output, $question);
                $question = new Question('Database Username: ');
                $this->config['username'] = $helper->ask($input, $output, $question);
                $question = new Question('Database Password: ');
                $question->setHidden(true);
                $question->setHiddenFallback(false);
                $this->config['password'] = $helper->ask($input, $output, $question);
                break;

            case 'sqlite':
                $question = new Question('Database Location: ');
                $this->config['database'] = $helper->ask($input, $output, $question);
                break;
        }
    }
}