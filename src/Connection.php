<?php

namespace MadeSimple\Database;

use MadeSimple\Database\QueryBuilder as Qb;

/**
 * Class Connection
 *
 * @package MadeSimple\Database
 * @author  Peter Scopes
 */
class Connection
{
    public static $columnQuote = '`';

    /**
     * @var \PDO[]
     */
    private static $instances = [];

    /**
     * @var array
     */
    private static $transactions = [];

    /**
     * @param string $key
     * @param \PDO   $pdo
     */
    public static function set($key, \PDO $pdo)
    {
        self::$instances[$key] = $pdo;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public static function lastInsertId($key)
    {
        return self::get($key)->lastInsertId();
    }

    /**
     * @param null|string $key
     *
     * @return \PDO
     */
    public static function get($key = null)
    {
        $key = is_null($key) ? 'system' : $key;

        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        throw new \InvalidArgumentException(sprintf('Unknown key: %s', $key));
    }

    /**
     * @param string $key
     *
     * @return Qb\Select
     */
    public static function select($key)
    {
        return new Qb\Select(self::get($key));
    }

    /**
     * @param string $key
     *
     * @return Qb\Insert
     */
    public static function insert($key)
    {
        return new Qb\Insert(self::get($key));
    }

    /**
     * @param string $key
     *
     * @return Qb\Update
     */
    public static function update($key)
    {
        return new Qb\Update(self::get($key));
    }

    /**
     * @param string $key
     *
     * @return Qb\Delete
     */
    public static function delete($key)
    {
        return new Qb\Delete(self::get($key));
    }


    /**
     * @param string $key
     *
     * @return bool
     */
    public static function beginTransaction($key = null)
    {
        $key = is_null($key) ? 'system' : $key;

        if (isset(self::$transactions[$key])) {
            self::$transactions[$key]++;

            return true;
        }

        if (self::$instances[$key]->beginTransaction()) {
            self::$transactions[$key] = 1;

            return true;
        }

        return false;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function rollBack($key = null)
    {
        $key = is_null($key) ? 'system' : $key;

        if (!isset(self::$transactions[$key])) {
            return false;
        }

        if (--self::$transactions[$key] == 0) {
            return self::$instances[$key]->rollBack();
        };

        return true;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function commit($key = null)
    {
        $key = is_null($key) ? 'system' : $key;

        if (!isset(self::$transactions[$key])) {
            return false;
        }

        if (--self::$transactions[$key] == 0) {
            return self::$instances[$key]->commit();
        };

        return true;
    }


    /**
     * @param string $clause
     *
     * @return string
     */
    public static function quoteClause($clause)
    {
        $clause = trim($clause);

        // Check for sub clauses
        if (false !== ($pos = strpos($clause, '('))) {
            $modClause  = $clause;
            $subClauses = [];
            while (false !== ($pos = strpos($modClause, '('))) {
                $depth = 1;
                for ($i = $pos + 1; $i < strlen($modClause) && $depth > 0; $i++) {
                    if ('(' === $modClause{$i}) {
                        $depth++;
                    }
                    if (')' === $modClause{$i}) {
                        $depth--;
                    }
                }

                // Extract the sub clause
                $subClause = substr($modClause, $pos + 1, ($i - $pos) - 2);

                // Is this a function?
                $function = '';
                if ($pos > 0 && ' ' !== $modClause{$pos - 1}) {
                    $pos      = strrpos(substr($modClause, 0, $pos), ' ') + 1;
                    $function = substr($modClause, $pos, ($i - $pos) - (strlen($subClause) + 2));
                }

                $count                    = count($subClauses);
                $modClause                = str_replace($function . '(' . $subClause . ')', '$' . $count, $modClause);
                $subClauses['$' . $count] = $function . '(' . self::quoteClause($subClause) . ')';
            }

            $modClause = self::quoteClause($modClause);
            foreach ($subClauses as $k => $subClause) {
                $modClause = str_replace($k, $subClause, $modClause);
            }

            return $modClause;
        }

        // Handle operators
        $arithmeticOperators = ['+', '-', '*', '/', '%'];
        $comparisonOperators = ['>=', '<=', '!=', '<>', '!<', '!>', '=', '>', '<'];
        $logicalOperators    =
            ['AND', '&&', 'OR', '||', 'IS', 'ALL', 'ANY', 'BETWEEN', 'EXISTS', 'IN', 'LIKE', 'NOT', 'UNIQUE'];
        foreach (array_merge($arithmeticOperators, $comparisonOperators, $logicalOperators) as $operator) {
            if (false !== strpos($clause, $operator)) {
                return implode(' ' . $operator .
                    ' ', array_map([Connection::class, 'quoteClause'], explode($operator, $clause))
                );
            }
        }

        // Check that this should be quoted
        $logicalValues = ['NULL', 'TRUE', 'FALSE'];
        if (
            0 === strpos($clause, ':') ||
            0 === strpos($clause, '"') ||
            0 === strpos($clause, '\'') ||
            0 === strpos($clause, '$') ||
            in_array($clause, $logicalValues) ||
            '?' === $clause ||
            is_numeric($clause)
        ) {
            return $clause;
        }

        return self::quoteColumn($clause);
    }

    /**
     * Applies quotes to the column.
     *
     * @param string $column
     *
     * @return string
     */
    public static function quoteColumn($column)
    {
        $str = $column;

        // Only consider non function parts
        $lBracket = strrpos($column, '(');
        $rBracket = strpos($column, ')');

        if (false !== $lBracket && false !== $rBracket) {
            $str = substr($column, $lBracket, $rBracket - $lBracket);
        }

        // Apply quotes
        $quoted = implode('.', array_map([Connection::class, 'applyQuote'], explode('.', $str)));

        return str_replace($str, $quoted, $column);
    }

    /**
     * @param string $column
     *
     * @return string
     */
    public static function applyQuote($column)
    {
        $column = trim($column, self::$columnQuote);

        return '*' == $column ? $column : sprintf('%1$s%2$s%1$s', self::$columnQuote, $column);
    }
}