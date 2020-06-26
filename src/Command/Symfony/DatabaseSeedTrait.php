<?php

namespace MadeSimple\Database\Command\Symfony;

use MadeSimple\Database\Migration\Migrator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

trait DatabaseSeedTrait
{
    protected function seedInitialize(InputInterface $input, OutputInterface $output)
    {
        // Ensure default value for options with optional value
        $input->setOption('seed', $input->getOption('seed') ?? $this->getDefinition()->getOption('seed')->getDefault());

        // Ensure locations exist
        $fs = new Filesystem();
        if ($input->getParameterOption(['--seed', '-s'], false, true) !== false && !$fs->exists($input->getOption('seed'))) {
            throw new \InvalidArgumentException('Seed path must be a directory that exists');
        }
    }

    protected function executeSeed(Migrator $migrator, InputInterface $input)
    {
        if ($this->getDefinition()->getOption('seed')->isValueRequired() || $input->getParameterOption(['--seed', '-s'], false, true) !== false) {
            $finder = Finder::create()->files()->sortByName()->name('*.php');
            $migrator->seed(iterator_to_array($finder->in($input->getOption('seed'))));
        }
    }
}