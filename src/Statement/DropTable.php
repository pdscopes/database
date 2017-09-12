<?php

namespace MadeSimple\Database\Statement;

class DropTable extends StatementBuilder
{
    /**
     * Set name of the table to be dropped.
     *
     * @param string $name
     *
     * @return DropTable
     */
    public function table($name)
    {
        $this->statement['table'] = $name;
        return $this;
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementDropTable($statement);
    }
}