<?php

/**
 * @see https://developers.podio.com/doc/space-members
 */
class PodioSpaceMember extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('role', 'string');
        $this->property('invited_on', 'datetime');
        $this->property('started_on', 'datetime');
        $this->property('ended_on', 'datetime');

        $this->has_one('user', 'User');
        $this->has_one('profile', 'Contact');
        $this->has_one('space', 'Space');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/space-members/get-space-membership-22397
     */
    public function get($space_id, $user_id)
    {
        return $this->member($this->podio->get("/space/{$space_id}/member/{$user_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/space-members/get-active-members-of-space-22395
     */
    public function get_all($space_id)
    {
        return $this->listing($this->podio->get("/space/{$space_id}/member/"));
    }

    /**
     * @see https://developers.podio.com/doc/space-members/get-space-members-by-role-68043
     */
    public function get_by_role($space_id, $role)
    {
        return $this->listing($this->podio->get("/space/{$space_id}/member/{$role}/"));
    }

    /**
     * @see https://developers.podio.com/doc/space-members/end-space-memberships-22399
     */
    public function delete($space_id, $user_ids)
    {
        return $this->podio->delete("/space/{$space_id}/member/{$user_ids}");
    }

    /**
     * @see https://developers.podio.com/doc/space-members/update-space-memberships-22398
     */
    public function update($space_id, $user_ids, $attributes = array())
    {
        return $this->podio->put("/space/{$space_id}/member/{$user_ids}", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/space-members/join-space-1927286
     */
    public function join($space_id)
    {
        return $this->podio->post("/space/{$space_id}/join");
    }

    /**
     * @see https://developers.podio.com/doc/space-members/add-member-to-space-1066259
     */
    public function add($space_id, $attributes = array())
    {
        return $this->podio->post("/space/{$space_id}/member/", $attributes);
    }

}
