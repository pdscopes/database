<?php

namespace MadeSimple\Database;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class Builder
{
    use ConnectionAwareTrait, LoggerAwareTrait;

    /**
     * @var array
     */
    protected $statement = [];

    /**
     * Builder constructor.
     *
     * @param Connection|null      $connection
     * @param LoggerInterface|null $logger
     */
    public function __construct(Connection $connection = null, LoggerInterface $logger = null)
    {
        $this->setConnection($connection);
        $this->setLogger($logger ?? new NullLogger);
        if (method_exists($this, 'setPdo')) {
            $this->setPdo($connection->pdo);
        }
        if (method_exists($this, 'setCompiler')) {
            $this->setCompiler($connection->compiler);
        }
    }

    /**
     * Add to the statement.
     *
     * @param string      $section
     * @param mixed|array $value
     *
     * @return static
     */
    protected function addToStatement($section, $value)
    {
        $value = (array) $value;

        if (!array_key_exists($section, $this->statement)) {
            $this->statement[$section] = $value;
        } else {
            $this->statement[$section] = array_merge($this->statement[$section], $value);
        }

        return $this;
    }
}