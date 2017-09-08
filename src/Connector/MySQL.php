<?php

namespace MadeSimple\Database\Connector;

use MadeSimple\Database\ConnectionAwareTrait;
use Psr\Log\LoggerInterface;

class MySQL extends BaseConnector
{
    use ConnectionAwareTrait;

    /**
     * MySQL constructor.
     *
     * @param null|LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        parent::__construct($logger);
    }

    /**
     * Connect to a database using the given configuration.
     *
     * @param array $config
     *
     * @return \PDO
     */
    public function connect(array $config)
    {
        // Create the PDO
        $dsn     = $this->getDsn($config);
        $options = $this->getOptions($config);
        $pdo     = $this->createPdo($dsn, $config, $options);

        $sql = '';
        // Configure encoding
        if (isset($config['charset'])) {
            $sql .= "set names '{$config['charset']}'";
            if (isset($config['collation'])) {
                $sql .= " collate '{$config['collation']}'";
            }
            $sql .= ';';
        }
        // Configure timezone
        if (isset($config['timezone'])) {
            $sql .= 'set time_zone="' . $config['timezone'] . '";';
        }
        if (!empty($sql)) {
            $this->logger->debug('Configuring Connection', ['sql' => $sql]);
            $pdo->exec($sql);
        }

        return $pdo;
    }

    /**
     * Determine if a unix socket or host DSN should be created.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config)
    {
        return "mysql:" .
            (isset($config['unix_socket']) && !empty($config['unix_socket'])
            ? $this->getSocketDsn($config)
            : $this->getHostDsn($config));
    }

    /**
     * Get the DSN for unix socket connection.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getSocketDsn(array $config)
    {
        return "unix_socket={$config['unix_socket']};dbname={$config['database']}";
    }

    /**
     * Get the DSN for host / port connection.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getHostDsn(array $config)
    {
        return isset($config['port']) && $config['port'] !== ''
            ? "host={$config['host']};port={$config['port']};dbname={$config['database']}"
            : "host={$config['host']};dbname={$config['database']}";
    }
}