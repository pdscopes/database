<?php

use MadeSimple\Database\Connection;
use MadeSimple\Database\Migration\MigrationInterface;
use MadeSimple\Database\Statement\CreateTable;
use MadeSimple\Database\Statement\DropTable;

class ExampleInitial implements MigrationInterface
{
    /**
     * @param Connection $connection
     *
     * @return void
     */
    function up(Connection $connection)
    {
        $connection->statement(function (CreateTable $create) {
            $create->table('user');
            $create->ifNotExists();
            $create->column('id')->integer(11, true)->notNull()->autoIncrement()->primaryKey();
            $create->column('uuid')->char(36)->notNull()->unique();
            $create->column('email')->char(255)->notNull()->unique();
            $create->column('password')->char(255)->notNull();
            $create->column('created_at')->timestamp()->notNull()->useCurrent();
            $create->column('updated_at')->timestamp()->notNull()->useCurrent();

            $create->engine('InnoDB')->charset('utf8mb4', 'utf8mb4_general_ci');
        });

        $connection->statement(function (CreateTable $create) {
            $create->table('post');
            $create->ifNotExists(true);
            $create->column('id')->integer(11, true)->notNull()->autoIncrement();
            $create->column('uuid')->char(36)->notNull();
            $create->column('user_id')->integer(11, true)->null(true);
            $create->column('title')->char(255)->notNull();
            $create->column('content')->text()->notNull();
            $create->column('created_at')->timestamp()->notNull()->useCurrent();
            $create->column('updated_at')->timestamp()->notNull()->useCurrent();


            $create
                ->primaryKey('id')
                ->foreignKey('user_id', 'user', 'id', 'CASCADE')
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
        $connection->statement(function (DropTable $drop) { $drop->table('post'); });
        $connection->statement(function (DropTable $drop) { $drop->table('user'); });
    }

}