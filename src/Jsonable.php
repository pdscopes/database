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
    public function toJson(int $options = 0, int $depth = 512);
}