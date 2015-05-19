<?php

/**
 * @see https://developers.podio.com/doc/ratings
 */
class PodioRating extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('rating_id', 'integer', array('id' => true));
        $this->property('type', 'string');
        $this->property('value', 'string');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/ratings/get-rating-22407
     */
    public function get_for_type_and_user($ref_type, $ref_id, $rating_type, $user_id)
    {
        return $this->member($this->podio->get("/rating/{$ref_type}/{$ref_id}/{$rating_type}/{$user_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/ratings/get-ratings-22375
     */
    public function get_for_type($ref_type, $ref_id, $rating_type)
    {
        return $this->podio->get("/rating/{$ref_type}/{$ref_id}/{$rating_type}")->json_body();
    }

    /**
     * @see https://developers.podio.com/doc/ratings/get-all-ratings-22376
     */
    public function get_for($ref_type, $ref_id)
    {
        return $this->podio->get("/rating/{$ref_type}/{$ref_id}")->json_body();
    }

    /**
     * @see https://developers.podio.com/doc/ratings/get-rating-own-84128
     */
    public function get_own_for_type($ref_type, $ref_id, $rating_type)
    {
        return $this->member($this->podio->get("/rating/{$ref_type}/{$ref_id}/{$rating_type}/self"));
    }

    /**
     * @see https://developers.podio.com/doc/ratings/add-rating-22377
     */
    public function create($ref_type, $ref_id, $rating_type, $attributes = array())
    {
        return $this->podio->post("/rating/{$ref_type}/{$ref_id}/{$rating_type}", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/ratings/remove-rating-22342
     */
    public function delete($ref_type, $ref_id, $rating_type)
    {
        return $this->podio->delete("/rating/{$ref_type}/{$ref_id}/{$rating_type}");
    }

}
