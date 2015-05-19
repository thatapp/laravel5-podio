<?php

/**
 * @see https://developers.podio.com/doc/subscriptions
 */
class PodioSubscription extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('started_on', 'datetime');
        $this->property('notifications', 'integer');

        $this->has_one('ref', 'Reference');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/subscriptions/get-subscription-by-id-22446
     */
    public function get($subscription_id)
    {
        return $this->member($this->podio->get("/subscription/{$subscription_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/subscriptions/get-subscription-by-reference-22408
     */
    public function get_for($ref_type, $ref_id)
    {
        return $this->member($this->podio->get("/subscription/{$ref_type}/{$ref_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/subscriptions/subscribe-22409
     */
    public function create($ref_type, $ref_id)
    {
        return $this->podio->post("/subscription/{$ref_type}/{$ref_id}")->json_body();
    }

    /**
     * @see https://developers.podio.com/doc/subscriptions/unsubscribe-by-id-22445
     */
    public function delete($subscription_id)
    {
        return $this->podio->delete("/subscription/{$subscription_id}");
    }

    /**
     * @see https://developers.podio.com/doc/subscriptions/unsubscribe-by-reference-22410
     */
    public function delete_for($ref_type, $ref_id)
    {
        return $this->podio->delete("/subscription/{$ref_type}/{$ref_id}");
    }

}
