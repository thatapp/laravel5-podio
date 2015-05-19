<?php

/**
 * @see https://developers.podio.com/doc/status
 */
class PodioStatus extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('status_id', 'integer', array('id' => true));
        $this->property('value', 'string');
        $this->property('rich_value', 'string');
        $this->property('link', 'string');
        $this->property('ratings', 'hash');
        $this->property('subscribed', 'boolean');
        $this->property('user_ratings', 'hash');
        $this->property('created_on', 'datetime');

        $this->has_one('created_by', 'ByLine');
        $this->has_one('created_via', 'Via');
        $this->has_one('embed', 'Embed', array('json_value' => 'embed_id', 'json_target' => 'embed_id'));
        $this->has_one('embed_file', 'File', array('json_value' => 'file_id', 'json_target' => 'embed_file_id'));
        $this->has_many('comments', 'Comment');
        $this->has_many('conversations', 'Conversation');
        $this->has_many('tasks', 'Task');
        $this->has_many('shares', 'AppMarketShare');
        $this->has_many('files', 'File', array('json_value' => 'file_id', 'json_target' => 'file_ids'));
        $this->has_many('questions', 'Question');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/status/add-new-status-message-22336
     */
    public function create($space_id, $attributes = array())
    {
        return $this->member($this->podio->post("/status/space/{$space_id}/", $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/status/get-status-message-22337
     */
    public function get($status_id)
    {
        return $this->member($this->podio->get("/status/{$status_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/status/delete-a-status-message-22339
     */
    public function delete($status_id)
    {
        return $this->podio->delete("/status/{$status_id}");
    }

}
