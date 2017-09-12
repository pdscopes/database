<?php

use MadeSimple\Database\Connection;
use MadeSimple\Database\Migration\MigrationInterface;
use MadeSimple\Database\Statement\CreateTable;
use MadeSimple\Database\Statement\DropTable;

class ExampleComment implements MigrationInterface
{
    /**
     * @param Connection $connection
     *
     * @return void
     */
    function up(Connection $connection)
    {
        $connection->statement(function (CreateTable $table) {
            $table->table('comment');
            $table->ifNotExists(true);
            $table->column('id')->integer(11, true)->notNull()->autoIncrement();
            $table->column('uuid')->char(36)->notNull();
            $table->column('user_id')->integer(11, true)->null(true);
            $table->column('post_id')->integer(11, true)->null(true);
            $table->column('content')->text()->notNull();
            $table->column('created_at')->timestamp()->notNull()->useCurrent();
            $table->column('updated_at')->timestamp()->notNull()->useCurrent();


            $table->primaryKey('id')
                  ->foreignKey('post_id', 'post', 'id', 'CASCADE')
                  ->engine('InnoDB');
        });
    }

    /**
     * @param Connection $connection
     *
     * @return void
     */
    function dn(Connection $connection)
    {
        $connection->statement(function (DropTable $drop) { $drop->table('comment'); });
    }

}