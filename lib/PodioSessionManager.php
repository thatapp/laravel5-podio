<?php

abstract class PodioSessionManager
{
    /**
     * Returns an PodioOAuth class from session
     * @return PodioOAuth
     */
    public abstract function get();


    /**
     * Save the current PodioOAuth to the session
     * @return PodioOAuth
     */
    public abstract function set($oauth);

}
