<?php

/**
 * @see https://developers.podio.com/doc/users
 */
class PodioUserMail extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('mail', 'string');
        $this->property('verified', 'boolean');
        $this->property('primary', 'boolean');
        $this->property('disabled', 'boolean');

        $this->init($attributes);
    }

}
