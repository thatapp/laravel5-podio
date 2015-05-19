<?php

/**
 * @see https://developers.podio.com/doc/questions
 */
class PodioQuestionAnswer extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('question_option_id', 'integer', array('id' => true));

        $this->has_one('user', 'Contact');

        $this->init($attributes);
    }

}
