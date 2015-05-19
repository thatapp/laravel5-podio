<?php

/**
 * @see https://developers.podio.com/doc/comments
 */
class PodioComment extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('comment_id', 'integer', array('id' => true));
        $this->property('value', 'string');
        $this->property('rich_value', 'string');
        $this->property('external_id', 'integer');
        $this->property('space_id', 'integer');
        $this->property('created_on', 'datetime');
        $this->property('like_count', 'integer');
        $this->property('is_liked', 'boolean');

        $this->has_one('created_by', 'ByLine');
        $this->has_one('created_via', 'Via');
        $this->has_one('ref', 'Reference');

        $this->has_one('embed', 'Embed', array('json_value' => 'embed_id', 'json_target' => 'embed_id'));
        $this->has_one('embed_file', 'File', array('json_value' => 'file_id', 'json_target' => 'embed_file_id'));
        $this->has_many('files', 'File', array('json_value' => 'file_id', 'json_target' => 'file_ids'));
        $this->has_many('questions', 'Question');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/comments/get-a-comment-22345
     */
    public function get($comment_id)
    {
        return $this->member($this->podio->get("/comment/{$comment_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/comments/get-comments-on-object-22371
     */
    public function get_for($ref_type, $ref_id, $attributes = array())
    {
        return $this->listing($this->podio->get("/comment/{$ref_type}/{$ref_id}/", $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/comments/delete-a-comment-22347
     */
    public function delete($comment_id)
    {
        return $this->podio->delete("/comment/{$comment_id}");
    }

    /**
     * @see https://developers.podio.com/doc/comments/add-comment-to-object-22340
     */
    public function create($ref_type, $ref_id, $attributes = array(), $options = array())
    {
        $url = $this->podio->url_with_options("/comment/{$ref_type}/{$ref_id}", $options);
        $body = $this->podio->post($url, $attributes)->json_body();
        return $body['comment_id'];
    }

    /**
     * @see https://developers.podio.com/doc/comments/update-a-comment-22346
     */
    public function update($comment_id, $attributes = array())
    {
        return $this->podio->put("/comment/{$comment_id}", $attributes);
    }

}
