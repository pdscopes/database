<?php

namespace MadeSimple\Database\Statement;

use MadeSimple\Database\Migration\SeedInterface;

class CreateView extends StatementBuilder
{
    /**
     * Set the name of the view to be created.
     *
     * @param string $name
     *
     * @return CreateView
     */
    public function view($name)
    {
        $this->statement['view'] = $name;
        return $this;
    }

    /**
     * @param callable $select function(Select){...}
     *
     * @see \MadeSimple\Database\Query\Select
     *
     * @return CreateView
     */
    public function asSelect(callable $select)
    {
        $this->statement['select'] = $select;
        return $this;
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementCreateView($statement);
    }
}