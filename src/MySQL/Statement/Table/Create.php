<?php

namespace MadeSimple\Database\MySQL\Statement\Table;

/**
 * Class Create
 *
 * @package MadeSimple\Database\MySQL\Statement\Table
 * @author  Peter Scopes
 *
 * @see https://dev.mysql.com/doc/refman/5.7/en/create-table.html
 */
class Create extends \MadeSimple\Database\Statement\Table\Create
{
    /**
     * @var  bool
     */
    protected $temporary;

    /**
     * @var  bool
     */
    protected $ifNotExists;

    /**
     * @var  array
     */
    protected $constraints = [];

    /**
     * @var  array
     */
    protected $options = [];

    /**
     * @param bool $flag
     */
    function temporary($flag)
    {
        $this->temporary = $flag;
    }

    /**
     * @param bool $flag
     */
    function ifNotExists($flag)
    {
        $this->ifNotExists = $flag;
    }

    /**
     * @param string $name
     */
    function name($name)
    {
        $this->name = $name;

    }

    /**
     * @param string $name
     *
     * @return Column
     */
    function column($name)
    {
        $column = new Column($this->connection, $name);
        $this->columns[$name] = $column;

        return $column;
    }




    function primaryKey($indexColNames)
    {
        $this->constraints['primaryKey'] = 'PRIMARY KEY (' . implode(',', (array)$indexColNames) . ')';
        return $this;
    }
    function index($indexColNames, $name = null)
    {
        $this->constraints[] = 'INDEX ' . (null !== $name ? $name : '') . ' (' . implode(',', (array)$indexColNames) . ')';
        return $this;
    }
    function unique($indexColNames, $name = null)
    {
        $this->constraints[] = 'UNIQUE ' . (null !== $name ? $name : '') . ' (' . implode(',', (array)$indexColNames) . ')';
        return $this;
    }
    function foreignKey($indexColNames, $referenceTable, $referenceColNames, $on = null, $name = null)
    {
        $this->constraints[] = 'FOREIGN KEY ' . (null !== $name ? $name : '') . ' (' . implode(',', (array)$indexColNames) . ')'
            .' REFERENCES ' . $referenceTable . '(' . implode(',', (array) $referenceColNames) . ')'
            . (null !== $on ? ' ON ' . strtoupper($on) : '');
        return $this;
    }



    function engine($engine)
    {
        $this->options['engine'] = 'ENGINE = ' . $engine;
    }
    function charset($charset)
    {
        $this->options['charset'] = 'CHARACTER SET = ' . $charset;
    }
    function collate($collate)
    {
        $this->options['collate'] = 'COLLATE = ' . $collate;
    }
    function comment($comment)
    {
        $this->options['comment'] = 'COMMENT = ' . $comment;
    }

    /**
     * @return string
     * @see __toString
     */
    public function toSql()
    {
        $sql = 'CREATE '
            . ($this->temporary ? 'TEMPORARY ' : '') . 'TABLE '
            . ($this->ifNotExists ? 'IF NOT EXISTS ' : '')
            . $this->connection->quoteClause($this->name);

        $columns = [];
        foreach ($this->columns as $column) {
            $columns[] = (string) $column;
        }
        foreach ($this->constraints as $constraint) {
            $columns[] = $this->connection->quoteClause($constraint);
        }
        $sql .= '(' . implode(',', $columns) . ') ';

        $sql .= implode(',', $this->options);

        return $sql;
    }
}