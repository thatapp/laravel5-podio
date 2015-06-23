<?php

class PodioItemCollection extends PodioCollection
{
    public $filtered;
    public $total;

    /**
     * @param $podio
     * @param An|array $items An array of PodioItem objects
     * @param $filtered Count of items in current selected
     * @param $total Total number of items if no filters were to apply
     */
    public function __construct($podio, $items = array(), $filtered = null, $total = null)
    {
        $this->filtered = $filtered;
        $this->total = $total;

        parent::__construct($podio, $items);
    }

    // Array access
    public function offsetSet($offset, $value)
    {
        if (!is_array($value) && !array_key_exists("item_id", $value)) {
            throw new PodioDataIntegrityError("Objects in PodioItemCollection must be of array of PodioItem");
        }

        parent::offsetSet($offset, $value);
    }

}
