<?php

namespace MadeSimple\Database\SQLite;

/**
 * Class Connection
 *
 * @package MadeSimple\Database\Connection
 * @author  Peter Scopes
 */
class Connection extends \MadeSimple\Database\Connection
{
    /**
     * SQLite constructor.
     *
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->columnQuote = '"';
    }

    /**
     * @param string   $name
     * @param \Closure $callable
     *
     * @return \MadeSimple\Database\Statement
     */
    public  function create($name, \Closure $callable)
    {
        $statement = new \MadeSimple\Database\SQLite\Statement\Table\Create($this, $name);
        call_user_func_array($callable, [$statement]);

        return $statement;
    }
}