<?php

/**
 * @see https://developers.podio.com/doc/search
 */
class PodioSearchResult extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('id', 'integer');
        $this->property('type', 'string');
        $this->property('rank', 'integer');
        $this->property('title', 'string');
        $this->property('created_on', 'datetime');
        $this->property('link', 'string');
        $this->property('app', 'hash');
        $this->property('org', 'hash');
        $this->property('space', 'hash');

        $this->has_one('created_by', 'ByLine');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/search/search-in-app-4234651
     */
    public function app($app_id, $attributes = array())
    {
        return $this->listing($this->podio->post("/search/app/{$app_id}/", $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/search/search-in-space-22479
     */
    public function space($space_id, $attributes = array())
    {
        return $this->listing($this->podio->post("/search/space/{$space_id}/", $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/search/search-in-organization-22487
     */
    public function org($org_id, $attributes = array())
    {
        return $this->listing($this->podio->post("/search/org/{$org_id}/", $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/search/search-globally-22488
     */
    public function search($attributes = array())
    {
        return $this->listing($this->podio->post("/search/", $attributes));
    }

    /**
     * Search in app and space. Only applicable to platform
     */
    public function search_app_and_space($space_id, $app_id, $attributes = array())
    {
        return $this->listing($this->podio->post("/search/app/{$app_id}/space/{$space_id}", $attributes));
    }

}
