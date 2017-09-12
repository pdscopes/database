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

    /**
     * @InheritDoc
     */
    public function compileStatementCreateTable(array $statement)
    {
        // IF NOT EXISTS
//        $ifNotExists = isset($statement['ifNotExists']) ? 'IF NOT EXISTS' : '';

        // Table
        $table = $this->compileSanitiseArray($statement['table'] ?? []);

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

    /**
     * @InheritDoc
     */
    public function compileStatementAlterTable(array $statement)
    {
        // Table
        $table = $this->compileSanitiseArray($statement['table']);
        // Alterations
        $alterations = $this->compileStatementAlterations($statement);

        return [$this->concatenateSql([
            'ALTER TABLE',
            $table,
            $alterations
        ]), []];
    }

    /**
     * @InheritDoc
     */
    public function compileStatementDropIndex(array $statement)
    {
        return ['', []];
    }




    protected function compileStatementColumn($columnArray)
    {
        /** @var ColumnBuilder $columnBuilder */
        $columnBuilder = $columnArray['columnBuilder'];
        $statement     = $columnBuilder->getStatement();

        // Name
        $name = $this->sanitise($columnArray['name']);
        // Data Type
        $dataType = strtoupper($statement['dataType']['type']);
        switch ($statement['dataType']['type']) {
            case 'tinyInteger':
            case 'smallInteger':
            case 'mediumInteger':
            case 'integer':
            case 'bigInteger':
                $dataType = 'INTEGER';
                break;
            case 'double':
            case 'float':
            case 'decimal':
                $dataType = 'REAL';
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
                $dataType = 'TEXT';
                break;
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
            $dataType,
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
                $referenceTable   = $this->sanitise($constraintArray['referenceTable']);
                $referenceColumns = implode(',', array_map([$this, 'sanitise'], $constraintArray['referenceColumns']));
                $onDelete         = (isset($constraintArray['onDelete']) ? ' ON DELETE ' . $constraintArray['onDelete'] : '');
                $onUpdate         = (isset($constraintArray['onUpdate']) ? ' ON UPDATE ' . $constraintArray['onUpdate'] : '');
                return 'FOREIGN KEY '
                    . ($constraintArray['name'] ?? '') . '(' . $columns . ') REFERENCES ' . $referenceTable
                    . '(' . $referenceColumns . ')' . $onDelete . $onUpdate;
        }

        return '';
    }

    protected function compileStatementAlterations(array $statement)
    {
        $sql = '';
        foreach ($statement['alterations'] as $alteration) {
            $sql .= "\n";
            switch ($alteration['type']) {
                case 'addColumn':
                    $sql .= 'ADD ' . $this->compileStatementColumn($alteration);
                    break;
                case 'dropColumn':
                    $sql .= 'DROP COLUMN ' . $alteration['column'];
                    break;
                case 'modifyColumn':
                    $sql .= 'MODIFY COLUMN ' . $this->compileStatementColumn($alteration);
                    break;
            }
        }

        return trim($sql);
    }
}