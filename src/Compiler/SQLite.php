<?php

namespace MadeSimple\Database\Compiler;

use MadeSimple\Database\Compiler;
use MadeSimple\Database\Statement\ColumnBuilder;
use Psr\Log\LoggerInterface;

class SQLite extends Compiler
{
    public function __construct(LoggerInterface $logger = null)
    {
        parent::__construct('"', $logger);
    }

    public function compileStatementCreateTable(array $statement)
    {
        // IF NOT EXISTS
//        $ifNotExists = isset($statement['ifNotExists']) ? 'IF NOT EXISTS' : '';

        // Table
        $table = $this->sanitise($statement['table']);

        // Columns
        $columns = $this->concatenateSql(array_map([$this, 'compileStatementColumn'], $statement['columns'] ?? []), ',');
        // Constraints
        $constraints = $this->concatenateSql(array_map([$this, 'compileStatementConstraint'], $statement['constraints'] ?? []), ',');
        $constraints = !empty($constraints) ? ',' . $constraints : '';


        return [$this->concatenateSql([
            'CREATE TABLE',
//            $ifNotExists,
            $table,
            '(',
            $columns,
            $constraints,
            ')'
        ]), []];
    }

    public function compileStatementAlterTable(array $statement)
    {
        // Table
        $table = $this->sanitise($statement['table']);
        // Alterations
        $alterations = $this->compileStatementAlterations($statement);

        return [$this->concatenateSql([
            'ALTER TABLE',
            $table,
            $alterations
        ]), []];
    }

    public function compileStatementDropIndex(array $statement)
    {
        return ['', []];
    }




    protected function compileStatementColumn(array $columnArray)
    {
        if (!isset($columnArray['columnBuilder'])) {
            return $this->sanitise($columnArray['name']);
        }
        $columnArray  += ['datatype' => ['type' => '']];

        /** @var ColumnBuilder $columnBuilder */
        $columnBuilder = $columnArray['columnBuilder'];
        $statement     = $columnBuilder->toArray();

        // Name
        $name = $this->sanitise($columnArray['name']);
        // Data Type
        $datatype = '';
        if (isset($statement['datatype'])) {
            switch ($statement['datatype']['type']) {
                case 'tinyInteger':
                case 'smallInteger':
                case 'mediumInteger':
                case 'integer':
                case 'bigInteger':
                    $datatype = 'INTEGER';
                    break;
                case 'double':
                case 'float':
                case 'decimal':
                    $datatype = 'REAL';
                    break;
                case 'date':
                case 'time':
                case 'timestamp':
                case 'dateTime':
                case 'char':
                case 'varchar':
                case 'binary':
                case 'tinyBlob':
                case 'blob':
                case 'mediumBlob':
                case 'longBlob':
                case 'tinyText':
                case 'text':
                case 'mediumText':
                case 'longText':
                case 'enum':
                case 'json':
                    $datatype = 'TEXT';
                    break;
            }
        }
        // Use Current
        $useCurrent = isset($statement['useCurrent']) ? "DEFAULT CURRENT_TIMESTAMP" : '';
        // Null
        $null = isset($statement['null']) ? ($statement['null'] ? 'NULL': 'NOT NULL') : '';
        // Primary Key
        $primaryKey = isset($statement['primaryKey']) ? 'PRIMARY KEY' : '';
        // Unique
        $unique = isset($statement['unique']) ? 'UNIQUE' : '';


        return $this->concatenateSql([
            $name,
            $datatype,
            $useCurrent,
            $null,
            $primaryKey,
            $unique,
        ]);
    }

    protected function compileStatementConstraint($constraintArray)
    {
        $columns = implode(',', array_map([$this, 'sanitise'], $constraintArray['columns']));
        switch ($constraintArray['type']) {
            case 'primaryKey':
                return 'PRIMARY KEY (' . $columns . ')';

            case 'index':
                return 'INDEX ' . ($constraintArray['name'] ?? '') . '(' . $columns . ')';

            case 'unique':
                return 'UNIQUE' . ($constraintArray['name'] ?? '') . '(' . $columns . ')';

            case 'foreignKey':
                // SQLite does not support foreign keys
        }

        return '';
    }

    protected function compileStatementAlterations(array $statement)
    {
        $sql = '';
        foreach ($statement['alterations'] ?? [] as $alteration) {
            $sql .= ', ';
            switch ($alteration['type']) {
                case 'renameTable':
                    $sql .= 'RENAME TO ' . $this->sanitise($alteration['name']);
                    break;

                case 'addColumn':
                    $sql .= 'ADD ' . $this->compileStatementColumn($alteration);
                    break;
                case 'dropColumn':
                    $sql .= 'DROP COLUMN ' . $this->sanitise($alteration['name']);
                    break;
                case 'modifyColumn':
                    $sql .= 'MODIFY COLUMN ' . $this->compileStatementColumn($alteration);
                    break;

                case 'renameColumn':
                    $sql .= 'CHANGE ' . $this->sanitise($alteration['currentName']) . ' ' . $this->compileStatementColumn($alteration);
                    break;
            }
        }

        return trim(substr($sql, 1));
    }
}