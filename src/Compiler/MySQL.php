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


    public function compileStatementCreateTable(array $statement)
    {
        // Temporary
        $temporary = isset($statement['temporary']) ? 'TEMPORARY' : '';
        // IF NOT EXISTS
        $ifNotExists = isset($statement['ifNotExists']) ? 'IF NOT EXISTS' : '';

        // Table
        $table = $this->compileSanitiseArray($statement['table'] ?? []);

        // Columns
        $columns = $this->concatenateSql(array_map([$this, 'compileStatementColumn'], $statement['columns'] ?? []), ',');
        // Constraints
        $constraints = $this->concatenateSql(array_map([$this, 'compileStatementConstraint'], $statement['constraints'] ?? []), ',');

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
            ',',
            $constraints,
            ')',
            $options
        ]), []];
    }

    protected function compileStatementColumn($columnArray, $createSyntax = true)
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
                $dataType = str_replace('INTEGER', 'INT', $dataType);
                $dataType = $dataType . '(' . $statement['dataType']['length'] . ')'
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
                $dataType = $dataType . (null !== $statement['dataType']['fsp'] ? '(' . $statement['dataType']['fsp'] . ')' : '');
                break;
            case 'char':
            case 'varchar':
                $dataType = $dataType . '(' . $statement['dataType']['length'] . ')' . ($statement['dataType']['binary'] ? ' BINARY' : '')
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
                break;
            case 'tinyText':
            case 'text':
            case 'mediumText':
            case 'longText':
                $dataType = $dataType . ($statement['dataType']['binary'] ? ' BINARY' :'')
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
}