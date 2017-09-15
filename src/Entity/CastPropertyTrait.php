<?php

namespace MadeSimple\Database\Entity;

trait CastPropertyTrait
{

    /**
     * List of properties to be cast.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * @param      $property
     * @param null $default
     *
     * @return array|float|null|string
     */
    public function cast($property, $default = null)
    {
        if (!isset($this->{$property})) {
            return $default;
        }

        if (!isset($this->casts[$property])) {
            return $this->{$property};
        }

        switch ($this->casts[$property]) {
            case 'int':
            case 'integer':
                return (int) $this->{$property};

            case 'bool':
            case 'boolean':
                return (bool) $this->{$property};

            case 'double':
            case 'float':
            case 'real':
                return (float) $this->{$property};

            case 'string':
                return (string) $this->{$property};

            case 'array':
                return (array) $this->{$property};

            case 'json':
                return json_decode($this->{$property}, true);

            default:
                return $default;
        }
    }
}