<?php

use Illuminate\Support\Facades\Session;

class PodioSessionManager
{

    public function get()
    {
        if (Session::Has('podio-entry-access-token')) {
            return new PodioOAuth(
                Session::get('podio-entry-access-token'),
                Session::get('podio-entry-refresh-token'),
                Session::get('podio-entry-expires-in'),
                Session::get('podio-entry-ref')
            );
        }
        return new PodioOAuth();
    }

    public function set($oauth)
    {
        Session::put('podio-entry-access-token', $oauth->access_token);
        Session::put('podio-entry-refresh-token', $oauth->refresh_token);
        Session::put('podio-entry-expires-in', $oauth->expires_in);
        Session::put('podio-entry-ref', $oauth->ref);
    }

}
