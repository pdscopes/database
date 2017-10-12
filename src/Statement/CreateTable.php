<?php

namespace MadeSimple\Database\Statement;

class CreateTable extends StatementBuilder
{
    /**
     * Set the name of the table to create.
     *
     * @param string $name
     *
     * @return CreateTable
     */
    public function table($name)
    {
        $this->statement['table'] = $name;
        return $this;
    }


    /**
     * Set this statement as a temporary table.
     *
     * @param bool $boolean
     *
     * @return CreateTable
     */
    public function temporary($boolean = true)
    {
        $this->statement['temporary'] = $boolean;

        return $this;
    }

    /**
     * Set this statement to be created if not exists.
     *
     * @param bool $boolean
     *
     * @return CreateTable
     */
    public function ifNotExists($boolean = true)
    {
        $this->statement['ifNotExists'] = $boolean;

        return $this;
    }

    /**
     * Set this statements primary keys.
     *
     * @param string|array|... $primaryKey
     *
     * @return CreateTable
     */
    public function primaryKey($columns)
    {
        $type    = 'primaryKey';
        $columns = is_array($columns) ? $columns : func_get_args();
        $this->statement['constraints'][] = compact('type', 'columns');

        return $this;
    }

    /**
     * Add an index of column(s) to the statement.
     *
     * @param string|array $columns
     * @param null|string  $name
     *
     * @return CreateTable
     */
    public function index($columns, $name = null)
    {
        $type    = 'index';
        $columns = (array) $columns;
        $this->statement['constraints'][] = compact('type', 'name', 'columns');

        return $this;
    }

    /**
     * Add a unique index of column(s) to the statement.
     *
     * @param string|array $columns
     * @param null|string  $name
     *
     * @return CreateTable
     */
    public function unique($columns, $name = null)
    {
        $type    = 'unique';
        $columns = (array) $columns;
        $this->statement['constraints'][] = compact('type', 'name', 'columns');

        return $this;
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
     * @return CreateTable
     */
    public function foreignKey($columns, $referenceTable, $referenceColumns, $onDelete = null, $onUpdate = null, $name = null)
    {
        $type             = 'foreignKey';
        $columns          = (array) $columns;
        $referenceColumns = (array) $referenceColumns;
        $this->statement['constraints'][] = compact('type', 'name', 'columns', 'referenceTable', 'referenceColumns', 'onDelete', 'onUpdate');

        return $this;
    }

    /**
     * Set the engine for this statement.
     *
     * @param string $engine e.g. InnoDB
     *
     * @return CreateTable
     */
    public function engine($engine)
    {
        $this->statement['engine'] = $engine;

        return $this;
    }

    /**
     * Set the engine for this statement.
     *
     * @param string $charset   e.g. utf8mb4
     * @param string $collation e.g. utf8mb4_general_ci
     *
     * @return CreateTable
     */
    public function charset($charset, $collation = null)
    {
        $this->statement['charset'] = $charset;
        $this->statement['collate'] = $collation;

        return $this;
    }

    /**
     * Set the comment for this statement.
     *
     * @param string $comment
     *
     * @return CreateTable
     */
    public function comment($comment)
    {
        $this->statement['comment'] = $comment;

        return $this;
    }

    /**
     * Add a column to the statement.
     *
     * @param string        $name
     * @param null|\Closure $closure function (ColumnBuilder) {...}
     * @see ColumnBuilder
     *
     * @return CreateTable|ColumnBuilder
     */
    public function column($name, \Closure $closure = null)
    {
        $columnBuilder = new ColumnBuilder($this->connection, $this->logger);
        $this->statement['columns'][] = compact('name', 'columnBuilder');

        if ($closure !== null) {
            $closure($columnBuilder);
            return $this;
        } else {
            return $columnBuilder;
        }
    }




    public  function buildSql(array $statement = null)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileStatementCreateTable($statement);
    }
}