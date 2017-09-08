<?php

namespace MadeSimple\Database\Example\Repository;

use MadeSimple\Database\Example\Entity\User;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Repository;

class UserRepository extends Repository
{
    public function __construct(Pool $pool)
    {
        parent::__construct($pool, User::class);
    }
}