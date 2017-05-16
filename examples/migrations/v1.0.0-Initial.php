<?php

use MadeSimple\Database\Connection;
use MadeSimple\Database\Migration;

/**
 * Class Initial
 *
 * @author
 */
class Initial implements Migration
{
    /**
     * @param Connection $connection
     *
     * @return void
     */
    function up(Connection $connection)
    {
        $table = $connection->create(function (\MadeSimple\Database\MySQL\Statement\Table\Create $table) {
            $table->name('foo');
            $table->column('id')->type('int(11)')->extras('unsigned NOT NULL AUTO_INCREMENT');
            $table->column('bar')->type('varchar(255)')->extras('DEFAULT NULL');
            $table->primaryKeys('id');
            $table->extras('ENGINE=InnoDB');
        });
        $table->execute();
    }

    /**
     * @param Connection $connection
     *
     * @return void
     */
    function dn(Connection $connection)
    {
        (new \MadeSimple\Database\Statement\Table\Drop($connection))->table('foo')->execute();
    }

}