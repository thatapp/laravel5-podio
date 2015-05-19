<?php

/**
 * @see https://developers.podio.com/doc/users
 */
class PodioUser extends PodioObject
{

    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);

        $this->property('user_id', 'integer', array('id' => true));
        $this->property('profile_id', 'integer');
        $this->property('name', 'string');
        $this->property('link', 'string');
        $this->property('avatar', 'integer');
        $this->property('mail', 'string');
        $this->property('status', 'string');
        $this->property('locale', 'string');
        $this->property('timezone', 'string');
        $this->property('flags', 'array');
        $this->property('type', 'string');
        $this->property('created_on', 'datetime');

        $this->has_one('profile', 'Contact');
        $this->has_many('mails', 'UserMail');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/users/get-user-22378
     */
    public function get()
    {
        return $this->member($this->podio->get("/user"));
    }

    /**
     * @see https://developers.podio.com/doc/users/get-user-property-29798
     */
    public function get_property($name)
    {
        return $this->podio->get("/user/property/{$name}")->json_body();
    }

    /**
     * @see https://developers.podio.com/doc/users/set-user-property-29799
     */
    public function set_property($name, $value)
    {
        return $this->podio->put("/user/property/{$name}", $value);
    }

    /**
     * @see https://developers.podio.com/doc/users/set-user-properties-9052829
     */
    public function set_properties($attributes)
    {
        return $this->podio->put("/user/property/", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/users/delete-user-property-29800
     */
    public function delete_property($name)
    {
        return $this->podio->delete("/user/property/{$name}");
    }

    /**
     * @see https://developers.podio.com/doc/users/update-profile-22402
     */
    public function update_profile($attributes)
    {
        return $this->podio->put("/user/profile/", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/users/get-profile-field-22380
     */
    public function get_profile_field($field)
    {
        return $this->podio->get("/user/profile/{$field}")->json_body();
    }

}
