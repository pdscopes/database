<?php

namespace MadeSimple\Database\Relation;

use MadeSimple\Database\Entity;
use MadeSimple\Database\Relation;
use PDO;
use ReflectionClass;

/**
 * Class OneToMany
 *
 * @package MadeSimple\Database\Relation
 * @author  Peter Scopes <peter.scopes@gmail.com>
 */
class ToMany extends Relation
{
    /**
     * @return null|Entity[]
     */
    public function fetch()
    {
        $reflection = new ReflectionClass($this->relative);
        $statement  = $this->query()->execute();

        if (!$statement) {
            return null;
        }

        $items = [];
        while (($row = $statement->fetch(PDO::FETCH_ASSOC))) {
            $items[] = $reflection->newInstanceArgs([$row]);
        }

        return $items;
    }
}