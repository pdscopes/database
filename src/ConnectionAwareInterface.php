<?php

namespace MadeSimple\Database;

interface ConnectionAwareInterface
{
    /**
     * @param Connection|null $connection
     */
    function setConnection(Connection $connection = null);
}