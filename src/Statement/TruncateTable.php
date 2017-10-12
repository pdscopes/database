<?php

namespace MadeSimple\Database\Statement;

class TruncateTable extends StatementBuilder
{
    /**
     * Set name of the table to be truncated.
     *
     * @param string $name
     *
     * @return TruncateTable
     */
    public function table($name)
    {
        $this->statement['table'] = $name;
        return $this;
    }


    public  function buildSql(array $statement = null)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementTruncateTable($statement);
    }
}