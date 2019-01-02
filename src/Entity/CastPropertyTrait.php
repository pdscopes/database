<?php

namespace MadeSimple\Database\Entity;

/**
 * @property array $casts List of properties to be cast.
 */
trait CastPropertyTrait
{
    /**
     * @param string $property
     * @param mixed  $default
     *
     * @return mixed
     */
    public function cast(string $property, $default = null)
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
                if (is_callable($this->casts[$property])) {
                    return call_user_func($this->casts[$property], $this->{$property});
                } else {
                    return $default;
                }
        }
    }
}