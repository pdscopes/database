<?php

namespace MadeSimple\Database\Statement;

class DropIndex extends StatementBuilder
{
    /**
     * Set the table to update.
     *
     * @param string $name
     *
     * @return DropIndex
     */
    public function name($name)
    {
        unset($this->statement['name']);

        return $this->addToStatement('name', $name);
    }

    /**
     * Set the table to update.
     *
     * @param string      $table
     * @param null|string $alias
     *
     * @return DropIndex
     */
    public function table($table, $alias = null)
    {
        unset($this->statement['table']);

        return $this->addToStatement('table', null === $alias ? [$table] : [$alias => $table]);
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementDropIndex($statement);
    }
}