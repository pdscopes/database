<?php

namespace MadeSimple\Database\Statement;

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
     * @param \Closure $select function(Select){...}
     *
     * @see \MadeSimple\Database\Query\Select
     *
     * @return UpdateView
     */
    public function asSelect($select)
    {
        $this->statement['select'] = $select;
        return $this;
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementUpdateView($statement);
    }
}