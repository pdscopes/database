<?php

namespace MadeSimple\Database\Statement;

class AlterTable extends StatementBuilder
{
    /**
     * Set the table to alter.
     *
     * @param string $name
     *
     * @return AlterTable
     */
    public function table($name)
    {
        $this->statement['table'] = $name;
        return $this;
    }

    /**
     * Alter the table by adding a column.
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
     * Alter the table by modifying a column.
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
     * Alter the table by altering a column.
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
     * Alter the table by dropping a column.
     *
     * @param string $column
     *
     * @return AlterTable
     */
    public function dropColumn($column)
    {
        $type = 'dropColumn';
        $this->statement['alterations'][] = compact('type', 'column');
        return $this;
    }


    /**
     * Alter the table by adding a foreign key constraint.
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
        $this->statement['alterations'][] = compact('type', 'name', 'columns', 'referenceTable', 'referenceColumns', 'onDelete', 'onUpdate');
        return $this;
    }

    /**
     * Alter the table by dropping a foreign key constraint.
     *
     * @param string $foreignKey
     *
     * @return AlterTable
     */
    public function dropForeignKey($foreignKey)
    {
        $type = 'dropForeignKey';
        $this->statement['alterations'][] = compact('type', 'foreignKey');
        return $this;
    }


    /**
     * Alter the table by adding a unique constraint.
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
        $this->statement['alterations'][] = compact('type', 'columns', 'name');
        return $this;
    }

    /**
     * Alter the table by dropping a unique constraint.
     *
     * @param string $unique
     *
     * @return AlterTable
     */
    public function dropUnique($unique)
    {
        $type = 'dropUnique';
        $this->statement['alterations'][] = compact('type', 'unique');
        return $this;
    }


    public  function buildSql(array $statement)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementAlterTable($statement);
    }
}