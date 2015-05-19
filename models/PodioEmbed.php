<?php

/**
 * @see https://developers.podio.com/doc/embeds
 */
class PodioEmbed extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('embed_id', 'integer', array('id' => true));
        $this->property('original_url', 'string');
        $this->property('resolved_url', 'string');
        $this->property('type', 'string');
        $this->property('title', 'string');
        $this->property('description', 'string');
        $this->property('created_on', 'datetime');
        $this->property('provider_name', 'string');
        $this->property('embed_html', 'string');
        $this->property('embed_height', 'integer');
        $this->property('embed_width', 'integer');

        $this->has_many('files', 'File');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/embeds/add-an-embed-726483
     */
    public function create($attributes = array())
    {
        return $this->member($this->podio->post("/embed/", $attributes));
    }

}
