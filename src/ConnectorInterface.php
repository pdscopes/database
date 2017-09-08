<?php

namespace MadeSimple\Database;

interface ConnectorInterface
{
    /**
     * @param array $config
     *
     * @return \PDO
     */
    public function connect(array $config);
}