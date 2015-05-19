<?php

/**
 * @see https://developers.podio.com/doc/forms
 */
class PodioForm extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('form_id', 'integer', array('id' => true));
        $this->property('app_id', 'integer');
        $this->property('space_id', 'integer');
        $this->property('status', 'string');
        $this->property('settings', 'hash');
        $this->property('domains', 'array');
        $this->property('fields', 'array');
        $this->property('attachments', 'boolean');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/forms/activate-form-1107439
     */
    public function activate($form_id)
    {
        return $this->podio->post("/form/{$form_id}/activate");
    }

    /**
     * @see https://developers.podio.com/doc/forms/deactivate-form-1107378
     */
    public function deactivate($form_id)
    {
        return $this->podio->post("/form/{$form_id}/deactivate");
    }

    /**
     * @see https://developers.podio.com/doc/forms/create-form-53803
     */
    public function create($app_id, $attributes = array())
    {
        return $this->member($this->podio->post("/form/app/{$app_id}/", $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/forms/delete-from-53810
     */
    public function delete($form_id)
    {
        return $this->podio->delete("/form/{$form_id}");
    }

    /**
     * @see https://developers.podio.com/doc/forms/get-form-53754
     */
    public function get($form_id)
    {
        return $this->member($this->podio->get("/form/{$form_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/forms/get-forms-53771
     */
    public function get_for_app($app_id)
    {
        return $this->listing($this->podio->get("/form/app/{$app_id}/"));
    }

    /**
     * @see https://developers.podio.com/doc/forms/update-form-53808
     */
    public function update($form_id, $attributes = array())
    {
        return $this->podio->put("/form/{$form_id}", $attributes);
    }

}
