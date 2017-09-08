<?php

namespace MadeSimple\Database\Statement;

class DropTable extends StatementBuilder
{
    /**
     * Set the table to update.
     *
     * @param string      $table
     * @param null|string $alias
     *
     * @return DropTable
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

        return $this->compiler->compileStatementDropTable($statement);
    }
}