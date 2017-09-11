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
        $connection->statement(function (CreateTable $table) {
            $table->name('user');
            $table->ifNotExists();
            $table->column('id')->integer(11, true)->notNull()->autoIncrement()->primaryKey();
            $table->column('uuid')->char(36)->notNull()->unique();
            $table->column('email')->char(255)->notNull()->unique();
            $table->column('password')->char(255)->notNull();
            $table->column('created_at')->timestamp()->notNull()->useCurrent();
            $table->column('updated_at')->timestamp()->notNull()->useCurrent();

            $table->engine('InnoDB')->charset('utf8mb4', 'utf8mb4_general_ci');
        });

        $connection->statement(function (CreateTable $table) {
            $table->name('post');
            $table->ifNotExists(true);
            $table->column('id')->integer(11, true)->notNull()->autoIncrement();
            $table->column('uuid')->char(36)->notNull();
            $table->column('user_id')->integer(11, true)->null(true);
            $table->column('title')->char(255)->notNull();
            $table->column('content')->text()->notNull();
            $table->column('created_at')->timestamp()->notNull()->useCurrent();
            $table->column('updated_at')->timestamp()->notNull()->useCurrent();


            $table->primaryKey('id')
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