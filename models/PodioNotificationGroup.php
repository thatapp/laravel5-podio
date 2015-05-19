<?php

/**
 * @see https://developers.podio.com/doc/notifications
 */
class PodioNotificationGroup extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->has_one('context', 'NotificationContext');
        $this->has_many('notifications', 'Notification');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/notifications/get-notification-v2-2973737
     */
    public function get($notification_id)
    {
        return $this->member($this->podio->get("/notification/{$notification_id}/v2"));
    }

    /**
     * @see https://developers.podio.com/doc/notifications/get-notifications-290777
     */
    public function get_all($attributes = array())
    {
        return $this->listing($this->podio->get("/notification/", $attributes));
    }

}
