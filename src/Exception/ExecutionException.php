<?php

namespace MadeSimple\Database\Exception;

use Exception;

/**
 * Class ExecutionException
 *
 * @package MadeSimple\Database\Exception
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
     * @param Exception $previous
     * @param string    $sql
     * @param array     $parameters
     */
    public function __construct(Exception $previous, $sql, $parameters)
    {
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
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