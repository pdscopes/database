<?php

namespace MadeSimple\Database\Connector;

use Psr\Log\LoggerInterface;

class SQLite extends BaseConnector
{
    /**
     * SQLite constructor.
     *
     * @param null|LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        parent::__construct($logger);
    }

    public function connect(array $config)
    {
        $options = $this->getOptions($config);

        if ($config['database'] === ':memory:') {
            return $this->createPdo('sqlite::memory:', $config, $options);
        }

        $path = realpath($config['database']);

        if ($path === false) {
            throw new \RuntimeException('Database does not exist: ' . $config['database']);
        }

        return $this->createPdo('sqlite:'.$path, $config, $options);
    }
}