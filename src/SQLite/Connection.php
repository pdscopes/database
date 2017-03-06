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
     * @param null $callable
     *
     * @return Statement\Table\Create
     */
    public  function create($callable = null)
    {
        $alter = new Statement\Table\Create($this);
        if ($callable instanceof \Closure) {
            call_user_func_array($callable, [$alter]);
        }
        return $alter;
    }
}