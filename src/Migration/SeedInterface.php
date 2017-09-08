<?php

namespace MadeSimple\Database\Migration;

use MadeSimple\Database\Connection;

interface SeedInterface
{
    /**
     * @param Connection $connection
     *
     * @return void
     */
    function sow(Connection $connection);
}