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
     * Alter the table be renaming.
     *
     * @param string $name
     *
     * @return AlterTable
     */
    public function renameTable($name)
    {
        $type = 'renameTable';
        $this->statement['alterations'][] = compact('type', 'name');
        return $this;
    }

    /**
     * Alter the table by settings the engine.
     *
     * @param string $engine e.g. InnoDB
     *
     * @return AlterTable
     */
    public function engine($engine)
    {
        $type = 'engine';
        $this->statement['alterations'][] = compact('type', 'engine');
        return $this;
    }

    /**
     * Set the engine for this statement.
     *
     * @param string $charset   e.g. utf8mb4
     * @param string $collation e.g. utf8mb4_general_ci
     *
     * @return AlterTable
     */
    public function charset($charset, $collation = null)
    {
        $type = 'charset';
        $this->statement['alterations'][] = compact('type', 'charset');
        if ($collation !== null) {
            $type = 'collate';
            $this->statement['alterations'][] = compact('type', 'collation');
        }
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
     * Alter the table by renaming a column.
     * Optionally change the datatype of the column.
     *
     * @param string        $currentName
     * @param string        $name
     * @return AlterTable
     */
    public function renameColumn($currentName, $name)
    {
        $type = 'renameColumn';
        $this->statement['alterations'][] = compact('type', 'currentName', 'name');

        return $this;
    }

    /**
     * Alter the table by changing a column's name.
     * Optionally change the datatype of the column.
     *
     * @param string        $currentName
     * @param string        $name
     * @param \Closure|null $closure
     * @return AlterTable|ColumnBuilder
     */
    public function changeColumn($currentName, $name, \Closure $closure = null)
    {
        $type = 'renameColumn';
        $columnBuilder = new ColumnBuilder($this->connection, $this->logger);
        $this->statement['alterations'][] = compact('type', 'currentName', 'name', 'columnBuilder');

        if ($closure !== null) {
            $closure($columnBuilder);
            return $this;
        } else {
            return $columnBuilder;
        }
    }

    /**
     * Alter the table by dropping a column.
     *
     * @param string $name
     *
     * @return AlterTable
     */
    public function dropColumn($name)
    {
        $type = 'dropColumn';
        $this->statement['alterations'][] = compact('type', 'name');
        return $this;
    }


    /**
     * Alter the table by adding a primary key constraint.
     *
     * @param string|array $columns
     * @param null|string  $name
     *
     * @return AlterTable
     */
    public function addPrimaryKey($columns, $name = null)
    {
        $type             = 'addPrimaryKey';
        $columns          = (array) $columns;
        $this->statement['alterations'][] = compact('type', 'name', 'columns');
        return $this;
    }

    /**
     * Alter the table by dropping a primary key constraint.
     *
     * @param string $name
     *
     * @return AlterTable
     */
    public function dropPrimaryKey($name = 'PRIMARY')
    {
        $type = 'dropPrimaryKey';
        $this->statement['alterations'][] = compact('type', 'name');
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
     * @param string $name
     *
     * @return AlterTable
     */
    public function dropForeignKey($name)
    {
        $type = 'dropForeignKey';
        $this->statement['alterations'][] = compact('type', 'name');
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
     * @param string $name
     *
     * @return AlterTable
     */
    public function dropUnique($name)
    {
        $type = 'dropUnique';
        $this->statement['alterations'][] = compact('type', 'name');
        return $this;
    }


    public  function buildSql(array $statement = null)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementAlterTable($statement);
    }
}