<?php

/**
 * @see https://developers.podio.com/doc/questions
 */
class PodioQuestion extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('question_id', 'integer', array('id' => true));
        $this->property('text', 'string');

        $this->has_one('ref', 'Reference');
        $this->has_many('answers', 'QuestionAnswer');
        $this->has_many('options', 'QuestionOption');

        $this->init($attributes);
    }

    /**
     * @see https://developers.podio.com/doc/questions/create-question-887166
     */
    public function create($ref_type, $ref_id, $attributes = array())
    {
        $body = $this->podio->post("/question/{$ref_type}/{$ref_id}/", $attributes)->json_body();
        return $body['question_id'];
    }

    /**
     * @see https://developers.podio.com/doc/questions/answer-question-887232
     */
    public function answer($question_id, $attributes = array())
    {
        return $this->podio->post("/question/{$question_id}/", $attributes);
    }

}
