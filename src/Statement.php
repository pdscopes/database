<?php

namespace MadeSimple\Database;

/**
 * Class Statement
 *
 * @package MadeSimple\Database
 * @author  Peter Scopes
 */
interface Statement
{
    /**
     * Convert the statement object into an SQL string to be executed.
     *
     * @return string
     * @see __toString
     */
    public function toSql();

    /**
     * Execute the statement.
     *
     * @param null|array $parameters Override the parameters already passed to the statement
     *
     * @return \PDOStatement|false FALSE on failure
     */
    public function execute(array $parameters = null);

    /**
     * Convert the statement object into an SQL string to be executed.
     *
     * @return string
     * @see toSql
     */
    public function __toString();
}