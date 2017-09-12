<?php

namespace MadeSimple\Database\Statement;

class CreateDb extends StatementBuilder
{
    /**
     * Set the name of the database to create.
     *
     * @param string $name
     *
     * @return CreateDb
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

        return $this->compiler->compileStatementCreateDb($statement);
    }
}