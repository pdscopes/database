<?php

namespace MadeSimple\Database\Entity;

use MadeSimple\Database\Pool;

/**
 * @uses \MadeSimple\Database\Entity::$connection
 */
trait Queryable
{
    /**
     * @return \MadeSimple\Database\EntityMap
     */
    public static abstract function map();

    /**
     * @param \MadeSimple\Database\Pool $pool
     * @param string|null $alias
     * @return \MadeSimple\Database\Query\Select
     */
    public static function qSelect(Pool $pool, $alias = null)
    {
        $connection = $pool->get(static::$connection);
        $map        = static::map();

        return $connection->select()->columns('*')->from($map->tableName(), $alias);
    }

    /**
     * @param \MadeSimple\Database\Pool $pool
     * @return \MadeSimple\Database\Query\Insert
     */
    public static function qInsert(Pool $pool)
    {
        $connection = $pool->get(static::$connection);
        $map        = static::map();

        return $connection->insert()->into($map->tableName());
    }

    /**
     * @param \MadeSimple\Database\Pool $pool
     * @return \MadeSimple\Database\Query\Update
     */
    public static function qUpdate(Pool $pool)
    {
        $connection = $pool->get(static::$connection);
        $map        = static::map();

        return $connection->update()->table($map->tableName());
    }

    /**
     * @param \MadeSimple\Database\Pool $pool
     * @return \MadeSimple\Database\Query\Delete
     */
    public static function qDelete(Pool $pool)
    {
        $connection = $pool->get(static::$connection);
        $map        = static::map();

        return $connection->delete()->from($map->tableName());
    }
}