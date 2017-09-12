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

    /**
     * Add a column to the statement.
     *
     * @param string        $name
     * @param null|\Closure $closure function (ColumnBuilder) {...}
     * @see ColumnBuilder
     *
     * @return AlterTable|ColumnBuilder
     */
    public function addColumn($name, \Closure $closure = null)
    {
        $type = 'addColumn';
        $columnBuilder = new ColumnBuilder($this->connection, $this->logger);
        $this->statement['alterations'][] = compact('type', 'name', 'columnBuilder');

        if ($closure !== null) {
            $closure($columnBuilder);
            return $this;
        } else {
            return $columnBuilder;
        }
    }

    /**
     * Modify a column to the statement.
     *
     * @param string        $name
     * @param null|\Closure $closure function (ColumnBuilder) {...}
     * @see ColumnBuilder
     *
     * @return AlterTable|ColumnBuilder
     */
    public function modifyColumn($name, \Closure $closure = null)
    {
        $type = 'modifyColumn';
        $columnBuilder = new ColumnBuilder($this->connection, $this->logger);
        $this->statement['alterations'][] = compact('type', 'name', 'columnBuilder');

        if ($closure !== null) {
            $closure($columnBuilder);
            return $this;
        } else {
            return $columnBuilder;
        }
    }

    /**
     * Alter a column to the statement.
     *
     * @param string        $name
     * @param null|\Closure $closure function (ColumnBuilder) {...}
     *
     * @see ColumnBuilder
     * @see AlterTable::modifyColumn()
     *
     * @return AlterTable|ColumnBuilder
     */
    public function alterColumn($name, \Closure $closure = null)
    {
        return $this->modifyColumn($name, $closure);
    }

    /**
     * Drop a column.
     *
     * @param string $column
     *
     * @return AlterTable
     */
    public function dropColumn($column)
    {
        $type = 'dropColumn';

        return $this->addToStatement('alterations', compact('type', 'column'));
    }


    /**
     * Add a foreign key index to the statement.
     *
     * @param string|array $columns
     * @param string       $referenceTable
     * @param string|array $referenceColumns
     * @param null|string  $onDelete
     * @param null|string  $onUpdate
     * @param null|string  $name
     *
     * @return AlterTable
     */
    public function addForeignKey($columns, $referenceTable, $referenceColumns, $onDelete = null, $onUpdate = null, $name = null)
    {
        $type             = 'addForeignKey';
        $columns          = (array) $columns;
        $referenceColumns = (array) $referenceColumns;
        $this->addToStatement('alterations', compact('type', 'name', 'columns', 'referenceTable', 'referenceColumns', 'onDelete', 'onUpdate'));

        return $this;
    }

    /**
     * Drop a foreign key constraint.
     *
     * @param string $foreignKey
     *
     * @return AlterTable
     */
    public function dropForeignKey($foreignKey)
    {
        $type = 'dropForeignKey';

        return $this->addToStatement('alterations', compact('type', 'foreignKey'));
    }


    /**
     * Add a unique constraint.
     *
     * @param string|array $columns
     * @param null|string  $name
     *
     * @return AlterTable
     */
    public function addUnique($columns, $name = null)
    {
        $type    = 'addUnique';
        $columns = (array) $columns;

        return $this->addToStatement('alterations', compact('type', 'columns', 'name'));
    }

    /**
     * Drop a unique constraint.
     *
     * @param string $unique
     *
     * @return AlterTable
     */
    public function dropUnique($unique)
    {
        $type = 'dropUnique';
        return $this->addToStatement('alterations', compact('type', 'unique'));
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementAlterTable($statement);
    }
}