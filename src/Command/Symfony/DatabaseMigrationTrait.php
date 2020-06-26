<?php

namespace MadeSimple\Database\Command\Symfony;

use MadeSimple\Database\Migration\Migrator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

trait DatabaseMigrationTrait
{
    protected function migrationInitialize(InputInterface $input, OutputInterface $output)
    {
        // Ensure default value for options with optional value
        $input->setOption('path', $input->getOption('path') ?? $this->getDefinition()->getOption('path')->getDefault());
        if ($input->hasOption('step')) {
            $input->setOption('step', $input->getOption('step') === null ? 1 : $input->getOption('step'));
        }

        // Ensure locations exist
        $fs = new Filesystem();
        if (!$fs->exists($input->getOption('path'))) {
            throw new \InvalidArgumentException('Migrations path must be a directory that exists');
        }
    }

    protected function executeMigrateUpgrade(Migrator $migrator, InputInterface $input)
    {
        // Find the necessary files
        $finder = Finder::create()->files()->sortByName()->name('*.php');
        $files  = array_map('realpath', iterator_to_array($finder->in($input->getOption('path'))));

        // Limit the files to be upgraded
        $files = array_diff($files, $migrator->list());
        if ($input->hasOption('step') && $input->getOption('step')) {
            $files = array_slice($files, 0, (int) $input->getOption('step'));
        }

        $migrator->upgrade($files);
    }
}