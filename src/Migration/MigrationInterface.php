<?php

namespace MadeSimple\Database\Migration;

use MadeSimple\Database\Connection;

interface MigrationInterface
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