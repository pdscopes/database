<?php

namespace MadeSimple\Database\Entity;

trait PropertiesToArrayTrait
{
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