<?php

class BOL_ComponentEntityService extends BOL_ComponentService
{
    /**
     * @var BOL_ComponentEntityPositionDao
     */
    private $componentPositionDao;
    /**
     * @var BOL_ComponentEntitySettingDao
     */
    private $componentSettingDao;
    /**
     * @var BOL_PlaceSchemeDao
     */
    private $placeSchemeDao;
    /**
     *
     * @var BOL_ComponentEntityPlaceDao
     */
    private $componentPlaceDao;

    protected function __construct()
    {
        parent::__construct();

        $this->componentPositionDao = BOL_ComponentEntityPositionDao::getInstance();
        $this->componentSettingDao = BOL_ComponentEntitySettingDao::getInstance();
        $this->placeSchemeDao = BOL_PlaceEntitySchemeDao::getInstance();
        $this->componentPlaceDao = BOL_ComponentEntityPlaceDao::getInstance();
    }
    /**
     * Class instance
     *
     * @var BOL_ComponentEntityService
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_ComponentEntityService
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function findComponentPlace( $componentPlaceUniqName, $entityId )
    {
        $componentPlace = $this->componentPlaceDao->findByUniqName($componentPlaceUniqName, $entityId);
        if ( $componentPlace === null )
        {
            $componentPlace = BOL_ComponentPlaceDao::getInstance()->findByUniqName($componentPlaceUniqName);
        }

        return $componentPlace;
    }

    public function findPlaceComponentList( $place, $entityId )
    {
        $placeId = $this->findPlaceId($place);
        $list = $this->componentPlaceDao->findComponentList($placeId, $entityId);

        return $this->fetchArrayList($list, 'uniqName');
    }

    public function cloneComponentPlace( $componentPlaceUniqName, $entityId )
    {
        $defaultComponentPlaceDao = BOL_ComponentPlaceDao::getInstance();
        $defaultComponentSettingDao = BOL_ComponentSettingDao::getInstance();

        /* @var $componentPlaceDto BOL_ComponentPlace */
        $componentPlaceDto = $defaultComponentPlaceDao->findByUniqName($componentPlaceUniqName);
        $componentEntityPlaceDto = new BOL_ComponentEntityPlace();
        $componentEntityPlaceDto->entityId = $entityId;
        $componentEntityPlaceDto->clone = 1;
        $componentEntityPlaceDto->componentId = $componentPlaceDto->componentId;
        $componentEntityPlaceDto->uniqName = uniqid('entity-');
        $componentEntityPlaceDto->placeId = $componentPlaceDto->placeId;

        $this->componentPlaceDao->save($componentEntityPlaceDto);

        $defaultComponentSettings = $defaultComponentSettingDao->findSettingList($componentPlaceUniqName);

        foreach ( $defaultComponentSettings as $setting )
        {
            $newSettingDto = new BOL_ComponentEntitySetting();
            $newSettingDto->name = $setting->name;
            $newSettingDto->componentPlaceUniqName = $componentEntityPlaceDto->uniqName;
            $newSettingDto->entityId = $entityId;
            $newSettingDto->value = $setting->value;

            $this->componentSettingDao->save($newSettingDto);
        }

        return $componentEntityPlaceDto;
    }

    public function findAllSettingList( $entityId )
    {
        $dtoList = $this->componentSettingDao->findAllEntitySettingList($entityId);

        return $this->fetchSettingList($dtoList);
    }

    public function findSettingList( $componentPlaceUniqName, $entityId, $settingList = array() )
    {
        $dtoList = $this->componentSettingDao->findSettingList($componentPlaceUniqName, $entityId, $settingList);

        return $this->fetchSettingList($dtoList, $componentPlaceUniqName);
    }

    public function saveComponentSettingList( $componentPlaceUniqName, $entityId, array $settingList )
    {
        foreach ( $settingList as $name => $value )
        {
            $this->componentSettingDao->saveSetting($componentPlaceUniqName, $entityId, $name, $value);
        }
    }

    public function findAllPositionList( $place, $entityId )
    {
        $placeId = $this->findPlaceId($place);
        $dtoList = $this->componentPositionDao->findAllPositionList($placeId, $entityId);

        return $this->fetchArrayList($dtoList, 'componentPlaceUniqName');
    }

    public function clearSection( $place, $entityId, $section )
    {
        $placeId = $this->findPlaceId($place);
        $componentPositionIds = $this->componentPositionDao->findSectionPositionIdList($placeId, $entityId, $section);

        return $this->componentPositionDao->deleteByIdList($componentPositionIds);
    }

    public function saveSectionPositionStack( $entityId, $section, array $componentPlaceStack )
    {

        for ( $i = 0; $i < count($componentPlaceStack); $i++ )
        {
            $dtoPosition = new BOL_ComponentEntityPosition();
            $dtoPosition->componentPlaceUniqName = $componentPlaceStack[$i];
            $dtoPosition->order = $i;
            $dtoPosition->section = $section;
            $dtoPosition->entityId = $entityId;

            $this->componentPositionDao->save($dtoPosition);
        }
    }

    public function moveComponentPlaceFromDefault( $componentPlaceUniqName, $entityId )
    {
        $existingComponent = $this->componentPlaceDao->findByUniqName($componentPlaceUniqName, $entityId);
        if ( $existingComponent !== null )
        {
            return $existingComponent;
        }

        $defaultComponentPlaceDao = BOL_ComponentPlaceDao::getInstance();

        /* @var $componentPlaceDto BOL_ComponentPlace */
        $componentPlaceDto = $defaultComponentPlaceDao->findByUniqName($componentPlaceUniqName);
        $componentEntityPlaceDto = new BOL_ComponentEntityPlace();
        $componentEntityPlaceDto->entityId = $entityId;
        $componentEntityPlaceDto->clone = $componentPlaceDto->clone;
        $componentEntityPlaceDto->componentId = $componentPlaceDto->componentId;
        $componentEntityPlaceDto->uniqName = $componentPlaceDto->uniqName;
        $componentEntityPlaceDto->placeId = $componentPlaceDto->placeId;

        $newComponent = $this->componentPlaceDao->save($componentEntityPlaceDto);

        return $newComponent;
    }

    public function deletePlaceComponent( $componentPlaceUniqName, $entityId )
    {
        $placeDto = $this->findComponentPlace($componentPlaceUniqName, $entityId);
        if ( $placeDto === null )
        {
            return;
        }

        $component = $this->findComponent($placeDto->componentId);

        $event = new PEEP_Event('widgets.before_place_delete', array(
            'class' => $component->className,
            'uniqName' => $placeDto->uniqName,
            'entityId' => $entityId
        ));

        PEEP::getEventManager()->trigger($event);

        $this->componentPlaceDao->deleteByUniqName($componentPlaceUniqName, $entityId);
        $this->componentSettingDao->deleteList($componentPlaceUniqName, $entityId);
    }

    public function savePlaceScheme( $place, $entityId, $schemeId )
    {
        $placeId = $this->findPlaceId($place);
        $placeSchemeDto = $this->placeSchemeDao->findPlaceScheme($placeId, $entityId);

        if ( !$placeSchemeDto )
        {
            $placeSchemeDto = new BOL_PlaceEntityScheme();
            $placeSchemeDto->placeId = $placeId;
            $placeSchemeDto->entityId = $entityId;
        }

        $placeSchemeDto->schemeId = $schemeId;

        $this->placeSchemeDao->save($placeSchemeDto);
    }

    /**
     *
     * @param string $place
     * @return BOL_Scheme
     */
    public function findSchemeByPlace( $place, $entityId )
    {
        $placeId = $this->findPlaceId($place);
        return $this->findSchemeByPlaceId($placeId, $entityId);
    }

    /**
     *
     * @param int $placeId
     * @return BOL_Scheme
     */
    public function findSchemeByPlaceId( $placeId, $entityId )
    {
        $placeSchemeDto = $this->placeSchemeDao->findPlaceScheme($placeId, $entityId);
        if ( !$placeSchemeDto )
        {
            return null;
        }
        return $this->schemeDao->findById($placeSchemeDto->schemeId);
    }

    public function resetCustomization( $place, $entityId )
    {
        $placeId = $this->findPlaceId($place);

        $componentIdList = $this->componentPlaceDao->findAdminComponentIdList($placeId, $entityId);
        $this->componentPlaceDao->deleteByIdList($componentIdList);

        $positionIdList = $this->componentPositionDao->findAllPositionIdList($placeId, $entityId);
        $this->componentPositionDao->deleteByIdList($positionIdList);
    }

    public function onEntityDelete( $place, $entityId )
    {
        $placeId = $this->findPlaceId($place);

        $adminCmps = BOL_ComponentAdminService::getInstance()->findPlaceComponentList($place);
        $entityCmps = $this->findPlaceComponentList($place, $entityId);
        $placeComponents = array_merge($adminCmps, $entityCmps);

        $uniqNames = array();
        foreach ( $placeComponents as $uniqName => $item )
        {
            $uniqNames[] = $uniqName;
        }

        $this->componentPositionDao->deleteByUniqNameList($entityId, $uniqNames);
        $this->componentSettingDao->deleteByUniqNameList($entityId, $uniqNames);
        $this->componentPlaceDao->deleteList($placeId, $entityId);

        $this->componentPlaceCacheDao->deleteCache($placeId, $entityId);
    }
}