<?php

namespace MadeSimple\Database\Statement;

/**
 * Class ExecutionException
 *
 * @package MadeSimple\Database\Statement
 * @author  Peter Scopes
 */
class ExecutionException extends \RuntimeException
{
    /**
     * @var string
     */
    protected $sql;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * ExecutionException constructor.
     *
     * @param \PDOException $previous
     * @param string        $sql
     * @param array         $parameters
     */
    public function __construct(\PDOException $previous, $sql, $parameters)
    {
        parent::__construct("Failed to execute SQL: " . $sql, 0, $previous);
        $this->sql = $sql;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}