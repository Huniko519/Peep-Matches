<?php

abstract class CONTACTIMPORTER_CLASS_Provider
{
    private $info = array();

    public function __construct( $info )
    {
        $this->info = $info;
    }

    public function getProviderInfo()
    {
        return $this->info;
    }

    abstract public function prepareButton( $params );

    public function getInviters( $code )
    {
	return array();
    }
}