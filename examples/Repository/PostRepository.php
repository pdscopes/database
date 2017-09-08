<?php

namespace MadeSimple\Database\Example\Repository;

use MadeSimple\Database\Example\Entity\Post;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Repository;

class PostRepository extends Repository
{
    public function __construct(Pool $pool)
    {
        parent::__construct($pool, Post::class);
    }
}