<?php

/**
 * @see https://developers.podio.com/doc/actions
 */
class PodioAction extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('action_id', 'integer', array('id' => true));
        $this->property('type', 'string');
        $this->property('data', 'hash');
        $this->property('text', 'string');

        $this->has_many('comments', 'Comment');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/actions/get-action-1701120
     */
    public function get($action_id)
    {
        return $this->member($this->podio->get("/action/{$action_id}"));
    }

}
