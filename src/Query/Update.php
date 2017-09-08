<?php

namespace MadeSimple\Database\Query;

class Update extends QueryBuilder
{
    use WhereTrait;

    /**
     * Set the table to update.
     *
     * @param string      $table
     * @param null|string $alias
     *
     * @return Update
     */
    public function table($table, $alias = null)
    {
        unset($this->statement['table']);

        return $this->addToStatement('table', null === $alias ? [$table] : [$alias => $table]);
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
     * Set the columns that will have values assigned.
     *
     * @param string|array|... $columns
     *
     * @return Update
     */
    public function columns($columns)
    {
        unset($this->statement['columns']);

        return $this->addToStatement('columns', is_array($columns) ? array_values($columns) : func_get_args());
    }

    /**
     * Set the values that will be inserted.
     *
     * @param string|array|... $values
     *
     * @return Update
     */
    public function values($values)
    {
        unset($this->statement['values']);

        return $this->addToStatement('values', is_array($values) ? array_values($values) : func_get_args());
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