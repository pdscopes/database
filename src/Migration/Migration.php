<?php

namespace MadeSimple\Database\Migration;

use MadeSimple\Database\Connection;

/**
 * Class Migration
 *
 * @package MadeSimple\Database\Migration
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