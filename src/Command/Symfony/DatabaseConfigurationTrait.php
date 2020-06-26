<?php

namespace MadeSimple\Database\Command\Symfony;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

trait DatabaseConfigurationTrait
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var bool
     */
    protected $configured;

    /**
     * @return string[] List of supported database drivers
     */
    protected function supportedDbDrivers(): array
    {
        return ['mysql', 'sqlite'];
    }

    /**
     * Adds the database configuration to the command.
     */
    protected function addDatabaseConfigure()
    {
        $this->addOption('no-env', null, InputOption::VALUE_NONE, 'Do not load db config from environment');
        $this->addOption('db-driver', null, InputOption::VALUE_REQUIRED, 'Driver for the database to run migration (available: '.join(', ', $this->supportedDbDrivers()).')');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Check the value of db-driver
        if ($input->getOption('db-driver') && !in_array($input->getOption('db-driver'), $this->supportedDbDrivers())) {
            throw new \InvalidArgumentException('db-driver must be one of: ' . join(', ',  $this->supportedDbDrivers()));
        }

        // Default to set configuration from the environment
        $this->setConfigurationFromEnvironment($input, $output);
    }

    /**
     * Interactively request missing arguments.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($this->configured) {
            return;
        }

        // Request missing data
        $helper = $this->getHelper('question');
        $this->config['driver'] = $input->getOption('db-driver');
        if (!$input->getOption('db-driver')) {
            $question = new ChoiceQuestion('Choose a database driver: ', ['mysql', 'sqlite']);
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

    protected function setConfigurationFromEnvironment(InputInterface $input, OutputInterface $output)
    {
        // If set to not config from the environment
        if ($input->getOption('no-env')) {
            $output->writeln('<fg=yellow>Not loading db config from env</>', OutputInterface::VERBOSITY_DEBUG);
            return;
        }

        $this->config['driver'] = getenv('DATABASE_DRIVER');
        switch ($this->config['driver']) {
            case 'mysql':
                $this->config['host']     = getenv('DATABASE_HOST');
                $this->config['database'] = getenv('DATABASE_NAME');
                $this->config['username'] = getenv('DATABASE_USERNAME');
                $this->config['password'] = getenv('DATABASE_PASSWORD');
                $this->configured = (bool) ($this->config['host'] && $this->config['database']);
                break;

            case 'sqlite':
                $this->config['database'] = getenv('DATABASE_LOCATION');
                $this->configured = (bool) $this->config['database'];
                break;

            default:
                $this->configured = false;
        }
    }
}
