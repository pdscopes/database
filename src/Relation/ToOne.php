<?php

namespace MadeSimple\Database\Relation;

use MadeSimple\Database\Entity;
use MadeSimple\Database\Relation;
use PDO;
use ReflectionClass;

/**
 * Class OneToOne
 *
 * @package MadeSimple\Database\Relation
 * @author  Peter Scopes <peter.scopes@gmail.com>
 */
class ToOne extends Relation
{
    /**
     * @return null|Entity
     */
    public function fetch()
    {
        $reflection = new ReflectionClass($this->relative);
        $statement  = $this->query()->limit(1)->execute();

        if (!$statement) {
            return null;
        }

        if (($row = $statement->fetch(PDO::FETCH_ASSOC))) {
            return $reflection->newInstanceArgs([$this->entity->pool, $row]);
        }

        return null;
    }
}