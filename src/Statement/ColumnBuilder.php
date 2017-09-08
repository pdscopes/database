<?php

namespace MadeSimple\Database\Statement;

use MadeSimple\Database\Builder;

class ColumnBuilder extends Builder
{
    /**
     * @return array
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * Tiny integer equivalent for the database.
     *
     * @param int  $length
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return ColumnBuilder
     */
    public function tinyInteger($length, $unsigned = false, $zerofill = false)
    {
        $type = 'tinyInteger';
        $this->statement['dataType'] = compact('type', 'length', 'unsigned', 'zerofill');

        return $this;
    }

    /**
     * Small integer equivalent for the database.
     * @param int  $length
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return ColumnBuilder
     */
    public function smallInteger($length, $unsigned = false, $zerofill = false)
    {
        $type = 'smallInteger';
        $this->statement['dataType'] = compact('type', 'length', 'unsigned', 'zerofill');

        return $this;
    }

    /**
     * Medium integer equivalent for the database.
     *
     * @param int  $length
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return ColumnBuilder
     */
    public function mediumInteger($length, $unsigned = false, $zerofill = false)
    {
        $type = 'mediumInteger';
        $this->statement['dataType'] = compact('type', 'length','unsigned', 'zerofill');

        return $this;
    }

    /**
     * Integer equivalent for the database.
     *
     * @param int  $length
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return ColumnBuilder
     */
    public function integer($length, $unsigned = false, $zerofill = false)
    {
        $type = 'integer';
        $this->statement['dataType'] = compact('type', 'length', 'unsigned', 'zerofill');

        return $this;
    }

    /**
     * Big integer equivalent for the database.
     *
     * @param int  $length
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return ColumnBuilder
     */
    public function bigInteger($length, $unsigned = false, $zerofill = false)
    {
        $type = 'bigInteger';
        $this->statement['dataType'] = compact('type', 'length', 'unsigned', 'zerofill');

        return $this;
    }

    /**
     * Double equivalent for the database.
     *
     * @param int  $length
     * @param int  $decimals
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return ColumnBuilder
     */
    public function double($length, $decimals, $unsigned = false, $zerofill = false)
    {
        $type = 'double';
        $this->statement['dataType'] = compact('type', 'length', 'decimals', 'unsigned', 'zerofill');

        return $this;
    }

    /**
     * Float equivalent for the database.
     *
     * @param int  $length
     * @param int  $decimals
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return ColumnBuilder
     */
    public function float($length, $decimals, $unsigned = false, $zerofill = false)
    {
        $type = 'float';
        $this->statement['dataType'] = compact('type', 'length', 'decimals', 'unsigned', 'zerofill');

        return $this;
    }

    /**
     * Decimal equivalent for the database.
     *
     * @param int  $length
     * @param int  $decimals
     * @param bool $unsigned
     * @param bool $zerofill
     *
     * @return ColumnBuilder
     */
    public function decimal($length, $decimals = null, $unsigned = false, $zerofill = false)
    {
        $type = 'decimal';
        $this->statement['dataType'] = compact('type', 'length', 'decimals', 'unsigned', 'zerofill');

        return $this;
    }

    /**
     * Date equivalent for the database.
     *
     * @return ColumnBuilder
     */
    public function date()
    {
        $type = 'date';
        $this->statement['dataType'] = compact('type');

        return $this;
    }

    /**
     * Time equivalent for the database.
     *
     * @param int $fsp
     *
     * @return ColumnBuilder
     */
    public function time($fsp = null)
    {
        $type = 'time';
        $this->statement['dataType'] = compact('type', 'fsp');

        return $this;
    }

    /**
     * Timestamp equivalent for the database.
     *
     * @param int $fsp
     *
     * @return ColumnBuilder
     */
    public function timestamp($fsp = null)
    {
        $type = 'timestamp';
        $this->statement['dataType'] = compact('type', 'fsp');

        return $this;
    }

    /**
     * Date time equivalent for the database.
     *
     * @param int $fsp
     *
     * @return ColumnBuilder
     */
    public function dateTime($fsp = null)
    {
        $type = 'dateTime';
        $this->statement['dataType'] = compact('type', 'fsp');

        return $this;
    }

    /**
     * Char equivalent for the database.
     *
     * @param int    $length
     * @param bool   $binary
     * @param string $charset
     * @param string $collate
     *
     * @return ColumnBuilder
     */
    public function char($length, $binary = false, $charset = null, $collate = null)
    {
        $type = 'char';
        $this->statement['dataType'] = compact('type', 'length', 'binary', 'charset', 'collate');

        return $this;
    }

    /**
     * Varchar equivalent for the database.
     *
     * @param int    $length
     * @param bool   $binary
     * @param string $charset
     * @param string $collate
     *
     * @return ColumnBuilder
     */
    public function varchar($length, $binary = false, $charset = null, $collate = null)
    {
        $type = 'varchar';
        $this->statement['dataType'] = compact('type', 'length', 'binary', 'charset', 'collate');

        return $this;
    }

    /**
     * Binary equivalent for the database.
     *
     * @param int $length
     *
     * @return ColumnBuilder
     */
    public function binary($length)
    {
        $type = 'binary';
        $this->statement['dataType'] = compact('type', 'length');

        return $this;
    }

    /**
     * Tiny blob equivalent for the database.
     *
     * @return ColumnBuilder
     */
    public function tinyBlob()
    {
        $type = 'tinyBlob';
        $this->statement['dataType'] = compact('type');

        return $this;
    }

    /**
     * Blob equivalent for the database.
     *
     * @return ColumnBuilder
     */
    public function blob()
    {
        $type = 'blob';
        $this->statement['dataType'] = compact('type');

        return $this;
    }

    /**
     * Medium blob equivalent for the database.
     *
     * @return ColumnBuilder
     */
    public function mediumBlob()
    {
        $type = 'mediumBlob';
        $this->statement['dataType'] = compact('type');

        return $this;
    }

    /**
     * Long blob equivalent for the database.
     *
     * @return ColumnBuilder
     */
    public function longBlob()
    {
        $type = 'longBlob';
        $this->statement['dataType'] = compact('type');

        return $this;
    }

    /**
     * Tiny text equivalent for the database.
     *
     * @param bool   $binary
     * @param string $charset
     * @param string $collate
     *
     * @return ColumnBuilder
     */
    public function tinyText($binary = false, $charset = null, $collate = null)
    {
        $type = 'tinyText';
        $this->statement['dataType'] = compact('type', 'binary', 'charset', 'collate');

        return $this;
    }

    /**
     * Text equivalent for the database.
     *
     * @param bool   $binary
     * @param string $charset
     * @param string $collate
     *
     * @return ColumnBuilder
     */
    public function text($binary = false, $charset = null, $collate = null)
    {
        $type = 'text';
        $this->statement['dataType'] = compact('type', 'length', 'binary', 'charset', 'collate');

        return $this;
    }

    /**
     * Medium text equivalent for the database.
     *
     * @param bool   $binary
     * @param string $charset
     * @param string $collate
     *
     * @return ColumnBuilder
     */
    public function mediumText($binary = false, $charset = null, $collate = null)
    {
        $type = 'mediumText';
        $this->statement['dataType'] = compact('type', 'length', 'binary', 'charset', 'collate');

        return $this;
    }

    /**
     * Long text equivalent for the database.
     *
     * @param bool   $binary
     * @param string $charset
     * @param string $collate
     *
     * @return ColumnBuilder
     */
    public function longText($binary = false, $charset = null, $collate = null)
    {
        $type = 'longText';
        $this->statement['dataType'] = compact('type', 'length', 'binary', 'charset', 'collate');

        return $this;
    }

    /**
     * Enum equivalent for the database.
     *
     * @param array  $values
     * @param string $charset
     * @param string $collate
     *
     * @return ColumnBuilder
     */
    public function enum(array $values, $charset = null, $collate = null)
    {
        $type = 'enum';
        $this->statement['dataType'] = compact('type', 'length', 'values', 'charset', 'collate');

        return $this;
    }

    /**
     * JSON equivalent for the database.
     *
     * @return ColumnBuilder
     */
    public function json()
    {
        $type = 'json';
        $this->statement['dataType'] = compact('type');

        return $this;
    }




    /**
     * Set whether this column can be null.
     *
     * @param bool $boolean
     *
     * @return ColumnBuilder
     */
    public function null($boolean = true)
    {
        $this->statement['null'] = $boolean;
        return $this;
    }

    /**
     * Set that this column is not null.
     *
     * @return ColumnBuilder
     */
    public function notNull()
    {
        return $this->null(false);
    }

    /**
     * Set the default value of the column.
     *
     * @param string $value
     *
     * @return ColumnBuilder
     */
    public function default($value)
    {
        $this->statement['default'] = (string) $value;
        unset($this->statement['useCurrent']);
        return $this;
    }

    /**
     * Set the default value as the current timestamp (date/date time/timestamp only).
     *
     * @return ColumnBuilder
     */
    public function useCurrent()
    {
        $this->statement['useCurrent'] = true;
        unset($this->statement['default']);
        return $this;
    }

    /**
     * Set the default value as null of the column.
     *
     * @return ColumnBuilder
     */
    public function defaultNull()
    {
        return $this->default(null);
    }

    /**
     * Set this column to automatically increment (MySQL only).
     *
     * @return ColumnBuilder
     */
    public function autoIncrement()
    {
        $this->statement['autoIncrement'] = true;
        return $this;
    }

    /**
     * Set the comment of the column.
     *
     * @param string $comment
     *
     * @return ColumnBuilder
     */
    public function comment($comment)
    {
        $this->statement['comment'] = $comment;
        return $this;
    }

    /**
     * Set the column as a primary key.
     *
     * @return ColumnBuilder
     */
    public function primaryKey()
    {
        $this->statement['primaryKey'] = true;
        return $this;
    }

    /**
     * Set the column as unique.
     *
     * @return ColumnBuilder
     */
    public function unique()
    {
        $this->statement['unique'] = true;
        return $this;
    }

    /**
     * Place the column as the "first" column in the table (MySQL only).
     *
     * @return ColumnBuilder
     */
    public function first()
    {
        $this->statement['first'] = true;
        unset($this->statement['after']);
        return $this;
    }

    /**
     * Place the column "after" another column (MySQL only).
     *
     * @param string $column
     *
     * @return ColumnBuilder
     */
    public function after($column)
    {
        $this->statement['after'] = $column;
        unset($this->statement['first']);
        return $this;
    }
}