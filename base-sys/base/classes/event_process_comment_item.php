<?php

class BASE_CLASS_EventProcessCommentItem extends PEEP_Event
{
    /**
     * @var BOL_Comment
     */
    private $item;
    /**
     * @var array
     */
    private $dataArr;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name, BOL_Comment $item, array $data )
    {
        parent::__construct($name);
        $this->item = $item;
        $this->dataArr = $data;
    }

    /**
     * @return BOL_Comment
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return array
     */
    public function getDataArr()
    {
        return $this->dataArr;
    }

    /**
     * @param array $data
     */
    public function setDataArr( array $data )
    {
        $this->dataArr = $data;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getDataProp( $name )
    {
        if ( $this->dataPropExists($name) )
        {
            return $this->dataArr[$name];
        }

        return null;
    }

    /**
     * @param string $name
     * @param mixed $val
     */
    public function setDataProp( $name, $val )
    {
        $this->dataArr[$name] = $val;
    }

    /**
     * @param bool $name
     */
    public function dataPropExists( $name )
    {
        return array_key_exists($name, $this->dataArr);
    }
}

