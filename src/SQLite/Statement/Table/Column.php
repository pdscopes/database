<?php

namespace MadeSimple\Database\SQLite\Statement\Table;

/**
 * Class Column
 *
 * @package MadeSimple\Database\SQLite\Statement\Table
 * @author  Peter Scopes
 * @see https://sqlite.org/lang_createtable.html
 */
class Column extends \MadeSimple\Database\Statement\Table\Column
{
    /**
     * @var bool
     */
    protected $notNull = false;

    /**
     * @var string
     */
    protected $constraint;

    /**
     * @return $this
     */
    function text()
    {
        $this->dataType = 'TEXT';
        return $this;
    }

    /**
     * @return $this
     */
    function numeric()
    {
        $this->dataType = 'NUMERIC';
        return $this;
    }

    /**
     * @return $this
     */
    function integer()
    {
        $this->dataType = 'INT';
        return $this;
    }

    /**
     * @return $this
     */
    function real()
    {
        $this->dataType = 'REAL';
        return $this;
    }

    /**
     * @return $this
     */
    function none()
    {
        $this->dataType = 'NONE';
        return $this;
    }


    /**
     * @return $this
     */
    function notNull()
    {
        $this->notNull = true;
        return $this;
    }


    /**
     * @return $this
     */
    function primaryKey()
    {
        $this->constraint = 'PRIMARY KEY';
        return $this;
    }
    function unique()
    {
        $this->constraint = 'UNIQUE';
        return $this;
    }


    /**
     * @return string
     */
    function __toString()
    {
        $definition = $this->connection->quoteClause($this->name) . ' ' . $this->dataType;

        if ($this->constraint) {
            $definition .= ' ' . $this->constraint;
        }
        if ($this->notNull) {
            $definition .= ' NOT NULL';
        }

        return $definition;
    }
}