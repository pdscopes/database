<?php

namespace MadeSimple\Database\Query;

class Raw
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @param string $value
     * @return static
     */
    public static function create($value)
    {
        return new static($value);
    }

    /**
     * Raw constructor.
     *
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = (string) $value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}