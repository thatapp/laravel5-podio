<?php

/**
 * @see https://developers.podio.com/doc/tasks
 */
class PodioTaskLabel extends PodioObject
{
    const DEFAULT_COLOR = 'E9E9E9';

    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('label_id', 'integer', array('id' => true));
        $this->property('text', 'string');
        $this->property('color', 'string');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/tasks/create-label-151265
     */
    public function create($attributes = array())
    {
        if (!isset($attributes['color'])) {
            $attributes['color'] = DEFAULT_COLOR;
        }
        $body = $this->podio->post("/task/label/", $attributes)->json_body();
        $body['label_id'];
    }

    /**
     * @see https://developers.podio.com/doc/tasks/get-labels-151534
     */
    public function get_all()
    {
        return $this->listing($this->podio->get("/task/label"));
    }

    /**
     * @see https://developers.podio.com/doc/tasks/delete-label-151302
     */
    public function delete($label_id)
    {
        return $this->podio->delete("/task/label/{$label_id}");
    }

    /**
     * @see https://developers.podio.com/doc/tasks/update-task-labels-151769
     */
    public function update($label_id, $attributes = array())
    {
        return $this->podio->put("/task/label/{$label_id}", $attributes);
    }

}
