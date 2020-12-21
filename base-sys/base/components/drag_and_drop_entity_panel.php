<?php

class BASE_CMP_DragAndDropEntityPanel extends BASE_CMP_DragAndDropPanel
{
    private $entityScheme;
    private $entitySettingList = array();
    private $entityPositionList = array();
    private $entityComponentList = array();
    private $entityClonedNameList = array();
    private $customizeMode = false;
    private $allowCustomize = false;
    private $responderController = 'BASE_CTRL_AjaxComponentEntityPanel';
    private $entityId;

    public function __construct( $placeName, $entityId, array $componentList, $customizeMode, $componentTemplate, $responderController = null )
    {
        parent::__construct($placeName, $componentList, $componentTemplate);

        $this->entityId = (int) $entityId;

        if ( !empty($responderController) )
        {
            $this->responderController = $responderController;
        }

        $this->customizeMode = (bool) $customizeMode;

        PEEP_ViewRenderer::getInstance()->registerFunction('dd_component', array($this, 'tplComponent'));

        $this->assign('customizeMode', $this->customizeMode);
        $this->assign('allowCustomize', $this->allowCustomize);
        $this->assign('placeName', $placeName);
        $this->assign('entityId', $this->entityId);

        $this->sharedData['entity'] = $this->entityId;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        if ( $this->customizeMode )
        {
            parent::initializeJs($this->responderController, 'PEEP_Components_DragAndDrop', $this->sharedData);

            $jsDragAndDropUrl = PEEP::getPluginManager()->getPlugin('BASE')->getStaticJsUrl() . 'drag_and_drop.js';
            PEEP::getDocument()->addScript($jsDragAndDropUrl);
        }
    }

    public function allowCustomize( $allowed = true )
    {
        $this->allowCustomize = $allowed;
        $this->assign('allowCustomize', $allowed);
    }

    public function customizeControlCunfigure( $customizeUrl, $normalUrl )
    {
        if ( $this->allowCustomize )
        {
            $js = new UTIL_JsGenerator();
            $js->newVariable('dndCustomizeUrl', $customizeUrl);
            $js->newVariable('dndNormalUrl', $normalUrl);
            $js->jQueryEvent('#goto_customize_btn', 'click', 'if(dndCustomizeUrl) window.location.href=dndCustomizeUrl;');
            $js->jQueryEvent('#goto_normal_btn', 'click', 'if(dndNormalUrl) window.location.href=dndNormalUrl;');
            PEEP::getDocument()->addOnloadScript($js);
        }
    }

    public function setEntityScheme( $scheme )
    {
        $this->entityScheme = $scheme;
    }

    public function setEntitySettingList( array $settingList )
    {
        $this->entitySettingList = $settingList;
    }

    public function setEntityPositionList( array $positionList )
    {
        $this->entityPositionList = $positionList;
    }

    public function setEntityComponentList( array $entityComponentList )
    {
        $this->entityComponentList = $entityComponentList;
    }

    protected function getCurrentScheme( $defaultScheme )
    {
        if ( empty($this->entityScheme) )
        {
            return $defaultScheme;
        }

        return $this->entityScheme;
    }

    protected function makePositionList( $defaultPositions )
    {
        $entityComponentList = $this->entityComponentList;

        $tmpList = array();

        foreach ( $defaultPositions as $item )
        {
            $componentFreezed = isset($this->settingList[$item['componentPlaceUniqName']]['freeze'])
                && $this->settingList[$item['componentPlaceUniqName']]['freeze'];

            if ( isset($entityComponentList[$item['componentPlaceUniqName']]) && !$componentFreezed )
            {
                continue;
            }

            $tmpList[$item['componentPlaceUniqName']] = $item;
        }

        foreach ( $this->entityPositionList as $item )
        {
            $tmpList[$item['componentPlaceUniqName']] = $item;
        }

        return parent::makePositionList($tmpList);
    }

    protected function makeComponentList( $defaultComponentList )
    {
        $entityList = array();
        foreach ( $this->entityComponentList as $item )
        {
            if ( !isset($defaultComponentList[$item['uniqName']]) )
            {
                $this->entityClonedNameList[] = $item['uniqName'];
            }
            $entityList[$item['uniqName']] = $item;
        }

        return parent::makeComponentList(array_merge($defaultComponentList, $entityList));
    }

    protected function makeSettingList( $defaultSettingtList )
    {
        foreach ( $this->entitySettingList as $key => $item )
        {
            $defaultSettingtList[$key] = empty($defaultSettingtList[$key]) ? $this->entitySettingList[$key] : array_merge($defaultSettingtList[$key], $this->entitySettingList[$key]);
        }

        return parent::makeSettingList($defaultSettingtList);
    }

    public function tplComponent( $params )
    {
        $uniqName = $params['uniqName'];
        $render = !empty($params['render']);

        $componentPlace = $this->componentList[$uniqName];
        $template = $this->customizeMode ? 'drag_and_drop_item_customize' : null;

        $viewInstance = new $this->itemClassName($uniqName, in_array($uniqName, $this->entityClonedNameList), $template, $this->sharedData);
        $viewInstance->setSettingList(empty($this->settingList[$uniqName]) ? array() : $this->settingList[$uniqName]);
        $viewInstance->componentParamObject->additionalParamList = $this->additionalSettingList;
        $viewInstance->componentParamObject->customizeMode = $this->customizeMode;

        if ( !empty($this->standartSettings[$componentPlace['className']]) )
        {
            $viewInstance->setStandartSettings($this->standartSettings[$componentPlace['className']]);
        }

        $viewInstance->setContentComponentClass($componentPlace['className']);

        if ( $render )
        {
            return $viewInstance->renderView();
        }

        return $viewInstance->renderScheme();
    }
}