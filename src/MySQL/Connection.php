<?php

namespace MadeSimple\Database\MySQL;

use MadeSimple\Database\MySQL\Statement\Table\Create;
use Psr\Log\LoggerInterface;

/**
 * Class Connection
 *
 * @package MadeSimple\Database\Connection
 * @author  Peter Scopes
 */
class Connection extends \MadeSimple\Database\Connection
{
    /**
     * Connection constructor.
     *
     * @param \PDO             $pdo
     * @param LoggerInterface  $logger
     */
    public function __construct(\PDO $pdo, LoggerInterface $logger)
    {
        parent::__construct($pdo, $logger, '`');
    }

    /**
     * @param string   $name
     * @param \Closure $callable
     *
     * @return \MadeSimple\Database\Statement
     */
    public  function create($name, \Closure $callable)
    {
        $statement = new Create($this, $this->logger, $name);
        call_user_func_array($callable, [$statement]);

        return $statement;
    }
}