<?php

class PodioOAuth
{
    public $client_id;
    public $client_secret;

    public $access_token;
    public $refresh_token;
    public $expires_in;
    public $ref;

    public function __construct($client_id = null, $client_secret = null, $access_token = null, $refresh_token = null, $expires_in = null, $ref = null)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
        $this->expires_in = $expires_in;
        $this->ref = $ref;
    }
}
