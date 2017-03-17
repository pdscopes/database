<?php

namespace MadeSimple\Database\Relationship;

use MadeSimple\Database\Relationship;

/**
 * Class Relational
 *
 * @package MadeSimple\Database\Relationship
 * @author  Peter Scopes
 */
trait Relational
{
    /**
     * @var Relationship[]
     */
    protected $relationships = [];

    /**
     * @param string $relationship
     * @param array  $args
     *
     * @return Relationship
     */
    public function relation($relationship, ... $args)
    {
        // If the relationship has not already been fetched
        if (!array_key_exists($relationship, $this->relationships)) {
            if (!method_exists($this, $relationship)) {
                throw new \InvalidArgumentException('No such relationship: ' . $relationship);
            }

            $this->relationships[$relationship] = call_user_func_array([$this, $relationship], $args)->fetch();
        }

        return $this->relationships[$relationship];
    }
}