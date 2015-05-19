<?php

/**
 * @see https://developers.podio.com/doc/recurrence
 */
class PodioRecurrence extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('recurrence_id', 'integer', array('id' => true));
        $this->property('name', 'string');
        $this->property('config', 'hash');
        $this->property('step', 'integer');
        $this->property('until', 'date');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/recurrence/get-recurrence-3415545
     */
    public function get_for($ref_type, $ref_id)
    {
        return $this->member($this->podio->get("/recurrence/{$ref_type}/{$ref_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/recurrence/create-or-update-recurrence-3349957
     */
    public function create($ref_type, $ref_id, $attributes = array())
    {
        return $this->podio->put("/recurrence/{$ref_type}/{$ref_id}", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/recurrence/create-or-update-recurrence-3349957
     */
    public function update($ref_type, $ref_id, $attributes = array())
    {
        return $this->podio->put("/recurrence/{$ref_type}/{$ref_id}", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/recurrence/delete-recurrence-3349970
     */
    public function delete($ref_type, $ref_id)
    {
        return $this->podio->delete("/recurrence/{$ref_type}/{$ref_id}");
    }

}
