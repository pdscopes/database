<?php

namespace MadeSimple\Database\Statement;

class DropIndex extends StatementBuilder
{
    /**
     * Set the name of the index to be dropped.
     *
     * @param string $name
     *
     * @return DropIndex
     */
    public function index($name)
    {
        $this->statement['index'] = $name;
        return $this;
    }

    /**
     * Set the table to drop the index on.
     *
     * @param string $name
     *
     * @return DropIndex
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

        return $this->compiler->compileStatementDropIndex($statement);
    }
}