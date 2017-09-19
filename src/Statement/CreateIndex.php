<?php

namespace MadeSimple\Database\Statement;

class CreateIndex extends StatementBuilder
{

    /**
     * Set the name of the index to be created.
     *
     * @param string|array $columns
     * @param string       $name
     *
     * @return CreateIndex
     */
    public function index($columns, $name)
    {
        $this->statement['columns'] = (array) $columns;
        $this->statement['index']   = $name;
        return $this;
    }

    /**
     * Set the table for the index to be created on.
     *
     * @param string $name
     *
     * @return CreateIndex
     */
    public function table($name)
    {
        $this->statement['table'] = $name;
        return $this;
    }

    /**
     * Set if the index should be unique.
     *
     * @return CreateIndex
     */
    public function unique()
    {
        $this->statement['unique'] = true;
        return $this;
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementCreateIndex($statement);
    }
}