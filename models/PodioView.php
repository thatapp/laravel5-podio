<?php

/**
 * @see https://developers.podio.com/doc/filters
 */
class PodioView extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('view_id', 'integer', array('id' => true));
        $this->property('name', 'string');
        $this->property('created_on', 'datetime');
        $this->property('items', 'integer');
        $this->property('sort_by', 'string');
        $this->property('sort_desc', 'string');
        $this->property('filters', 'hash');
        $this->property('layout', 'string');
        $this->property('fields', 'hash');

        $this->has_one('created_by', 'ByLine');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/views/create-view-27453
     */
    public function create($app_id, $attributes = array())
    {
        $body = $this->podio->post("/view/app/{$app_id}/", $attributes)->json_body();
        return $body['view_id'];
    }

    /**
     * @see https://developers.podio.com/doc/views/get-view-27450
     */
    public function get($view_id)
    {
        return $this->member($this->podio->get("/view/{$view_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/views/get-views-27460
     */
    public function get_for_app($app_id)
    {
        return $this->listing($this->podio->get("/view/app/{$app_id}/"));
    }

    /**
     * @see https://developers.podio.com/doc/views/get-last-view-27663
     */
    public function get_last($app_id)
    {
        return $this->member($this->podio->get("/view/app/{$app_id}/last"));
    }

    /**
     * @see https://developers.podio.com/doc/views/update-last-view-5988251
     */
    public function update_last($app_id, $attributes = array())
    {
        return $this->podio->put("/view/app/{$app_id}/last", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/views/delete-view-27454
     */
    public function delete($view_id)
    {
        return $this->podio->delete("/view/{$view_id}");
    }

}
