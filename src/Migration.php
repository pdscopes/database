<?php

namespace MadeSimple\Database;

/**
 * Interface Migration
 *
 * @package MadeSimple\Database
 * @author  Peter Scopes
 */
interface Migration
{
    /**
     * @param Connection $connection
     *
     * @return void
     */
    function up(Connection $connection);

    /**
     * @param Connection $connection
     *
     * @return void
     */
    function dn(Connection $connection);
}