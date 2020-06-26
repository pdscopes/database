<?php

namespace MadeSimple\Database;

class Pool
{
    /**
     * @var string
     */
    protected $default;

    /**
     * @var Connection[]
     */
    protected $connections;

    /**
     * Pool constructor.
     *
     * @param Connection ...$connections
     */
    function __construct(... $connections)
    {
        $this->connections = [];
        array_walk_recursive($connections, function ($e, $k) {
            if (!$e instanceof Connection) {
                throw new \InvalidArgumentException('Pool only accepts Connections');
            }
            $this->connections[$k] = $e;
        });
        if (!empty($connections)) {
            $this->default = array_keys($this->connections)[0];
        }
    }

    /**
     * @param string $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @param string|null $name
     *
     * @return Connection
     */
    public function get($name = null)
    {
        $name = $name ?? $this->default;

        return $this->connections[$name];
    }

    /**
     * @param string     $name
     * @param Connection $connection
     */
    public function set($name, Connection $connection)
    {
        if (empty($this->connections)) {
            $this->setDefault($name);
        }
        $this->connections[$name] = $connection;
    }
}