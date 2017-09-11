<?php

namespace MadeSimple\Database\Query;

class Update extends QueryBuilder
{
    use WhereTrait;

    /**
     * Set the table to update.
     *
     * @param string $table
     *
     * @return Update
     */
    public function table($table)
    {
        $this->statement['table'] = $table;
        return $this;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Update
     */
    public function set($column, $value)
    {
        $this->addToStatement('columns', [$column]);
        $this->addToStatement('values', [$value]);

        return $this;
    }

    /**
     * Add a set of columns and their values.
     *
     * @param array $columns Array of column name pointing to new value
     *
     * @return Update
     */
    public function columns(array $columns)
    {
        $this->addToStatement('columns', array_keys($columns));
        $this->addToStatement('values', array_values($columns));
        return $this;
    }


    /**
     * @see PDOStatement::rowCount()
     * @return int
     */
    public function affectedRows()
    {
        return $this->pdoStatement->rowCount();
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileQueryUpdate($statement);
    }
}