<?php

namespace MadeSimple\Database\MySQL\Statement\Table;

/**
 * Class Column
 *
 * @package MadeSimple\Database\MySQL\Statement\Table
 * @author  Peter Scopes
 */
class Column extends \MadeSimple\Database\Statement\Table\Column
{

    /**
     * @var  bool
     */
    protected $null;

    /**
     * @var  mixed
     */
    protected $default;

    /**
     * @var  bool
     */
    protected $autoIncrement;

    /**
     * @var  string
     */
    protected $comment;


    /**
     * @param int $length
     *
     * @return $this
     */
    function bit($length)
    {
        $this->dataType = 'BIT(' . $length . ')';
        return $this;
    }

    /**
     * @param int  $length
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return $this
     */
    function tinyint($length, $unsigned = false, $zerofill = false)
    {
        $this->dataType = 'TINYINT(' . $length . ')' . ($unsigned ? ' UNSIGNED' :'') . ($zerofill ? ' ZEROFILL' : '');
        return $this;
    }

    /**
     * @param int  $length
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return $this
     */
    function smallint($length, $unsigned = false, $zerofill = false)
    {
        $this->dataType = 'SMALLINT(' . $length . ')' . ($unsigned ? ' UNSIGNED' :'') . ($zerofill ? ' ZEROFILL' : '');
        return $this;
    }

    /**
     * @param int  $length
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return $this
     */
    function mediumint($length, $unsigned = false, $zerofill = false)
    {
        $this->dataType = 'MEDIUMINT(' . $length . ')' . ($unsigned ? ' UNSIGNED' :'') . ($zerofill ? ' ZEROFILL' : '');
        return $this;
    }

    /**
     * @param int  $length
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return $this
     */
    function int($length, $unsigned = false, $zerofill = false)
    {
        $this->dataType = 'INT(' . $length . ')' . ($unsigned ? ' UNSIGNED' :'') . ($zerofill ? ' ZEROFILL' : '');
        return $this;
    }

    /**
     * @param int  $length
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return $this
     */
    function integer($length, $unsigned = false, $zerofill = false)
    {
        $this->dataType = 'INTEGER(' . $length . ')' . ($unsigned ? ' UNSIGNED' :'') . ($zerofill ? ' ZEROFILL' : '');
        return $this;
    }

    /**
     * @param int  $length
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return $this
     */
    function bigint($length, $unsigned = false, $zerofill = false)
    {
        $this->dataType = 'BIGINT(' . $length . ')' . ($unsigned ? ' UNSIGNED' :'') . ($zerofill ? ' ZEROFILL' : '');
        return $this;
    }

    /**
     * @param int  $length
     * @param int  $decimals
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return $this
     */
    function real($length, $decimals, $unsigned = false, $zerofill = false)
    {
        $this->dataType = 'REAL(' . $length . ',' . $decimals . ')' . ($unsigned ? ' UNSIGNED' :'') . ($zerofill ? ' ZEROFILL' : '');
        return $this;
    }

    /**
     * @param int  $length
     * @param int  $decimals
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return $this
     */
    function double($length, $decimals, $unsigned = false, $zerofill = false)
    {
        $this->dataType = 'REAL(' . $length . ',' . $decimals . ')' . ($unsigned ? ' UNSIGNED' :'') . ($zerofill ? ' ZEROFILL' : '');
        return $this;
    }

    /**
     * @param int  $length
     * @param int  $decimals
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return $this
     */
    function float($length, $decimals, $unsigned = false, $zerofill = false)
    {
        $this->dataType = 'REAL(' . $length . ',' . $decimals . ')' . ($unsigned ? ' UNSIGNED' :'') . ($zerofill ? ' ZEROFILL' : '');
        return $this;
    }

    /**
     * @param int  $length
     * @param int  $decimals
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return $this
     */
    function decimal($length, $decimals = null, $unsigned = false, $zerofill = false)
    {
        $this->dataType = 'REAL(' . $length . (null !== $decimals ? ',' . $decimals : '') . ')' . ($unsigned ? ' UNSIGNED' :'') . ($zerofill ? ' ZEROFILL' : '');
        return $this;
    }

    /**
     * @param int  $length
     * @param int  $decimals
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return $this
     */
    function numeric($length, $decimals = null, $unsigned = false, $zerofill = false)
    {
        $this->dataType = 'REAL(' . $length . (null !== $decimals ? ',' . $decimals : '') . ')' . ($unsigned ? ' UNSIGNED' :'') . ($zerofill ? ' ZEROFILL' : '');
        return $this;
    }

    /**
     * @return $this
     */
    function date()
    {
        $this->dataType = 'DATE';
        return $this;
    }

    /**
     * @param int $fsp
     *
     * @return $this
     */
    function time($fsp = null)
    {
        $this->dataType = 'TIME' . (null !== $fsp ? '(' . $fsp . ')' : '');
        return $this;
    }

    /**
     * @param int $fsp
     *
     * @return $this
     */
    function timestamp($fsp = null)
    {
        $this->dataType = 'TIMESTAMP' . (null !== $fsp ? '(' . $fsp . ')' : '');
        return $this;
    }

    /**
     * @param int $fsp
     *
     * @return $this
     */
    function datetime($fsp = null)
    {
        $this->dataType = 'DATETIME' . (null !== $fsp ? '(' . $fsp . ')' : '');
        return $this;
    }

    /**
     * @return $this
     */
    function year()
    {
        $this->dataType = 'YEAR';
        return $this;
    }

    /**
     * @param int    $length
     * @param bool   $binary
     * @param string $charset
     * @param string $collate
     *
     * @return $this
     */
    function char($length, $binary = false, $charset = null, $collate = null)
    {
        $this->dataType = 'CHAR(' . $length . ')' . ($binary ? ' BINARY' : '')
            . (null !== $charset ? ' CHARACTER SET ' . $charset : '')
            . (null !== $collate ? ' COLLATE ' . $collate : '');
        return $this;
    }

    /**
     * @param int    $length
     * @param bool   $binary
     * @param string $charset
     * @param string $collate
     *
     * @return $this
     */
    function varchar($length, $binary = false, $charset = null, $collate = null)
    {
        $this->dataType = 'VARCHAR(' . $length . ')' . ($binary ? ' BINARY' : '')
            . (null !== $charset ? ' CHARACTER SET ' . $charset : '')
            . (null !== $collate ? ' COLLATE ' . $collate : '');
        return $this;
    }

    /**
     * @param int $length
     *
     * @return $this
     */
    function binary($length)
    {
        $this->dataType = 'BINARY(' . $length. ')';
        return $this;
    }

    /**
     * @param int $length
     *
     * @return $this
     */
    function varbinary($length)
    {
        $this->dataType = 'VARBINARY(' . $length. ')';
        return $this;
    }

    /**
     * @return $this
     */
    function tinyblob()
    {
        $this->dataType = 'TINYBLOB';
        return $this;
    }

    /**
     * @return $this
     */
    function blob()
    {
        $this->dataType = 'BLOB';
        return $this;
    }

    /**
     * @return $this
     */
    function mediumblob()
    {
        $this->dataType = 'MEDIUMBLOB';
        return $this;
    }

    /**
     * @return $this
     */
    function longblob()
    {
        $this->dataType = 'LONGBLOB';
        return $this;
    }

    /**
     * @param bool   $binary
     * @param string $charset
     * @param string $collate
     *
     * @return $this
     */
    function tinytext($binary = false, $charset = null, $collate = null)
    {
        $this->dataType = 'TINYTEXT' . ($binary ? ' BINARY' :'')
            . (null !== $charset ? ' CHARACTER SET ' . $charset : '')
            . (null !== $collate ? ' COLLATE ' . $collate : '');
        return $this;
    }

    /**
     * @param bool   $binary
     * @param string $charset
     * @param string $collate
     *
     * @return $this
     */
    function text($binary = false, $charset = null, $collate = null)
    {
        $this->dataType = 'TEXT' . ($binary ? ' BINARY' :'')
            . (null !== $charset ? ' CHARACTER SET ' . $charset : '')
            . (null !== $collate ? ' COLLATE ' . $collate : '');
        return $this;
    }

    /**
     * @param bool   $binary
     * @param string $charset
     * @param string $collate
     *
     * @return $this
     */
    function mediumtext($binary = false, $charset = null, $collate = null)
    {
        $this->dataType = 'MEDIUMTEXT' . ($binary ? ' BINARY' :'')
            . (null !== $charset ? ' CHARACTER SET ' . $charset : '')
            . (null !== $collate ? ' COLLATE ' . $collate : '');
        return $this;
    }

    /**
     * @param bool   $binary
     * @param string $charset
     * @param string $collate
     *
     * @return $this
     */
    function longtext($binary = false, $charset = null, $collate = null)
    {
        $this->dataType = 'LONGTEXT' . ($binary ? ' BINARY' :'')
            . (null !== $charset ? ' CHARACTER SET ' . $charset : '')
            . (null !== $collate ? ' COLLATE ' . $collate : '');
        return $this;
    }

    /**
     * @param array  $values
     * @param string $charset
     * @param string $collate
     *
     * @return $this
     */
    function enum(array $values, $charset = null, $collate = null)
    {
        $this->dataType = 'ENUM(' . implode(',', $values) . ')'
            . (null !== $charset ? ' CHARACTER SET ' . $charset : '')
            . (null !== $collate ? ' COLLATE ' . $collate : '');
        return $this;
    }

    /**
     * @param array  $values
     * @param string $charset
     * @param string $collate
     *
     * @return $this
     */
    function set(array $values, $charset = null, $collate = null)
    {
        $this->dataType = 'SET(' . implode(',', $values) . ')'
            . (null !== $charset ? ' CHARACTER SET ' . $charset : '')
            . (null !== $collate ? ' COLLATE ' . $collate : '');
        return $this;
    }

    /**
     * @return $this
     */
    function json()
    {
        $this->dataType = 'JSON';
        return $this;
    }



    /**
     * @param bool $flag
     *
     * @return $this
     */
    function null($flag)
    {
        $this->null = $flag;
        return $this;
    }

    /**
     * @param string $value Provide your own quotes
     *
     * @return $this
     */
    function defaultValue($value)
    {
        $this->default = $value;
        return $this;
    }

    /**
     * @param bool $flag
     *
     * @return $this
     */
    function autoIncrement($flag)
    {
        $this->autoIncrement = $flag;
        return $this;
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
    function comment($comment)
    {
        $this->comment = $comment;
        return $this;
    }


    /**
     * @return string
     */
    function __toString()
    {
        $definition = $this->connection->quoteClause($this->name) . ' ' . $this->dataType;
        if (null !== $this->null) {
            $definition .= ($this->null ? ' NULL' : ' NOT NULL');
        }
        if (null !== $this->default) {
            $definition .= ' DEFAULT ' . $this->default;
        }
        if ($this->autoIncrement) {
            $definition .= ' AUTO_INCREMENT';
        }
        if (null !== $this->comment) {
            $definition .= ' COMMENT \'' . $this->comment . '\'';
        }

        return $definition;
    }
}