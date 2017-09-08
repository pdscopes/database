<?php

namespace MadeSimple\Database\Example\Repository;

use MadeSimple\Database\Example\Entity\Comment;
use MadeSimple\Database\Pool;
use MadeSimple\Database\Repository;

class CommentRepository extends Repository
{
    public function __construct(Pool $pool)
    {
        parent::__construct($pool, Comment::class);
    }
}