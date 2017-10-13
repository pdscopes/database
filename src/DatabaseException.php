<?php

namespace MadeSimple\Database;

class DatabaseException extends \RuntimeException
{
    const ERROR_CONNECTION = 1;
    const ERROR_COMPILE    = 2;
    const ERROR_EXECUTION  = 3;

    public $errorInfo;
}