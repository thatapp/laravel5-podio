<?php

class ApiHelper
{
    private $podio;

    public function __construct($podio) {
        $this->podio = $podio;
    }

    public function __get($field) {
        $field = "Podio" . $field;
        return new $field($this->podio);
    }
}