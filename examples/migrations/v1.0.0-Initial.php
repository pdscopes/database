<?php

use MadeSimple\Database\Connection;
use MadeSimple\Database\Migration;
use MadeSimple\Database\MySQL\Statement\Table;

class Initial implements Migration
{
    /**
     * @param Connection $connection
     *
     * @return void
     */
    function up(Connection $connection)
    {
        $connection->create('user', function (Table\Create $table) {
            $table->ifNotExists(true);
            $table->column('id')->int(11, true)->null(false)->autoIncrement(true);
            $table->column('uuid')->char(36)->null(false);
            $table->column('email')->char(255)->null(false);
            $table->column('password')->char(255)->null(false);
            $table->column('createdAt')->timestamp()->null(false)->defaultValue('CURRENT_TIMESTAMP');
            $table->column('updatedAt')->timestamp()->null(false)->defaultValue('CURRENT_TIMESTAMP');


            $table->primaryKey('id');
            $table->unique('uuid');
            $table->unique('email');
            $table->engine('InnoDB');
        })->execute();

        $connection->create('post', function (Table\Create $table) {
            $table->ifNotExists(true);
            $table->column('id')->int(11, true)->null(false)->autoIncrement(true);
            $table->column('uuid')->char(36)->null(false);
            $table->column('userId')->int(11, true)->null(true);
            $table->column('title')->char(255)->null(false);
            $table->column('content')->text()->null(false);
            $table->column('createdAt')->timestamp()->null(false)->defaultValue('CURRENT_TIMESTAMP');
            $table->column('updatedAt')->timestamp()->null(false)->defaultValue('CURRENT_TIMESTAMP');


            $table->primaryKey('id');
            $table->foreignKey('userId', 'user', 'id');
            $table->engine('InnoDB');
        })->execute();
    }

    /**
     * @param Connection $connection
     *
     * @return void
     */
    function dn(Connection $connection)
    {
        $connection->drop()->table('post')->execute();
        $connection->drop()->table('user')->execute();
    }

}