<?php

namespace MadeSimple\Database\Statement;

class DropDb extends StatementBuilder
{
    /**
     * Set the name of the database to be dropped.
     *
     * @param string $name
     *
     * @return DropDb
     */
    public function database($name)
    {
        $this->statement['database'] = $name;
        return $this;
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementDropDb($statement);
    }
}