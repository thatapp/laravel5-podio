<?php

/**
 * @see https://developers.podio.com/doc/users
 */
class PodioUserStatus extends PodioObject
{

    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('properties', 'hash');
        $this->property('inbox_new', 'integer');
        $this->property('calendar_code', 'string');
        $this->property('task_mail', 'string');
        $this->property('mailbox', 'string');

        $this->has_one('user', 'User');
        $this->has_one('profile', 'Contact');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/users/get-user-status-22480
     */
    public function get()
    {
        return $this->podio->get("/user/status");
    }

}
