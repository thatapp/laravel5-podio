<?php

/**
 * @see https://developers.podio.com/doc/items
 */
class PodioItemDiff extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('field_id', 'integer');
        $this->property('type', 'string');
        $this->property('external_id', 'integer');
        $this->property('label', 'string');
        $this->property('from', 'array');
        $this->property('to', 'array');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/items/revert-item-revision-953195
     */
    public function revert($item_id, $revision_id)
    {
        $response = $this->podio->delete("/item/{$item_id}/revision/{$revision_id}");
        if ($response->body) {
            $json_body = $response->json_body();
            return $json_body['revision'];
        }
        return null;
    }

    /**
     * @see https://developers.podio.com/doc/items/get-item-revision-difference-22374
     */
    public function get_for($item_id, $revision_from_id, $revision_to_id)
    {
        return $this->listing($this->podio->get("/item/{$item_id}/revision/{$revision_from_id}/{$revision_to_id}"));
    }

}
