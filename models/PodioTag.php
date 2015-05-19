<?php

/**
 * @see https://developers.podio.com/doc/tags
 */
class PodioTag extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('count', 'integer');
        $this->property('text', 'string');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/tags/create-tags-22464
     */
    public function create($ref_type, $ref_id, $attributes = array())
    {
        return $this->podio->post("/tag/{$ref_type}/{$ref_id}/", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/tags/update-tags-39859
     */
    public function update($ref_type, $ref_id, $attributes = array())
    {
        return $this->podio->put("/tag/{$ref_type}/{$ref_id}/", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/tags/remove-tag-22465
     */
    public function delete($ref_type, $ref_id, $attributes = array())
    {
        return $this->podio->delete("/tag/{$ref_type}/{$ref_id}/", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/tags/get-tags-on-app-22467
     */
    public function get_for_app($app_id, $attributes = array())
    {
        return $this->listing($this->podio->get("/tag/app/{$app_id}/", $attributes));
    }

}
