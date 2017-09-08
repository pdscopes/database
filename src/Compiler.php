<?php

namespace MadeSimple\Database;

use MadeSimple\Database\Query\Column;
use MadeSimple\Database\Query\Raw;
use MadeSimple\Database\Query\Select;
use MadeSimple\Database\Query\WhereBuilder;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class Compiler implements CompilerInterface
{
    use LoggerAwareTrait, ConnectionAwareTrait;

    /**
     * @var string
     */
    protected $sanitiseWrapper = '';

    /**
     * Compiler constructor.
     *
     * @param string               $sanitiseWrapper
     * @param null|LoggerInterface $logger
     */
    public function __construct($sanitiseWrapper, LoggerInterface $logger = null)
    {
        $this->sanitiseWrapper = $sanitiseWrapper;
        $this->setLogger($logger ?? new NullLogger);
    }

    /**
     * @param string $dirtyColumnRef
     *
     * @return string
     */
    public function sanitise($dirtyColumnRef)
    {
        if ($dirtyColumnRef === '*') {
            return $dirtyColumnRef;
        }

        $delimiter = $this->sanitiseWrapper;
        $sanitised = '';
        foreach (explode('.', $dirtyColumnRef) as $piece) {
            $sanitised .= '.' . ($piece === '*'
                ? '*'
                : $delimiter . str_replace($delimiter, $delimiter.$delimiter, $piece) . $delimiter);
        }
        return substr($sanitised, 1);
    }

    /**
     * @param string $dirtyColumnRef
     *
     * @return string
     */
    protected function querySanitise($dirtyColumnRef)
    {
        if ($dirtyColumnRef instanceof Raw) {
            return (string) $dirtyColumnRef;
        }
        if (!is_string($dirtyColumnRef)) {
            return $dirtyColumnRef;
        }

        return static::sanitise($dirtyColumnRef);
    }

    /**
     * Concatenate query pieces into a proper string.
     *
     * @param array  $pieces
     * @param string $glue
     *
     * @return string
     */
    protected function concatenateSql(array $pieces, $glue = ' ')
    {
        $query = implode($glue, array_filter(array_map('trim', $pieces)));

        return $query;
    }

    /**
     * Compiles and sanitises an array of string (optionally alias as array key)
     * into a comma separated string.
     *
     * @param array  $array
     * @param string $glue
     *
     * @return string
     */
    protected function compileSanitiseArray(array $array, $glue = ',')
    {
        $sanitised = [];
        foreach ($array as $alias => $value) {
            $value = $this->sanitise($value);

            if (!is_int($alias)) {
                $value .= ' AS ' . $this->sanitise($alias);
            }

            $sanitised[] = $value;
        }

        return implode($glue, $sanitised);
    }

    /**
     * Compiles and sanitises an array of string (optionally alias as array key)
     * into a comma separated string.
     *
     * @param array  $array
     * @param string $glue
     *
     * @return string
     */
    protected function compileQuerySanitiseArray(array $array, $glue = ',')
    {
        $sanitised = [];
        foreach ($array as $alias => $value) {
            $value = $this->querySanitise($value);

            if (!is_int($alias)) {
                $value .= ' AS ' . $this->sanitise($alias);
            }

            $sanitised[] = $value;
        }

        return implode($glue, $sanitised);
    }


    public function compileQuerySelect(array $statement)
    {
        // Validate select statement
        if (!array_key_exists('from', $statement)) {
            throw new \RuntimeException('No tables to select from specified');
        }


        // Select
        $columns = $this->compileQuerySanitiseArray($statement['columns'] ?? ['*']);
        // From
        $tables  = $this->compileQuerySanitiseArray($statement['from']);
        // Join
        list($joinCriteria, $joinBindings)   = $this->compileQueryJoins($statement);
        // Where
        list($whereCriteria, $whereBindings) = $this->compileQueryCriteriaWithType($statement, 'where', 'WHERE');
        // Group by
        $groupBy = isset($statement['groupBy']) ? 'GROUP BY ' . $this->compileQuerySanitiseArray($statement['groupBy']) : '';
        // Having
        list($havingCriteria, $havingBindings) = $this->compileQueryCriteriaWithType($statement, 'having', 'HAVING');
        // Order By
        $orderBy = $this->compileQueryOrderBy($statement);
        // Limit
        $limit   = isset($statement['limit']) ? 'LIMIT ' . $statement['limit'] : '';
        // Offset
        $offset  = isset($statement['offset']) ? 'OFFSET ' . $statement['offset'] : '';


        // Put all the parts together
        $sql      = $this->concatenateSql([
            'SELECT',
            $columns,
            'FROM',
            $tables,
            $joinCriteria,
            $whereCriteria,
            $groupBy,
            $havingCriteria,
            $orderBy,
            $limit,
            $offset
        ]);
        $bindings = array_merge($joinBindings, $whereBindings, $havingBindings);

        return [$sql, $bindings];
    }

    public function compileQueryInsert(array $statement)
    {
        // Validate select statement
        if (!array_key_exists('into', $statement)) {
            throw new \RuntimeException('No table to insert into specified');
        }


        // From
        $tables  = $this->compileQuerySanitiseArray($statement['into']);
        // Columns
        $columns = $this->compileQuerySanitiseArray($statement['columns']);
        $columns = empty($columns) ? '' : '(' . $columns . ')';
        // Values
        list($valuesSql, $valuesBindings) = $this->compileQueryValues($statement);


        // Put all the parts together
        $sql      = $this->concatenateSql([
            'INSERT',
            'INTO',
            $tables,
            $columns,
            $valuesSql
        ]);
        $bindings = array_merge($valuesBindings);

        return [$sql, $bindings];
    }

    public function compileQueryUpdate(array $statement)
    {
        // Validate select statement
        if (!array_key_exists('table', $statement)) {
            throw new \RuntimeException('No table to update specified');
        }


        // From
        $table   = $this->compileQuerySanitiseArray($statement['table']);
        // Set Pairs
        list($setSql, $setBindings) = $this->compileQuerySetPairs($statement);
        // Where
        list($whereCriteria, $whereBindings) = $this->compileQueryCriteriaWithType($statement, 'where', 'WHERE');


        // Put all the parts together
        $sql      = $this->concatenateSql([
            'UPDATE',
            $table,
            'SET',
            $setSql,
            $whereCriteria
        ]);
        $bindings = array_merge($setBindings, $whereBindings);

        return [$sql, $bindings];
    }

    public function compileQueryDelete(array $statement)
    {
        // Validate select statement
        if (!array_key_exists('from', $statement)) {
            throw new \RuntimeException('No tables to delete from specified');
        }


        // From
        $tables  = $this->compileQuerySanitiseArray($statement['from']);
        // Where
        list($whereCriteria, $whereBindings) = $this->compileQueryCriteriaWithType($statement, 'where', 'WHERE');


        // Put all the parts together
        $sql      = $this->concatenateSql([
            'DELETE',
            'FROM',
            $tables,
            $whereCriteria
        ]);
        $bindings = array_merge($whereBindings);

        return [$sql, $bindings];
    }


    public function compileStatementCreateDb(array $statement)
    {
        return [$this->concatenateSql([
            'CREATE DATABASE',
            $this->compileSanitiseArray($statement['database']),
        ]), []];
    }

    public function compileStatementDropDb(array $statement)
    {
        return [$this->concatenateSql([
            'DROP DATABASE',
            $this->compileSanitiseArray($statement['database']),
        ]), []];
    }

    public function compileStatementCreateTable(array $statement)
    {
        // Table
        $table = $this->compileSanitiseArray($statement['table']);

        return [$this->concatenateSql([
            'CREATE TABLE',
            $table,
            '()'
        ]), []];
    }

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

    public function compileStatementTruncateTable(array $statement)
    {
        return [$this->concatenateSql([
            'TRUNCATE TABLE',
            $this->compileSanitiseArray($statement['table']),
        ]), []];
    }

    public function compileStatementDropTable(array $statement)
    {
        return [$this->concatenateSql([
            'DROP TABLE',
            $this->compileSanitiseArray($statement['table']),
        ]), []];
    }

    public function compileStatementCreateIndex(array $statement)
    {
        // Unique
        $unique = isset($statement['unique']) ? 'UNIQUE' : '';
        // Index
        $name   = $this->compileSanitiseArray($statement['name']);
        // Table
        $table  = $this->compileSanitiseArray($statement['table']);

        return [$this->concatenateSql([
            'CREATE',
            $unique,
            'INDEX',
            $name,
            'ON',
            $table,
        ]), []];
    }

    public function compileStatementDropIndex(array $statement)
    {
        // Index
        $name  = $this->compileSanitiseArray($statement['name']);
        // Table
        $table = $this->compileSanitiseArray($statement['table']);

        return [$this->concatenateSql([
            'DROP INDEX',
            $name,
            'ON',
            $table,
        ]), []];
    }

    public function compileStatementCreateView(array $statement)
    {
        // Index
        $name   = $this->compileSanitiseArray($statement['name']);
        // Table
        list($selectSql, $selectBindings) = $this->compileStatementSelect($statement);

        return [$this->concatenateSql([
            'CREATE VIEW',
            $name,
            'AS',
            $selectSql,
        ]), $selectBindings];
    }

    public function compileStatementUpdateView(array $statement)
    {
        // Index
        $name   = $this->compileSanitiseArray($statement['name']);
        // Table
        list($selectSql, $selectBindings) = $this->compileStatementSelect($statement);

        return [$this->concatenateSql([
            'CREATE OR REPLACE VIEW',
            $name,
            'AS',
            $selectSql,
        ]), $selectBindings];
    }

    public function compileStatementDropView(array $statement)
    {
        // Index
        $name   = $this->compileSanitiseArray($statement['name']);

        return [$this->concatenateSql([
            'DROP VIEW',
            $name,
        ]), []];
    }


    /**
     * Compiles the joins and returns the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string,array]
     */
    protected function compileQueryJoins($statement)
    {
        $sql      = '';
        $bindings = [];
        if (!array_key_exists('join', $statement)) {
            return [$sql, $bindings];
        }

        foreach ($statement['join'] as $joinArray) {
            $type      = strtoupper($joinArray['type']);
            $table     = $this->querySanitise($joinArray['table']);
            $statement = $joinArray['statement'];

            if (null !== $joinArray['alias']) {
                $table .= ' AS ' . $this->querySanitise($joinArray['alias']);
            }

            list($joinCriteria, $joinBindings) = $this->compileQueryCriteria($statement['where']);

            $bindings = array_merge($bindings, $joinBindings);
            $sql = $this->concatenateSql([
                $sql,
                $type .' JOIN',
                $table,
                'ON',
                $joinCriteria
            ], ' ');
        }

        return [$sql, $bindings];
    }

    /**
     * Compiles the joins and returns the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string,array]
     */
    protected function compileQueryValues($statement)
    {
        $sql      = '';
        $bindings = [];
        if (!array_key_exists('values', $statement) || empty($statement['values'])) {
            return [$sql, $bindings];
        }

        $sql     .= 'VALUES (' . implode(',', array_fill(0, count($statement['values']), '?')) . ')';
        $bindings = array_merge($bindings, $statement['values']);

        return [$sql, $bindings];
    }

    /**
     * Compiles the joins and returns the SQL and bindings.
     *
     * @param array $statement
     *
     * @return array [string,array]
     */
    protected function compileQuerySetPairs($statement)
    {
        if (!array_key_exists('columns', $statement) || !array_key_exists('values', $statement)) {
            throw new \RuntimeException('Update queries must have both columns and values set');
        }
        if (count($statement['columns']) !== count($statement['values'])) {
            throw new \RuntimeException('Update queries must have a matching number of columns and values');
        }

        $sql      = '';
        $bindings = $statement['values'];
        foreach ($statement['columns'] as $column) {
            $sql .= ',' . $this->querySanitise($column) . '=?';
        }
        $sql = substr($sql, 1);

        return [$sql, $bindings];
    }




    /**
     * Checks that $statement contains an element $field. Then compiles that
     * criteria and prepends $keyword.
     *
     * @param array  $statement
     * @param string $field
     * @param string $keyword
     *
     * @return array [string, array]
     */
    protected function compileQueryCriteriaWithType(array $statement, $field, $keyword)
    {
        $criteria = '';
        $bindings = [];

        if (array_key_exists($field, $statement)) {
            list($criteria, $bindings) = $this->compileQueryCriteria($statement[$field]);

            if ($criteria) {
                $criteria = $keyword . ' ' . $criteria;
            }
        }

        return [$criteria, $bindings];
    }

    /**
     * Compiles $statement into SQL and builds an array of bindings.
     *
     * @param array $statements
     *
     * @return array [string, array]
     */
    protected function compileQueryCriteria(array $statements)
    {
        $criteria = '';
        $bindings = [];
        foreach ($statements as $statement) {
            $type = $statement['type'] ?? null;
            switch ($type) {
                case 'exists':
                    $this->compileQueryExistsStatement($statement, $criteria, $bindings);
                    break;

                default:
                    $this->compileQueryBasicStatement($statement, $criteria, $bindings);
                    break;
            }
        }
        $criteria = trim(substr($criteria, 3));

        return [$criteria, $bindings];
    }

    /**
     * Compiles $statement into SQL appending it to $criteria and adds the bindings
     * to $bindings.
     *
     * @param array  $statement
     * @param string $criteria
     * @param array  $bindings
     */
    protected function compileQueryBasicStatement(array $statement, &$criteria, &$bindings)
    {
        $column   = $this->querySanitise($statement['column']);
        $operator = strtoupper($statement['operator']);
        $value    = $statement['value'];
        $boolean  = strtoupper($statement['boolean']);

        if ($column instanceof \Closure) {
            // Nested criteria
            list ($nestedCriteria, $nestedBindings) = $this->compileQueryNestedCriteria($column);
            $bindings  = array_merge($bindings, $nestedBindings);
            $criteria .= $boolean . ' (' . $nestedCriteria . ') ';
        } elseif (is_array($value)) {
            // Array of values
            $bindings  = array_merge($bindings, array_values($value));
            $criteria .= $boolean . ' ' . $column . ' ' . $operator;

            switch ($operator) {
                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $criteria .= ' ? AND ? ';
                    break;
                default:
                    $criteria .= ' (' . implode(',', array_fill(0, count($value), '?')) . ') ';
                    break;
            }
        } elseif ($value instanceof Column) {
            // Column comparison
            $value     = $this->querySanitise((string) $value);
            $criteria .= $boolean . ' ' . $column . ' ' . $operator . ' ' . $value . ' ';
        } elseif ($value instanceof Raw) {
            // Raw value
            $criteria .= $boolean . ' ' . $column . ' ' . $operator . ' ' . $value . ' ';
        } else {
            // Standard where
            if ($statement['column'] instanceof Raw) {
                // Completely raw condition
                $criteria .= $boolean . ' ' . $statement['column'] . ' ';
            } else {
                // Standard condition
                $bindings  = array_merge($bindings, [$statement['value']]);
                $criteria .= $boolean . ' ' . $column . ' ' . $operator . ' ? ';
            }
        }
    }

    /**
     * Compiles the EXISTS (NOT EXISTS) type statement into SQL appending it to $criteria and
     * adds the bindings to $bindings.
     *
     * @param array  $statement
     * @param string $criteria
     * @param array  $bindings
     */
    protected function compileQueryExistsStatement(array $statement, &$criteria, &$bindings)
    {
        $exists  = ($statement['not'] ? ' NOT ' : ' ') . 'EXISTS ';
        $boolean = strtoupper($statement['boolean']);
        list($existsCriteria, $existsBindings) = $statement['builder']->buildSql();

        $criteria .= $boolean . $exists . '(' . $existsCriteria . ') ';
        $bindings  = array_merge($bindings, $existsBindings);
    }

    /**
     * Compiles a nested criteria into SQL and bindings.
     *
     * @param \Closure $closure
     *
     * @return array [string, array]
     */
    protected function compileQueryNestedCriteria($closure)
    {
        $whereBuilder = new WhereBuilder($this->connection);
        $closure($whereBuilder);

        return $this->compileQueryCriteria($whereBuilder->getStatementPiece('where', []));
    }

    /**
     * Compiles the statements order by into SQL.
     *
     * @param array $statement
     *
     * @return string
     */
    protected function compileQueryOrderBy(array $statement)
    {
        if (empty($statement['orderBy'])) {
            return '';
        }
        $sql = '';
        foreach ($statement['orderBy'] as $orderBy) {
            $sql .= ',' . $this->querySanitise($orderBy['column']) . ' ' . strtoupper($orderBy['direction']);
        }
        return 'ORDER BY ' . substr($sql, 1);
    }




    protected function compileStatementAlterations(array $statement)
    {
        $sql = '';
        foreach ($statement['alterations'] as $alteration) {
            $sql .= "\n";
            switch ($alteration['type']) {
                case 'add':
                    $sql .= 'ADD ' . $alteration['column'] . ' ' . $alteration['dataType'] . ' ' . $alteration['constraints'];
                    break;
                case 'drop':
                    $sql .= 'DROP COLUMN ' . $alteration['column'];
                    break;
                case 'modify':
                    $sql .= 'MODIFY COLUMN ' . $alteration['column'] . ' ' . $alteration['dataType'] . ' ' . $alteration['constraints'];
                    break;
            }
        }

        return trim($sql);
    }

    public function compileStatementSelect(array $statement)
    {
        /** @var Select $select */
        $select = reset($statement['select']);
        return $select->buildSql();
    }
}