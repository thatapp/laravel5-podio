<?php

/**
 * @see https://developers.podio.com/doc/applications
 */
class PodioAppField extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('field_id', 'integer', array('id' => true));
        $this->property('type', 'string');
        $this->property('external_id', 'string');
        $this->property('config', 'hash');
        $this->property('status', 'string');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/applications/add-new-app-field-22354
     */
    public function create($app_id, $attributes = array())
    {
        $body = $this->podio->post("/app/{$app_id}/field/", $attributes)->json_body();
        return $body['field_id'];
    }

    /**
     * @see https://developers.podio.com/doc/applications/get-app-field-22353
     */
    public function get($app_id, $field_id)
    {
        return $this->member($this->podio->get("/app/{$app_id}/field/{$field_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/applications/update-an-app-field-22356
     */
    public function update($app_id, $field_id, $attributes = array())
    {
        return $this->podio->put("/app/{$app_id}/field/{$field_id}", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/applications/delete-app-field-22355
     */
    public function delete($app_id, $field_id, $attributes = array())
    {
        $body = $this->podio->delete("/app/{$app_id}/field/{$field_id}", $attributes)->json_body();
        return $body['revision'];
    }


}
