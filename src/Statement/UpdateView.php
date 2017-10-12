<?php

namespace MadeSimple\Database\Statement;

use MadeSimple\Database\Query\Select;

class UpdateView extends StatementBuilder
{
    /**
     * Set the name of the view to be updated.
     *
     * @param string $name
     *
     * @return UpdateView
     */
    public function view($name)
    {
        $this->statement['view'] = $name;
        return $this;
    }

    /**
     * @param callable|Select $select function(Select){...}
     *
     * @see \MadeSimple\Database\Query\Select
     *
     * @return UpdateView
     */
    public function asSelect($select)
    {
        if (is_callable($select)) {
            $callable = $select;
            $select   = new Select($this->connection, $this->logger);
            call_user_func($callable, $select);
        }

        $this->statement['select'] = $select->statement;
        return $this;
    }


    public  function buildSql(array $statement = null)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementUpdateView($statement);
    }
}