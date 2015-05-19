<?php

class PodioSessionManager
{
    private static $cached_clients = array();

    public function get($key)
    {
        if (array_key_exists($key, self::$cached_clients)) {
            return self::$cached_clients[$key];
        }
        return null;
    }

    public function set($key, $podio)
    {
        self::$cached_clients[$key] = $podio;
    }

}
