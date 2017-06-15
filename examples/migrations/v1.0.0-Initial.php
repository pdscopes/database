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
            $table->column('id')->int(11, true)->notNull()->autoIncrement();
            $table->column('uuid')->char(36)->notNull();
            $table->column('email')->char(255)->notNull();
            $table->column('password')->char(255)->notNull();
            $table->column('createdAt')->timestamp()->notNull()->defaultValue('CURRENT_TIMESTAMP');
            $table->column('updatedAt')->timestamp()->notNull()->defaultValue('CURRENT_TIMESTAMP');


            $table->primaryKey('id');
            $table->unique('uuid');
            $table->unique('email');
            $table->engine('InnoDB');
        })->execute();

        $connection->create('post', function (Table\Create $table) {
            $table->ifNotExists(true);
            $table->column('id')->int(11, true)->notNull()->autoIncrement();
            $table->column('uuid')->char(36)->notNull();
            $table->column('userId')->int(11, true)->null(true);
            $table->column('title')->char(255)->notNull();
            $table->column('content')->text()->notNull();
            $table->column('createdAt')->timestamp()->notNull()->defaultValue('CURRENT_TIMESTAMP');
            $table->column('updatedAt')->timestamp()->notNull()->defaultValue('CURRENT_TIMESTAMP');


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