<?php

namespace MadeSimple\Database\Statement;

class DropView extends StatementBuilder
{
    /**
     * Set the name of the view.
     *
     * @param string $name
     *
     * @return DropView
     */
    public function view($name)
    {
        $this->statement['view'] = $name;
        return $this;
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementDropView($statement);
    }
}