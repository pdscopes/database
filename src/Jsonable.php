<?php

namespace MadeSimple\Database;

interface Jsonable
{
    /**
     * Convert instance to a JSON string.
     *
     * @param int $options
     * @param int $depth
     * @return string
     */
    public function toJson($options = 0, $depth = 512);
}