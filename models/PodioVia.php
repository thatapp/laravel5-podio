<?php

class PodioVia extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('id', 'integer');
        $this->property('auth_client_id', 'integer');
        $this->property('name', 'string');
        $this->property('url', 'string');
        $this->property('display', 'boolean');

        $this->init($attributes);
    }
}
