<?php

/**
 * @see https://developers.podio.com/doc/items
 */
class PodioItemRevision extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('revision', 'integer', array('id' => true));
        $this->property('app_revision', 'integer');
        $this->property('created_on', 'datetime');

        $this->has_one('created_by', 'ByLine');
        $this->has_one('created_via', 'Via');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/items/get-item-revision-22373
     */
    public function get($item_id, $revision_id)
    {
        return $this->member($this->podio->get("/item/{$item_id}/revision/{$revision_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/items/get-item-revision-22373
     */
    public function get_for($item_id)
    {
        return $this->listing($this->podio->get("/item/{$item_id}/revision/"));
    }

}
