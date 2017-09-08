<?php

namespace MadeSimple\Database\Connector;

use MadeSimple\Database\ConnectorInterface;
use MadeSimple\Database\Compiler;
use PDO;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class BaseConnector implements ConnectorInterface
{
    use LoggerAwareTrait;

    /**
     * @var Compiler
     */
    protected $compiler;

    /**
     * MySQL constructor.
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->setLogger($logger ?? new NullLogger);
    }

    /**
     * The default PDO connection options.
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false,
    ];

    /**
     * @param array $config
     *
     * @return array
     */
    protected function getOptions(array $config)
    {
        $options = $config['options'] ?? [];
        return (array) (array_diff_key($this->options, $options) + $options);
    }

    /**
     * @param string $dsn
     * @param array  $config
     * @param array  $options
     *
     * @return PDO
     */
    protected function createPdo($dsn, array $config, array $options)
    {
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';

        $this->logger->debug('Establishing Connection', ['dsn' => $dsn, 'user' => $username]);
        return new PDO($dsn, $username, $password, $options);
    }
}