<?php

namespace MadeSimple\Database;

trait ConnectionAwareTrait
{
    /**
     * @var Connection
     */
    public $connection;

    /**
     * @param Connection|null $connection
     */
    public function setConnection(Connection $connection = null)
    {
        $this->connection = $connection;
    }
}