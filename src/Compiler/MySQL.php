<?php

namespace MadeSimple\Database\Compiler;

use MadeSimple\Database\Compiler;
use MadeSimple\Database\Statement\ColumnBuilder;
use Psr\Log\LoggerInterface;

class MySQL extends Compiler
{
    public function __construct(LoggerInterface $logger = null)
    {
        parent::__construct('`', $logger);
    }


    /**
     * @InheritDoc
     */
    public function compileStatementCreateTable(array $statement)
    {
        // Temporary
        $temporary = isset($statement['temporary']) ? 'TEMPORARY' : '';
        // IF NOT EXISTS
        $ifNotExists = isset($statement['ifNotExists']) ? 'IF NOT EXISTS' : '';

        // Table
        $table = $this->sanitise($statement['table']);

        // Columns
        $columns = $this->concatenateSql(array_map([$this, 'compileStatementColumn'], $statement['columns'] ?? []), ',');
        // Constraints
        $constraints = $this->concatenateSql(array_map([$this, 'compileStatementConstraint'], $statement['constraints'] ?? []), ',');
        $constraints = !empty($constraints) ? ',' . $constraints : '';

        // Options
        $options[] = !empty($statement['engine']) ? 'ENGINE='.$statement['engine'] : '';
        $options[] = !empty($statement['charset']) ? 'DEFAULT CHARACTER SET='.$statement['charset'] : '';
        $options[] = !empty($statement['collate']) ? 'COLLATE='.$statement['collate'] : '';
        $options[] = !empty($statement['comment']) ? "COMMENT='{$statement['comment']}'" : '';
        $options = implode(',', array_filter($options));

        return [$this->concatenateSql([
            'CREATE',
            $temporary,
            'TABLE',
            $ifNotExists,
            $table,
            '(',
            $columns,
            $constraints,
            ')',
            $options
        ]), []];
    }

    /**
     * @InheritDoc
     */
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

    /**
     * @InheritDoc
     */
    public function compileStatementDropIndex(array $statement)
    {
        // Table
        $table = $this->sanitise($statement['table']);
        // Index
        $name  = $this->sanitise($statement['index']);

        return [$this->concatenateSql([
            'ALTER TABLE',
            $table,
            'DROP INDEX',
            $name,
        ]), []];
    }




    protected function compileStatementColumn($columnArray, $createSyntax = true)
    {
        if (!isset($columnArray['columnBuilder'])) {
            return $this->sanitise($columnArray['name']);
        }

        /** @var ColumnBuilder $columnBuilder */
        $columnBuilder = $columnArray['columnBuilder'];
        $statement     = $columnBuilder->getStatement();

        // Name
        $name = $this->sanitise($columnArray['name']);
        // Data Type
        $dataType = '';
        if (isset($statement['dataType'])) {
            $dataType = strtoupper($statement['dataType']['type']);
            switch ($statement['dataType']['type']) {
                case 'tinyInteger':
                case 'smallInteger':
                case 'mediumInteger':
                case 'integer':
                case 'bigInteger':
                    $dataType = str_replace('INTEGER', 'INT', $dataType)
                        . '(' . $statement['dataType']['length'] . ')'
                        . ($statement['dataType']['unsigned'] ? ' UNSIGNED' :'')
                        . ($statement['dataType']['zerofill'] ? ' ZEROFILL' : '');
                    break;
                case 'double':
                case 'float':
                case 'decimal':
                    $dataType = 'REAL(' . $statement['dataType']['length'] . ',' . $statement['dataType']['decimals'] . ')'
                        . ($statement['dataType']['unsigned'] ? ' UNSIGNED' :'')
                        . ($statement['dataType']['zerofill'] ? ' ZEROFILL' : '');
                    break;
                case 'date':
                    $dataType = 'DATE';
                    break;
                case 'time':
                case 'timestamp':
                case 'dateTime':
                    $dataType = $dataType
                        . (null !== $statement['dataType']['fsp'] ? '(' . $statement['dataType']['fsp'] . ')' : '');
                    break;
                case 'char':
                case 'varchar':
                    $dataType = $dataType
                        . '(' . $statement['dataType']['length'] . ')' . ($statement['dataType']['binary'] ? ' BINARY' : '')
                        . (null !== $statement['dataType']['charset'] ? ' CHARACTER SET ' . $statement['dataType']['charset'] : '')
                        . (null !== $statement['dataType']['collate'] ? ' COLLATE ' . $statement['dataType']['collate'] : '');
                    break;
                case 'binary':
                    $dataType = 'BINARY(' . $statement['dataType']['length']. ')';
                    break;
                case 'tinyBlob':
                case 'blob':
                case 'mediumBlob':
                case 'longBlob':
                    $dataType = $dataType;
                    break;
                case 'tinyText':
                case 'text':
                case 'mediumText':
                case 'longText':
                    $dataType = $dataType
                        . ($statement['dataType']['binary'] ? ' BINARY' :'')
                        . (null !== $statement['dataType']['charset'] ? ' CHARACTER SET ' . $statement['dataType']['charset'] : '')
                        . (null !== $statement['dataType']['collate'] ? ' COLLATE ' . $statement['dataType']['collate'] : '');
                    break;
                case 'enum':
                    $dataType = 'ENUM(\'' . implode('\',\'', $statement['dataType']['values']) . '\')'
                        . (null !== $statement['dataType']['charset'] ? ' CHARACTER SET ' . $statement['dataType']['charset'] : '')
                        . (null !== $statement['dataType']['collate'] ? ' COLLATE ' . $statement['dataType']['collate'] : '');
                    break;
                case 'json':
                    $dataType = 'JSON';
                    break;
            }
        }
        // Null
        $null = isset($statement['null']) ? ($statement['null'] ? 'NULL': 'NOT NULL') : '';
        // Default
        $default = isset($statement['default']) ? "DEFAULT '{$statement['default']}'" : '';
        // Use Current
        $useCurrent = isset($statement['useCurrent']) ? "DEFAULT CURRENT_TIMESTAMP" : '';
        // Auto Increment
        $autoIncrement = isset($statement['autoIncrement']) ? 'AUTO_INCREMENT' : '';
        // Comment
        $comment = isset($statement['comment']) ? "COMMENT '{$statement['comment']}'" : '';
        // Primary Key
        $primaryKey = isset($statement['primaryKey']) ? 'PRIMARY KEY' : '';
        // Unique
        $unique = isset($statement['unique']) ? 'UNIQUE' : '';

        // First
        $first = !$createSyntax && isset($statement['first']) ? 'FIRST' : '';
        // After
        $after = !$createSyntax && isset($statement['first']) ? 'AFTER ' . $this->sanitise($statement['after']) : '';


        return $this->concatenateSql([
            $name,
            $dataType,
            $null,
            $default,
            $useCurrent,
            $autoIncrement,
            $primaryKey,
            $unique,
            $comment,
            $first,
            $after,
        ]);
    }

    protected function compileStatementConstraint($constraintArray)
    {
        $columns = implode(',', array_map([$this, 'sanitise'], $constraintArray['columns']));
        switch ($constraintArray['type']) {
            case 'primaryKey':
                return 'PRIMARY KEY (' . $columns . ')';

            case 'index':
                $name = $constraintArray['name'] ? $this->sanitise($constraintArray['name']) : '';
                return 'INDEX ' . $name . '(' . $columns . ')';

            case 'unique':
                $name = $constraintArray['name'] ? $this->sanitise($constraintArray['name']) : '';
                return 'UNIQUE ' . $name . '(' . $columns . ')';

            case 'foreignKey':
                $name = $constraintArray['name'] ? $this->sanitise($constraintArray['name']) : '';
                $referenceTable   = $this->sanitise($constraintArray['referenceTable']);
                $referenceColumns = implode(',', array_map([$this, 'sanitise'], $constraintArray['referenceColumns']));
                $onDelete         = (isset($constraintArray['onDelete']) ? ' ON DELETE ' . strtoupper($constraintArray['onDelete']) : '');
                $onUpdate         = (isset($constraintArray['onUpdate']) ? ' ON UPDATE ' . strtoupper($constraintArray['onUpdate']) : '');
                return 'FOREIGN KEY '. $name . '(' . $columns . ') REFERENCES ' . $referenceTable
                    . '(' . $referenceColumns . ')' . $onDelete . $onUpdate;
        }

        return '';
    }

    protected function compileStatementAlterations(array $statement)
    {
        $sql = '';
        foreach ($statement['alterations'] ?? [] as $alteration) {
            $sql .= ', ';
            switch ($alteration['type']) {
                case 'addColumn':
                    $sql .= 'ADD ' . $this->compileStatementColumn($alteration, false);
                    break;
                case 'dropColumn':
                    $sql .= 'DROP COLUMN ' . $this->sanitise($alteration['column']);
                    break;
                case 'modifyColumn':
                    $sql .= 'MODIFY COLUMN ' . $this->compileStatementColumn($alteration, false);
                    break;
                case 'renameColumn':
                    $sql .= 'CHANGE ' . $this->sanitise($alteration['currentName']) . ' ' . $this->compileStatementColumn($alteration);
                    break;

                case 'addForeignKey':
                    $name             = ($alteration['name'] ? 'CONSTRAINT ' . $this->sanitise($alteration['name']) . ' ' : '');
                    $columns          = implode(',', array_map([$this, 'sanitise'], $alteration['columns']));
                    $referenceTable   = $this->sanitise($alteration['referenceTable']);
                    $referenceColumns = implode(',', array_map([$this, 'sanitise'], $alteration['referenceColumns']));
                    $onDelete         = (isset($alteration['onDelete']) ? ' ON DELETE ' . $alteration['onDelete'] : '');
                    $onUpdate         = (isset($alteration['onUpdate']) ? ' ON UPDATE ' . $alteration['onUpdate'] : '');

                    $sql .= 'ADD ' . $name
                        . 'FOREIGN KEY '
                        . '(' . $columns . ') REFERENCES ' . $referenceTable
                        . '(' . $referenceColumns . ')' . $onDelete . $onUpdate;
                    break;
                case 'dropForeignKey':
                    $sql .= 'DROP FOREIGN KEY ' . $this->sanitise($alteration['foreignKey']);
                    break;

                case 'addUnique':
                    $name    = ($alteration['name'] ? 'CONSTRAINT ' . $this->sanitise($alteration['name']) . ' ' : '');
                    $columns = implode(',', array_map([$this, 'sanitise'], $alteration['columns']));

                    $sql .= 'ADD ' . $name . 'UNIQUE (' . $columns . ')';
                    break;
                case 'dropUnique':
                    $sql .= 'DROP INDEX ' . $this->sanitise($alteration['unique']);
                    break;
            }
        }

        return trim(substr($sql, 1));
    }
}