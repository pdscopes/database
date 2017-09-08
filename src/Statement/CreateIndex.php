<?php

namespace MadeSimple\Database\Statement;

class CreateIndex extends StatementBuilder
{
    /**
     * Set if the index should be unique.
     *
     * @return CreateIndex
     */
    public function unique()
    {
        unset($this->statement['unique']);

        return $this->addToStatement('unique', true);
    }

    /**
     * Set the name of the index.
     *
     * @param string $name
     *
     * @return CreateIndex
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
     * @return CreateIndex
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

        return $this->compiler->compileStatementCreateIndex($statement);
    }
}