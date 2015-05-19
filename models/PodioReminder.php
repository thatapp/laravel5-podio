<?php

/**
 * @see https://developers.podio.com/doc/reminders
 */
class PodioReminder extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('reminder_id', 'integer', array('id' => true));
        $this->property('reminder_delta', 'integer');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/reminders/get-reminder-3415569
     */
    public function get_for($ref_type, $ref_id)
    {
        return $this->member($this->podio->get("/reminder/{$ref_type}/{$ref_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/reminders/create-or-update-reminder-3315055
     */
    public function create($ref_type, $ref_id, $attributes = array())
    {
        return $this->podio->put("/reminder/{$ref_type}/{$ref_id}", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/reminders/create-or-update-reminder-3315055
     */
    public function update($ref_type, $ref_id, $attributes = array())
    {
        return $this->podio->put("/reminder/{$ref_type}/{$ref_id}", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/reminders/snooze-reminder-3321049
     */
    public function snooze($ref_type, $ref_id)
    {
        return $this->podio->post("/reminder/{$ref_type}/{$ref_id}/snooze");
    }

    /**
     * @see https://developers.podio.com/doc/reminders/delete-reminder-3315117
     */
    public function delete($ref_type, $ref_id)
    {
        return $this->podio->delete("/reminder/{$ref_type}/{$ref_id}");
    }

}
