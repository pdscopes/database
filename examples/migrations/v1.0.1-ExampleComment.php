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
        $connection->statement(function (CreateTable $create) {
            $create->table('comment');
            $create->ifNotExists(true);
            $create->column('id')->integer(11, true)->notNull()->autoIncrement();
            $create->column('uuid')->char(36)->notNull();
            $create->column('user_id')->integer(11, true)->null(true);
            $create->column('post_id')->integer(11, true)->null(true);
            $create->column('content')->text()->notNull();
            $create->column('created_at')->timestamp()->notNull()->useCurrent();
            $create->column('updated_at')->timestamp()->notNull()->useCurrent();


            $create
                ->primaryKey('id')
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