<?php

class BASE_CLASS_WidgetParameter
{

    public function __construct()
    {
        $this->standartParamList = new WidgetStandartParamList();
        $this->widgetDetails = new WidgetDetails();
    }
    /**
     *
     * @var array
     */
    public $customParamList = array();
    /**
     *
     * @var array
     */
    public $additionalParamList = array();
    /**
     *
     * @var WidgetStandartParamList
     */
    public $standartParamList = array();
    /**
     *
     * @var bool
     */
    public $customizeMode = false;
    /**
     *
     * @var WidgetDetails
     */
    public $widgetDetails;

}

class WidgetStandartParamList
{
    public $wrapInBox = false;
    public $showTitle = true;
    public $freezed = false;
    public $toolbar = array();
    public $restrictView = false;
    public $accessRestriction = null;
    public $capContent = null;
}

class WidgetDetails
{
    public $uniqName;
}