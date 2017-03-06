<?php

namespace MadeSimple\Database\QueryBuilder;

/**
 * Class WhereTrait
 *
 * @package MadeSimple\Database\QueryBuilder
 * @author  Peter Scopes
 */
trait WhereTrait
{
    /**
     * @var Clause
     */
    protected $where;

    /**
     * @param array $parameters Associated mapping of parameter name to value
     *
     * @return static
     */
    public abstract function setParameters(array $parameters);

    /**
     * @param string|\Closure $clause    A where clause or closure
     * @param array|mixed     $parameter A single, array of, or associated mapping of parameters
     *
     * @return static
     */
    public function where($clause, $parameter = null)
    {
        $this->where->where($clause);
        if (null !== $parameter) {
            $this->setParameters(!is_array($parameter) ? [$parameter] : $parameter);
        }

        return $this;
    }

    /**
     * @param string|\Closure $clause    A where clause or closure
     * @param array|mixed     $parameter A single, array of, or associated mapping of parameters
     *
     * @return static
     */
    public function andWhere($clause, $parameter = null)
    {
        $this->where->andX($clause);
        if (null !== $parameter) {
            $this->setParameters(!is_array($parameter) ? [$parameter] : $parameter);
        }

        return $this;
    }

    /**
     * @param string|\Closure $clause    A where clause or closure
     * @param array|mixed     $parameter A single, array of, or associated mapping of parameters
     *
     * @return static
     */
    public function orWhere($clause, $parameter = null)
    {
        $this->where->orX($clause);
        if (null !== $parameter) {
            $this->setParameters(!is_array($parameter) ? [$parameter] : $parameter);
        }

        return $this;
    }
}