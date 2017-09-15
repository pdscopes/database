<?php

namespace MadeSimple\Database\Entity;

trait PropertiesToArrayTrait
{

    /**
     * List of properties to be visible by default.
     *
     * @var array
     */
    protected $visible = [];

    /**
     * List of properties to be hidden by default.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * @param array $propertiesList
     * @return array
     */
    public function propertiesToArray(array $propertiesList)
    {
        $properties = [];
        foreach ($propertiesList as $property) {
            if (in_array($property, $this->hidden)) {
                continue;
            }
            $properties[$property] = $this->cast($property);
        }
        foreach ($this->visible as $property) {
            $properties[$property] = $this->cast($property);
        }

        return $properties;
    }

}