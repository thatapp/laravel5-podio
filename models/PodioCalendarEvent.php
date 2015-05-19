<?php

/**
 * @see https://developers.podio.com/doc/calendar
 */
class PodioCalendarEvent extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('id', 'integer');
        $this->property('type', 'string');
        $this->property('group', 'string');
        $this->property('title', 'string');
        $this->property('description', 'string');
        $this->property('location', 'string');
        $this->property('status', 'string');
        $this->property('link', 'string');
        $this->property('start', 'datetime');
        $this->property('end', 'datetime');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/actions/get-action-1701120
     */
    public function get($attributes = array())
    {
        return $this->listing($this->podio->get("/calendar/", $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/calendar/get-space-calendar-22459
     */
    public function get_for_space($space_id, $attributes = array())
    {
        return $this->listing($this->podio->get("/calendar/space/{$space_id}/", $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/calendar/get-app-calendar-22460
     */
    public function get_for_app($app_id, $attributes = array())
    {
        return $this->listing($this->podio->get("/calendar/app/{$app_id}/", $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/calendar/get-global-calendar-as-ical-22513
     */
    public function ical($user_id, $token)
    {
        return $this->podio->get("/calendar/ics/{$user_id}/{$token}/")->body;
    }

    /**
     * @see https://developers.podio.com/doc/calendar/get-space-calendar-as-ical-22514
     */
    public function ical_for_space($space_id, $user_id, $token)
    {
        return $this->podio->get("/calendar/space/{$space_id}/ics/{$user_id}/{$token}/")->body;
    }

    /**
     * @see https://developers.podio.com/doc/calendar/get-app-calendar-as-ical-22515
     */
    public function ical_for_app($app_id, $user_id, $token)
    {
        return $this->podio->get("/calendar/app/{$app_id}/ics/{$user_id}/{$token}/")->body;
    }

    /**
     * @see https://developers.podio.com/doc/calendar/get-calendar-summary-1609256
     */
    public function get_summary($attributes = array())
    {
        $result = $this->podio->get("/calendar/summary", $attributes)->json_body();
        $result['today']['events'] = $this->listing($result['today']['events']);
        $result['upcoming']['events'] = $this->listing($result['upcoming']['events']);
        return $result;
    }

    /**
     * @see https://developers.podio.com/doc/calendar/get-calendar-summary-for-personal-1657903
     */
    public function get_summary_personal($attributes = array())
    {
        $result = $this->podio->get("/calendar/personal/summary", $attributes)->json_body();
        $result['today']['events'] = $this->listing($result['today']['events']);
        $result['upcoming']['events'] = $this->listing($result['upcoming']['events']);
        return $result;
    }

    /**
     * @see https://developers.podio.com/doc/calendar/get-calendar-summary-for-space-1609328
     */
    public function get_summary_for_space($space_id, $attributes = array())
    {
        $result = $this->podio->get("/calendar/space/{$space_id}/summary", $attributes)->json_body();
        $result['today']['events'] = $this->listing($result['today']['events']);
        $result['upcoming']['events'] = $this->listing($result['upcoming']['events']);
        return $result;
    }

}
