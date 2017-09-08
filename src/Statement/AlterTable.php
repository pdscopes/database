<?php

namespace MadeSimple\Database\Statement;

class AlterTable extends StatementBuilder
{
    /**
     * Set the table to update.
     *
     * @param string      $table
     * @param null|string $alias
     *
     * @return AlterTable
     */
    public function table($table, $alias = null)
    {
        unset($this->statement['table']);

        return $this->addToStatement('table', null === $alias ? [$table] : [$alias => $table]);
    }

    // ALTER TABLE table_name
    // ADD column_name dataType;
    public function add($column, $dataType, $constraints)
    {
        $type = 'add';

        return $this->addToStatement('alterations', compact('type', 'column', 'dataType', 'constraints'));
    }

    // ALTER TABLE table_name
    // DROP COLUMN column_name;
    public function drop($column)
    {
        $type = 'drop';

        return $this->addToStatement('alterations', compact('type', 'column'));
    }

    public function modify($column, $dataType, $constraints)
    {
        $type = 'modify';

        return $this->addToStatement('alterations', compact('type', 'column', 'dataType', 'constraints'));
    }

    public function alter($column, $dataType, $constraints)
    {
        return $this->modify($column, $dataType, $constraints);
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementAlterTable($statement);
    }
}