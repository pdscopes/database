<?php

namespace MadeSimple\Database\Query;

class Delete extends QueryBuilder
{
    use WhereTrait;

    /**
     * Set the table to delete from.
     *
     * @param string      $table
     * @param null|string $alias
     *
     * @return Delete
     */
    public function from($table, $alias = null)
    {
        $this->statement['from'] = compact('table', 'alias');
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

        return $this->compiler->compileQueryDelete($statement);
    }
}