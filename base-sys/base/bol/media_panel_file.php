<?php

class BOL_MediaPanelFile extends PEEP_Entity
{
    public $plugin,
    $type,
    $userId,
    $data,
    $stamp;

    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * 
     * @return BOL_MediaPanelFile
     */
    public function setPlugin( $plugin )
    {
        $this->plugin = $plugin;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * 
     * @return BOL_MediaPanelFile
     */
    public function setType( $type )
    {
        $this->type = $type;

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * 
     * @return BOL_MediaPanelFile
     */
    public function setUserId( $userId )
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * 
     * @return BOL_MediaPanelFile
     */
    public function setData( $data )
    {
        $this->data = self::getDataJson($data);

        return $this;
    }

    public function getData()
    {
        $o = json_decode($this->data);

        return $o;
    }

    private static function getDataJson( $data )
    {
        if ( !count($data) )
        {
            return '{}';
        }

        ksort($data, SORT_STRING);

        return json_encode($data);
    }

    public function getStamp()
    {
        return $this->stamp;
    }

    /**
     * 
     * @return BOL_MediaPanelFile
     */
    public function setStamp( $stamp )
    {
        $this->stamp = $stamp;

        return $this;
    }
}