<?php

class BASE_CMP_DragAndDropIndex extends BASE_CMP_DragAndDropPanel
{
    private $customizeMode = false;
    private $allowCustomize = false;

    public function __construct( $placeName, array $componentList, $customizeMode, $componentTemplate )
    {
        parent::__construct($placeName, $componentList, $componentTemplate);

        $this->customizeMode = (bool) $customizeMode;

        PEEP_ViewRenderer::getInstance()->registerFunction('dd_component', array($this, 'tplComponent'));

        $this->assign('customizeMode', $this->customizeMode);
        $this->assign('allowCustomize', $this->allowCustomize);
        $this->assign('placeName', $placeName);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        if ( $this->customizeMode )
        {
            $this->initializeJs('BASE_CTRL_AjaxComponentAdminPanel', 'PEEP_Components_DragAndDrop', $this->sharedData);

            $jsDragAndDropUrl = PEEP::getPluginManager()->getPlugin('BASE')->getStaticJsUrl() . 'drag_and_drop.js';
            PEEP::getDocument()->addScript($jsDragAndDropUrl);
        }
    }

    public function setSidebarPosition( $value )
    {
        $this->assign('sidebarPosition', $value);
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

    public function tplComponent( $params )
    {
        $uniqName = $params['uniqName'];
        $render = !empty($params['render']);

        $isClone = $this->componentList[$uniqName]['clone'];

        $componentPlace = $this->componentList[$uniqName];
        $template = $this->customizeMode ? 'drag_and_drop_item_customize' : null;

        $viewInstance = new BASE_CMP_DragAndDropItem($uniqName, $isClone, $template, $this->sharedData);
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