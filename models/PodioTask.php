<?php

/**
 * @see https://developers.podio.com/doc/tasks
 */
class PodioTask extends PodioObject
{
    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
        $this->property('task_id', 'integer', array('id' => true));
        $this->property('status', 'string');
        $this->property('group', 'string');
        $this->property('text', 'string');
        $this->property('description', 'string');
        $this->property('private', 'boolean');
        $this->property('due_on', 'datetime');
        $this->property('due_date', 'string');
        $this->property('due_time', 'string');
        $this->property('space_id', 'integer');
        $this->property('link', 'string');
        $this->property('created_on', 'datetime');
        $this->property('completed_on', 'datetime');
        $this->property('external_id', 'string');

        $this->has_one('ref', 'Reference');
        $this->has_one('created_by', 'ByLine');
        $this->has_one('completed_by', 'ByLine');
        $this->has_one('created_via', 'Via');
        $this->has_one('deleted_via', 'Via');
        $this->has_one('completed_via', 'Via');
        $this->has_one('responsible', 'User', array('json_value' => 'user_id'));
        $this->has_one('reminder', 'Reminder');
        $this->has_one('recurrence', 'Recurrence');
        $this->has_many('labels', 'TaskLabel', array('json_value' => 'label_id', 'json_target' => 'label_ids'));
        $this->has_many('files', 'File', array('json_value' => 'file_id', 'json_target' => 'file_ids'));
        $this->has_many('comments', 'Comment');

        $this->init($attributes);
    }

    /**
     * Creates or updates a task
     */
    public function save()
    {
        if ($this->id) {
            return self::update($this->id, $this);
        } else {
            $new = self::create($this);
            $this->task_id = $new->task_id;
            return $this;
        }
    }

    /**
     * Complete a task
     */
    public function completed()
    {
        return self::complete($this->id);
    }

    /**
     * Incomplete a task
     */
    public function incompleted()
    {
        return self::incomplete($this->id);
    }

    /**
     * Delete a task
     */
    public function destroy()
    {
        return self::delete($this->id);
    }

    /**
     * @see https://developers.podio.com/doc/tasks/create-task-22419
     */
    public function create($attributes = array(), $options = array())
    {
        $url = $this->podio->url_with_options("/task/", $options);
        return $this->member($this->podio->post($url, $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/tasks/create-task-with-reference-22420
     */
    public function create_for($ref_type, $ref_id, $attributes = array(), $options = array())
    {
        $url = $this->podio->url_with_options("/task/{$ref_type}/{$ref_id}/", $options);
        return $this->member($this->podio->post($url, $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/tasks/get-task-22413
     */
    public function get($task_id)
    {
        return $this->member($this->podio->get("/task/{$task_id}"));
    }

    /**
     * @see https://developers.podio.com/doc/tasks/get-tasks-77949
     */
    public function get_all($attributes = array())
    {
        return $this->listing($this->podio->get("/task/", $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/tasks/delete-task-77179
     */
    public function delete($task_id)
    {
        return $this->podio->delete("/task/{$task_id}");
    }

    /**
     * @see https://developers.podio.com/doc/tasks/remove-task-reference-6146114
     */
    public function delete_ref($task_id)
    {
        return $this->podio->delete("/task/{$task_id}/ref");
    }

    /**
     * @see https://developers.podio.com/doc/tasks/update-task-10583674
     */
    public function update($task_id, $attributes = array(), $options = array())
    {
        $url = $this->podio->url_with_options("/task/{$task_id}", $options);
        return $this->member($this->podio->put($url, $attributes));
    }

    /**
     * @see https://developers.podio.com/doc/tasks/assign-task-22412
     */
    public function assign($task_id, $attributes = array())
    {
        return $this->podio->post("/task/{$task_id}/assign", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/tasks/complete-task-22432
     */
    public function complete($task_id)
    {
        return $this->podio->post("/task/{$task_id}/complete");
    }

    /**
     * @see https://developers.podio.com/doc/tasks/incomplete-task-22433
     */
    public function incomplete($task_id)
    {
        return $this->podio->post("/task/{$task_id}/incomplete");
    }

    /**
     * @see https://developers.podio.com/doc/tasks/rank-task-81015
     */
    public function rank($task_id, $attributes = array())
    {
        return $this->podio->post("/task/{$task_id}/rank", $attributes);
    }

    /**
     * @see https://developers.podio.com/doc/calendar/get-task-calendar-as-ical-10195650
     */
    public function ical($task_id)
    {
        return $this->podio->get("/calendar/task/{$task_id}/ics/")->body;
    }

    /**
     * @see https://developers.podio.com/doc/tasks/get-task-summary-1612017
     */
    public function get_summary($attributes = array())
    {
        $result = $this->podio->get("/task/summary", $attributes)->json_body();
        $result['overdue']['tasks'] = $this->listing($result['overdue']['tasks']);
        $result['today']['tasks'] = $this->listing($result['today']['tasks']);
        $result['other']['tasks'] = $this->listing($result['other']['tasks']);
        return $result;
    }

    /**
     * @see https://developers.podio.com/doc/tasks/get-task-summary-for-personal-1657217
     */
    public function get_summary_personal($attributes = array())
    {
        $result = $this->podio->get("/task/personal/summary", $attributes)->json_body();
        $result['overdue']['tasks'] = $this->listing($result['overdue']['tasks']);
        $result['today']['tasks'] = $this->listing($result['today']['tasks']);
        $result['other']['tasks'] = $this->listing($result['other']['tasks']);
        return $result;
    }

    /**
     * @see https://developers.podio.com/doc/tasks/get-task-summary-for-organization-1612063
     */
    public function get_summary_for_org($org_id, $attributes = array())
    {
        $result = $this->podio->get("/task/org/{$org_id}/summary", $attributes)->json_body();
        $result['overdue']['tasks'] = $this->listing($result['overdue']['tasks']);
        $result['today']['tasks'] = $this->listing($result['today']['tasks']);
        $result['other']['tasks'] = $this->listing($result['other']['tasks']);
        return $result;
    }

    /**
     * @see https://developers.podio.com/doc/tasks/get-task-summary-for-space-1612130
     */
    public function get_summary_for_space($space_id, $attributes = array())
    {
        $result = $this->podio->get("/task/space/{$space_id}/summary", $attributes)->json_body();
        $result['overdue']['tasks'] = $this->listing($result['overdue']['tasks']);
        $result['today']['tasks'] = $this->listing($result['today']['tasks']);
        $result['other']['tasks'] = $this->listing($result['other']['tasks']);
        return $result;
    }

    /**
     * @see https://developers.podio.com/doc/tasks/get-task-summary-for-reference-1657980
     */
    public function get_summary_for($ref_type, $ref_id, $attributes = array())
    {
        $result = $this->podio->get("/task/{$ref_type}/{$ref_id}/summary", $attributes)->json_body();
        $result['overdue']['tasks'] = $this->listing($result['overdue']['tasks']);
        $result['today']['tasks'] = $this->listing($result['today']['tasks']);
        $result['other']['tasks'] = $this->listing($result['other']['tasks']);
        return $result;
    }

}
