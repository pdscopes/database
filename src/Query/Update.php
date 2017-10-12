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
     * @param string|array $column
     * @param mixed|null   $value
     *
     * @return Update
     */
    public function set($column, $value = null)
    {
        if (!is_array($column)) {
            $column = [$column => $value];
        }
        $this->addToStatement('set', $column);

        return $this;
    }

    /**
     * @param string|array $column
     * @param mixed|null   $value
     * @return Update
     */
    public function setRaw($column, $value = null)
    {
        if (!is_array($column)) {
            $column = [$column => $value];
        }
        $column = array_map([Raw::class, 'create'], $column);
        $this->addToStatement('set', $column);

        return $this;
    }

    /**
     * @param string|array $column
     * @param mixed|null   $value
     * @return Update
     */
    public function setColumn($column, $value = null)
    {
        if (!is_array($column)) {
            $column = [$column => $value];
        }
        $column = array_map([Column::class, 'create'], $column);
        $this->addToStatement('set', $column);

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


    public  function buildSql(array $statement = null)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileQueryUpdate($statement);
    }


    protected function tidyAfterExecution()
    {
        unset($this->statement['set']);
    }
}