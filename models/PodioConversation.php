<?php

/**
 * @see https://developers.podio.com/doc/conversations
 */
class PodioConversation extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('conversation_id', 'integer', array('id' => true));
        $this->property('subject', 'string');

        // Creating conversations
        $this->property('text', 'string');
        $this->property('participants', 'array');

        // Getting conversations
        $this->property('created_on', 'datetime');

        $this->has_one('ref', 'Reference');
        $this->has_one('embed', 'Embed', array('json_value' => 'embed_id', 'json_target' => 'embed_id'));
        $this->has_one('embed_file', 'File', array('json_value' => 'file_id', 'json_target' => 'embed_file_id'));
        $this->has_one('created_by', 'ByLine');
        $this->has_many('files', 'File', array('json_value' => 'file_id', 'json_target' => 'file_ids'));
        $this->has_many('messages', 'ConversationMessage');
        $this->has_many('participants_full', 'ConversationParticipant');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/conversations/get-conversation-22369
     */
    public function get($conversation_id)
    {
        return $this->member($this->podio->get("/conversation/{$conversation_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/conversations/get-conversations-34822801
     */
    public function get_all($attributes = array())
    {
        return $this->listing($this->podio->get("/conversation/", $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/conversations/get-conversations-on-object-22443
     */
    public function get_for($ref_type, $ref_id, $plugin)
    {
        return $this->listing($this->podio->get("/batch/{$ref_type}/{$ref_id}/"));
    }

    /**
     * @see https://developers.podio.com/doc/conversations/create-conversation-22441
     */
    public function create($attributes = array())
    {
        return $this->podio->post("/conversation/", $attributes)->json_body();
    }

    /**
     * @see https://developers.podio.com/doc/conversations/create-conversation-on-object-22442
     */
    public function create_for($ref_type, $ref_id, $attributes = array())
    {
        return $this->podio->post("/conversation/{$ref_type}/{$ref_id}/", $attributes)->json_body();
    }

    /**
     * @see https://developers.podio.com/doc/conversations/reply-to-conversation-22444
     */
    public function create_reply($conversation_id, $attributes = array())
    {
        $body = $this->podio->post("/conversation/{$conversation_id}/reply", $attributes)->json_body();
        return $body['message_id'];
    }

    /**
     * @see https://developers.podio.com/doc/conversations/add-participants-384261
     */
    public function add_participant($conversation_id, $attributes = array())
    {
        return $this->podio->post("/conversation/{$conversation_id}/participant/", $attributes)->json_body();
    }

}
