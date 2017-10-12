<?php

namespace MadeSimple\Database\Migration;

use MadeSimple\Database\Connection;
use MadeSimple\Database\ConnectionAwareTrait;
use MadeSimple\Database\Query\Raw;
use MadeSimple\Database\Statement\CreateTable;
use MadeSimple\Database\Statement\DropTable;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Migrator
{
    use ConnectionAwareTrait, LoggerAwareTrait;

    /**
     * Migrator constructor.
     *
     * @param Connection           $connection
     * @param LoggerInterface|null $logger
     */
    public function __construct(Connection $connection, LoggerInterface $logger = null)
    {
        $this->setConnection($connection);
        $this->setLogger($logger ?? new NullLogger);
    }

    /**
     * Create the migration table.
     */
    public function install()
    {
        if ($this->alreadyInstalled()) {
            $this->logger->notice('Migration table already installed');
            return;
        }

        // Create the table
        $this->connection->statement(function (CreateTable $table) {
            $table->table('migrations');
            $table->ifNotExists(true);
            $table->column('id')->integer(11, true)->null(false)->autoIncrement();
            $table->column('file')->char(255)->null(false);
            $table->column('batch')->integer(11, true)->null(false);
            $table->column('migrated_at')->datetime()->null(false);

            $table->primaryKey('id');
            $table->engine('InnoDB');
        });
        $this->logger->notice('Migration table created');
    }

    /**
     * List migrated files.
     *
     * @return string[]
     */
    public function list()
    {
        if (!$this->alreadyInstalled()) {
            return [];
        }

        return $this->connection->select()
            ->columns('file')
            ->from('migrations')
            ->orderBy('migrated_at')
            ->orderBy('file', 'desc')
            ->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    /**
     * Migrate the specified array of files.
     *
     * @param string|array $files
     */
    public function upgrade($files = [])
    {
        $files = (array) $files;
        if (!$this->alreadyInstalled()) {
            $this->logger->notice('Migration table not yet installed');
            return;
        }

        // Get the next batch number
        $batch = $this->getBatchNumber() + 1;

        foreach ($files as $file) {
            $file = realpath($file);
            // Check file exists
            if (!file_exists($file)) {
                $this->logger->warning('Migration file not found: "' . $file . '"');
                continue;
            }
            // Check the file has not already been migrated
            $migrated = $this->connection->select()->from('migrations')->where('file', '=', $file)->count();
            if ($migrated) {
                $this->logger->notice('Already migrated file: "' . $file . '"');
                continue;
            }

            $migration = $this->getMigrationClass($file);

            if ($migration instanceof MigrationInterface) {
                $migration->up($this->connection);
                $this->connection->insert()
                    ->into('migrations')
                    ->columns('file', 'batch', 'migrated_at')
                    ->values(realpath($file), $batch, date('Y-m-d H:i:s'))
                    ->query();
                $this->logger->notice('Migrated file: "' . $file . '"');
            }
        }
    }

    /**
     * Seed the database using the specified files.
     *
     * @param string|array $files
     */
    public function seed($files = [])
    {
        $files = (array) $files;
        if (!$this->alreadyInstalled()) {
            $this->logger->notice('Migration table not yet installed');
            return;
        }

        foreach ($files as $file) {
            $file = realpath($file);
            // Check file exists
            if (!file_exists($file)) {
                $this->logger->warning('Seed file not found: "' . $file . '"');
                continue;
            }

            $seed = $this->getMigrationClass($file);
            if ($seed instanceof SeedInterface) {
                $seed->sow($this->connection);
                $this->logger->notice('Seeded file: "' . $file . '"');
            }
        }
    }

    /**
     * Rollback the database $count batches or if null is given all batches.
     *
     * @param null|int $count
     */
    public function rollback($count = null)
    {
        if (!$this->alreadyInstalled()) {
            $this->logger->notice('Migration table not yet installed');
            return;
        }

        // Get the current batch number
        $batch = $this->getBatchNumber();
        $count = $count ?? $batch;

        if ($batch === 0) {
            $this->logger->notice('Already at clean installation');
            return;
        }

        while ($count > 0 && $batch > 0) {
            $rows = $this->connection->select()
                ->from('migrations')
                ->where('batch', '=', $batch)
                ->orderBy('migrated_at','desc')
                ->orderBy('file', 'desc')
                ->query()->fetchAll();
            foreach ($rows as $row) {
                if (!file_exists($row['file'])) {
                    $this->logger->warning('Migration file not found: "' . $row['file'] . '"');
                    continue;
                }
                $migration = $this->getMigrationClass($row['file']);
                if ($migration instanceof MigrationInterface) {
                    $migration->dn($this->connection);
                    $this->connection->delete()
                        ->from('migrations')
                        ->where('file', '=', $row['file'])
                        ->where('batch', '=', $batch)
                        ->query();
                    $this->logger->notice('Rolled back file: "' . $row['file'] . '"');
                }
            }
            $count--;
            $batch--;
        }
    }

    /**
     * Remove the migration table.
     */
    public function uninstall()
    {
        if (!$this->alreadyInstalled()) {
            $this->logger->notice('Migration table not yet installed');
            return;
        }

        if ($this->getBatchNumber() !== 0) {
            $this->logger->notice('Migration table cannot be uninstalled');
            return;
        }

        $this->connection->statement(function (DropTable $drop) {
            $drop->table('migrations');
        });
        $this->logger->notice('Migration table removed');
    }


    /**
     * @return bool
     */
    protected function alreadyInstalled()
    {
        switch ($this->connection->config('driver')) {
            case 'mysql':
                $stmt = $this->connection->rawQuery('SHOW TABLES LIKE \'migrations\'');
                return $stmt->rowCount() === 1;
            case 'sqlite':
                $count = $this->connection->select()
                    ->from('sqlite_master')
                    ->where('type', '=', 'table')
                    ->where('name', '=','migrations')
                    ->count();
                return (int) $count === 1;

            default:
                throw new \RuntimeException('Unknown database driver');
        }

    }

    /**
     * @return int
     */
    protected function getBatchNumber()
    {
        return (int) $this->connection->select()
            ->columns(new Raw('MAX(batch)'))
            ->from('migrations')
            ->query()->fetch(\PDO::FETCH_COLUMN, 0);
    }

    /**
     * @param string $file
     *
     * @return object
     */
    protected function getMigrationClass($file)
    {
        require_once $file;

        $fileName   = basename($file);
        $className  = substr($fileName, strrpos($fileName, '-') + 1, -4);
        $className  = str_replace('_', '', ucwords($className, '_'));
        $reflection = new \ReflectionClass($className);

        return $reflection->newInstance();
    }
}