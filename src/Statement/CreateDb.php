<?php

namespace MadeSimple\Database\Statement;

class CreateDb extends StatementBuilder
{
    /**
     * Set the table to update.
     *
     * @param string      $table
     * @param null|string $alias
     *
     * @return CreateDb
     */
    public function table($table, $alias = null)
    {
        unset($this->statement['database']);

        return $this->addToStatement('database', null === $alias ? [$table] : [$alias => $table]);
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementCreateDb($statement);
    }
}