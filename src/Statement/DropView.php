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
        unset($this->statement['view']);

        return $this->addToStatement('view', $name);
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementDropView($statement);
    }
}