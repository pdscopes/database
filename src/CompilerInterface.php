<?php

namespace MadeSimple\Database;

interface CompilerInterface extends ConnectionAwareInterface
{
    /**
     * @param string $dirtyColumnRef
     *
     * @return string
     */
    function sanitise($dirtyColumnRef);


    /**
     * Compile a SELECT query into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileQuerySelect(array $statement);

    /**
     * Compile an INSERT query into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileQueryInsert(array $statement);

    /**
     * Compile an UPDATE query into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileQueryUpdate(array $statement);

    /**
     * Compile a DELETE query into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileQueryDelete(array $statement);


    /**
     * Compile a CREATE DATABASE statement into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileStatementCreateDb(array $statement);

    /**
     * Compile a DROP DATABASE statement into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileStatementDropDb(array $statement);

    /**
     * Compile a CREATE TABLE statement into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileStatementCreateTable(array $statement);

    /**
     * Compile a ALTER TABLE statement into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileStatementAlterTable(array $statement);

    /**
     * Compile a TRUNCATE TABLE statement into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileStatementTruncateTable(array $statement);

    /**
     * Compile a DROP TABLE statement into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileStatementDropTable(array $statement);

    /**
     * Compile a CREATE INDEX statement into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileStatementCreateIndex(array $statement);

    /**
     * Compile a DROP INDEX statement into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileStatementDropIndex(array $statement);

    /**
     * Compile a CREATE VIEW statement into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileStatementCreateView(array $statement);

    /**
     * Compile a UPDATE VIEW statement into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileStatementUpdateView(array $statement);

    /**
     * Compile a DROP VIEW statement into the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string, array]
     */
    function compileStatementDropView(array $statement);
}