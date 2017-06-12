<?php

namespace MadeSimple\Database;

/**
 * Interface Seed
 *
 * @package MadeSimple\Database
 * @author  Peter Scopes
 */
interface Seed
{
    /**
     * @param Connection $connection
     *
     * @return void
     */
    function sow(Connection $connection);
}