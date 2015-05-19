<?php

/**
 * @see https://developers.podio.com/doc/importer
 */
class PodioImporter extends PodioObject
{

    public function __construct($podio, $attributes = array())
    {
        parent::__construct($podio);
    }

    /**
     * @see https://developers.podio.com/doc/importer/get-info-5929504
     */
    public function info($file_id)
    {
        return $this->podio->get("/importer/{$file_id}/info")->json_body();
    }

    /**
     * @see https://developers.podio.com/doc/importer/get-preview-5936702
     */
    public function preview($file_id, $row, $attributes = array())
    {
        return $this->podio->post("/importer/{$file_id}/preview/{$row}", $attributes)->json_body();
    }

    /**
     * @see https://developers.podio.com/doc/importer/import-app-items-212899
     */
    public function process_app($file_id, $app_id, $attributes = array())
    {
        $body = $this->podio->post("/importer/{$file_id}/item/app/{$app_id}", $attributes)->json_body();
        return $body['batch_id'];
    }

    /**
     * @see https://developers.podio.com/doc/importer/import-space-contacts-4261072
     */
    public function process_contacts($file_id, $space_id, $attributes = array())
    {
        return $this->podio->post("/importer/{$file_id}/contact/space/{$space_id}", $attributes)->json_body();
    }

}
