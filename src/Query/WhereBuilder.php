<?php

namespace MadeSimple\Database\Query;

use MadeSimple\Database\Builder;

class WhereBuilder extends Builder
{
    use WhereTrait;

    /**
     * @param string     $piece
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getStatementPiece($piece, $default = null)
    {
        return $this->statement[$piece] ?? $default;
    }
}