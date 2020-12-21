<?php

class BASE_CMP_ComponentEntitySettings extends BASE_CMP_ComponentSettings
{
    /**
     * Component default settings
     *
     * @var array
     */
    private $entitytSettingList = array();

    public function __construct( array $componentSettings = array(), array $defaultSettings = array(), array $entitySettingList )
    {
        parent::__construct($componentSettings, $defaultSettings);

        $this->entitytSettingList = $entitySettingList;
    }

    protected function makeSettingList( $defaultSettingList )
    {
        return parent::makeSettingList(array_merge($defaultSettingList, $this->entitySettingList));
    }
}

