<?php

namespace MadeSimple\Database\Query;

use PDOStatement;

class Insert extends QueryBuilder
{
    /**
     * Set the table to insert into.
     *
     * @param string $table
     *
     * @return Insert
     */
    public function into($table)
    {
        $this->statement['into'] = compact('table');
        return $this;
    }

    /**
     * Set the columns that will have values assigned.
     *
     * @param string|array|... $columns
     *
     * @return Insert
     */
    public function columns($columns)
    {
        unset($this->statement['columns']);

        return $this->addToStatement('columns', is_array($columns) ? $columns : func_get_args());
    }

    /**
     * Set the values that will be inserted.
     *
     * @param string|array|... $values
     *
     * @return Insert
     */
    public function values($values)
    {
        return $this->addToStatement('values', is_array($values) ? $values : func_get_args());
    }


    /**
     * @see PDOStatement::rowCount()
     * @return int
     */
    public function affectedRows()
    {
        return $this->pdoStatement->rowCount();
    }

    /**
     * @param string $name
     * @see PDO::lastInsertId()
     * @return int
     */
    public function lastInsertId($name = null)
    {
        return $this->pdo->lastInsertId($name);
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileQueryInsert($statement);
    }
}