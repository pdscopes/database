<?php

namespace MadeSimple\Database\Statement;

class CreateView extends StatementBuilder
{
    /**
     * Set the name of the view.
     *
     * @param string $name
     *
     * @return CreateView
     */
    public function name($name)
    {
        unset($this->statement['name']);

        return $this->addToStatement('name', $name);
    }

    /**
     * @param \Closure $select function(Select){...}
     *
     * @see \MadeSimple\Database\Query\Select
     *
     * @return CreateView
     */
    public function asSelect($select)
    {
        unset($this->statement['select']);

        return $this->addToStatement('select', $select);
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementCreateView($statement);
    }
}