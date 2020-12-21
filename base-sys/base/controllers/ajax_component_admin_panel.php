<?php

class BASE_CTRL_AjaxComponentAdminPanel extends BASE_CTRL_AjaxComponentPanel
{
    /**
     *
     * @var BOL_ComponentAdminService
     */
    private $componentService;

    /**
     * @see PEEP_ActionController::init()
     *
     */
    public function init()
    {
        parent::init();

        $this->registerAction('allowCustomize', array($this, 'allowCustomize'));

        if ( !PEEP::getUser()->isAdmin() )
        {
            throw new Redirect404Exception();
        }

        $this->componentService = BOL_ComponentAdminService::getInstance();
    }

    private function clearCache( $place )
    {
        $this->componentService->clearCache($place);
    }

    protected function saveComponentPlacePositions( $data )
    {
        $this->componentService->clearSection($data['place'], $data['section']);
        $this->componentService->saveSectionPositionStack($data['section'], $data['stack']);
        $this->clearCache($data['place']);

        return true;
    }

    protected function deleteComponent( $data )
    {
        $this->clearCache($data['place']);
        return $this->componentService->deletePlaceComponent($data['componentId']);
    }

    protected function cloneComponent( $data )
    {
        $this->componentService->clearSection($data['place'], $data['section']);
        $newComponentUniqName = $this->componentService->cloneComponentPlace($data['componentId'])->uniqName;

        foreach ( $data['stack'] as & $item )
        {
            $item = ( $item == $data['componentId'] ) ? $newComponentUniqName : $item;
        }

        $this->componentService->saveSectionPositionStack($data['section'], $data['stack']);
        $this->clearCache($data['place']);

        return $newComponentUniqName;
    }

    protected function saveSettings( $data )
    {
        $componentPlaceUniqName = $data['componentId'];
        $settings = $data['settings'];

        $componentId = $this->componentService->findPlaceComponent($componentPlaceUniqName)->componentId;
        $componentClass = $this->componentService->findComponent($componentId)->className;

        try
        {
            $this->validateComponentSettingList($componentClass, $settings, $data['place'], $data);
        }
        catch ( WidgetSettingValidateException $e )
        {
            return array('error' => array(
                    'message' => $e->getMessage(),
                    'field' => $e->getFieldName()
                ));
        }

        $settings = $this->processSettingList($componentClass, $settings, $data['place'], true, $data);

        $this->componentService->saveComponentSettingList($componentPlaceUniqName, $settings);
        $componentSettings = $this->componentService->findSettingList($componentPlaceUniqName);
        $this->clearCache($data['place']);

        return array('settingList' => $componentSettings);
    }

    protected function getSettingsMarkup( $data )
    {
        if ( empty($data['componentId']) )
        {
            return array();
        }

        $componentPlaceUniqName = $data['componentId'];

        $componentId = $this->componentService->findPlaceComponent($componentPlaceUniqName)->componentId;

        $componentClass = $this->componentService->findComponent($componentId)->className;
        
        $componentSettingList = $this->getComponentSettingList($componentClass, $data);
        $componentStandardSettingValueList = $this->getComponentStandardSettingValueList($componentClass, $data);
        $componentAccess = $this->getComponentAccess($componentClass, $data);

        $entitySettingList = $this->componentService->findSettingList($componentPlaceUniqName);

        $cmpClass = empty($data["settingsCmpClass"]) ? "BASE_CMP_ComponentSettings" : $data["settingsCmpClass"];
        $cmp = PEEP::getClassInstance($cmpClass, $componentPlaceUniqName, $componentSettingList, $entitySettingList, $componentAccess);
        
        if ( $data['place'] == BOL_ComponentService::PLACE_INDEX )
        {
            $cmp->markAsHidden('freeze');
        }

        $cmp->setStandardSettingValueList($componentStandardSettingValueList);

        return $this->getSettingFormMarkup($cmp);
    }

    protected function savePlaceScheme( $data )
    {
        $placeName = $data['place'];
        $scheme = (int) $data['scheme'];
        $this->componentService->savePlaceScheme($placeName, $scheme);

        $this->clearCache($data['place']);

        return true;
    }

    protected function moveComponentToPanel( $data )
    {
        $placeComponentId = $data['componentId'];
        $this->componentService->saveComponentSettingList($placeComponentId, array('freeze' => 0));

        $this->clearCache($data['place']);

        return array(
            'freeze' => false
        );
    }

    protected function reloadComponent( $data )
    {
        $componentUniqName = $data['componentId'];
        $renderView = !empty($data['render']);

        $componentPlace = $this->componentService->findPlaceComponent($componentUniqName);
        $component = $this->componentService->findComponent($componentPlace->componentId);
        $componentSettingList = $this->componentService->findSettingList($componentUniqName);

        $viewInstance = new BASE_CMP_DragAndDropItem($componentUniqName, $componentPlace->clone, 'drag_and_drop_item_customize');
        $viewInstance->setSettingList($componentSettingList);
        $viewInstance->componentParamObject->additionalParamList = $data['additionalSettings'];
        $viewInstance->componentParamObject->customizeMode = true;

        $viewInstance->setContentComponentClass($component->className);

        return $this->getComponentMarkup($viewInstance, $renderView);
    }

    protected function allowCustomize( $data )
    {
        $placeName = $data['place'];
        $allowed = $data['state'];

        $this->componentService->saveAllowCustomize($placeName, $allowed);
    }
}
