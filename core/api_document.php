<?php

class PEEP_ApiDocument extends PEEP_Document
{
    public function  __construct()
    {
        $this->type = PEEP_Document::JSON;
    }

    private $body;

    public function getBody()
    {
        return $this->body;
    }

    public function setBody( $body )
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function render()
    {
        if( $this->type == PEEP_Document::JSON )
        {
            return $this->renderJson();
        }
    }

    private function renderJson()
    {
        PEEP::getResponse()->setHeader(PEEP_Response::HD_CNT_TYPE, "application/json");

        $body = $this->getBody();
        
        $apiResponse = array(
            "type" => "success",
            "data" => empty($body) ? new stdClass() : $body
        );
        
        return json_encode($apiResponse);
    }
}