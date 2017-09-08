<?php

namespace MadeSimple\Database\Query;

use MadeSimple\Database\CompilableTrait;
use MadeSimple\Database\Builder;
use PDO;

abstract class QueryBuilder extends Builder
{
    use CompilableTrait;

    /**
     * The PDO fetch parameters to use.
     *
     * @var array
     */
    protected $fetchParameters = [PDO::FETCH_ASSOC];

    /**
     * Set the fetch mode.
     *
     * @param array ...$mode
     *
     * @return static
     */
    public function setFetchMode(...$mode)
    {
        $this->fetchParameters = $mode;

        return $this;
    }
}