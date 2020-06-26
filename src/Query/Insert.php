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
     * Repeatedly execute the query on $chunkSize sets of rows.
     *
     * @param array $rows An array of values sets to be inserted
     * @param int $chunkSize Number of rows to add before executing a query
     * @see values()
     * @see query()
     */
    public function chunkedQuery(array $rows, int $chunkSize = 500)
    {
        // Flatten $rows into the values
        $values = array_merge(...$rows);
        // Calculate $chunkSize for values (rows * columns)
        $chunkSize *= count($this->statement['columns']);
        $this->pdo->beginTransaction();
        foreach (array_chunk($values, $chunkSize) as $chunkedValues) {
            $this->values($chunkedValues)->query();
        }
        $this->pdo->commit();
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


    public  function buildSql(array $statement = null)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileQueryInsert($statement);
    }


    protected function tidyAfterExecution()
    {
        unset($this->statement['values']);
    }
}