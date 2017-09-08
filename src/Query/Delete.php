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
        unset($this->statement['from']);

        return $this->addToStatement('from', null === $alias ? [$table] : [$alias => $table]);
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

        return $this->compiler->compileQueryDelete($statement);
    }
}