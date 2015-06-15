<?php

/**
 * Collection for managing a list of PodioAppField objects.
 */
class PodioAppFieldCollection extends PodioFieldCollection
{

    /**
     * Constructor. Pass in either decoded JSON from an API request
     * or an array of PodioAppField objects.
     */
    public function __construct($podio, $attributes)
    {

        // Make default array into array of proper objects
        $fields = array();

        foreach ($attributes as $field_attributes) {
            $field = is_object($field_attributes) ? $field_attributes : new PodioAppField($podio, $field_attributes);
            $fields[] = $field;
        }

        // Add to internal storage
        parent::__construct($podio, $fields);
    }

    /**
     * Array access. Add field to collection.
     */
    public function offsetSet($offset, $field)
    {
        if (!is_a($field, 'PodioAppField')) {
            throw new PodioDataIntegrityError("Objects in PodioAppFieldCollection must be of class PodioAppField");
        }

        parent::offsetSet($offset, $field);
    }

}
