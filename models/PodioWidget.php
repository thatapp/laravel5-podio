<?php

/**
 * @see https://developers.podio.com/doc/widgets
 */
class PodioWidget extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('widget_id', 'integer', array('id' => true));
        $this->property('type', 'string');
        $this->property('title', 'string');
        $this->property('config', 'hash');
        $this->property('rights', 'array');
        $this->property('data', 'hash'); // Only for get_for() method

        $this->has_one('created_by', 'ByLine');
        $this->property('created_on', 'datetime');
        $this->has_one('ref', 'Reference');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/widgets/create-widget-22491
     */
    public function create($ref_type, $ref_id, $attributes = array())
    {
        return $this->member($this->podio->post("/widget/{$ref_type}/{$ref_id}/", $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/widgets/delete-widget-22492
     */
    public function delete($widget_id)
    {
        return $this->podio->delete("/widget/{$widget_id}");
    }

    /**
     * @see https://developers.podio.com/doc/widgets/get-widget-22489
     */
    public function get($widget_id)
    {
        return $this->member($this->podio->get("/widget/{$widget_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/widgets/get-widgets-22494
     */
    public function get_for($ref_type, $ref_id)
    {
        return $this->listing($this->podio->get("/widget/{$ref_type}/{$ref_id}/"));
    }

    /**
     * @see https://developers.podio.com/doc/widgets/update-widget-22490
     */
    public function update($widget_id, $attributes = array())
    {
        return $this->podio->put("/widget/{$widget_id}", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/widgets/update-widget-order-22495
     */
    public function update_order($ref_type, $ref_id, $attributes = array())
    {
        return $this->podio->put("/widget/{$ref_type}/{$ref_id}/order", $attributes);
    }

}
