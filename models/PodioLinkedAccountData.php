<?php

class PodioLinkedAccountData extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('id', 'integer');
        $this->property('type', 'string');
        $this->property('info', 'string');
        $this->property('url', 'string');

        $this->init($attributes);
    }

}
