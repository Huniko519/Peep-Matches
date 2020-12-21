<?php

abstract class BASE_CMP_DragAndDropPanel extends PEEP_Component
{
    protected $settingsCmpClass = "BASE_CMP_ComponentSettings";
    protected $itemClassName = "BASE_CMP_DragAndDropItem";
    
    protected $componentList = array();
    protected $settingList = array();
    protected $positionList = array();
    protected $standartSettings = array();

    protected $placeName;
    /**
     *
     * @var BOL_Scheme
     */
    protected $scheme = 1;
    protected $schemeList = array();
    protected $additionalSettingList = array();
    protected $sharedData = array();

    public function __construct( $placeName, array $componentList, $template = null )
    {
        parent::__construct();

        if ( $template !== null )
        {
            $plugin = PEEP::getPluginManager()->getPlugin(PEEP::getAutoloader()->getPluginKey(get_class($this)));
            $this->setTemplate($plugin->getCmpViewDir() . $template . '.html');
        }

        $this->placeName = $placeName;
        $this->componentList = $componentList;

        foreach ( $this->componentList as $widget )
        {
            $this->standartSettings[$widget['className']] = call_user_func(array($widget['className'], 'getStandardSettingValueList'), $widget["uniqName"]);
        }

        PEEP_ViewRenderer::getInstance()->registerFunction('dd_component', array($this, 'tplComponent'));

        $this->assign('disableJs', !empty($_GET['disable-js']));

        $this->assign('placeName', $placeName);


        $this->sharedData = array(
            'additionalSettings' => &$this->additionalSettingList,
            'place' => $this->placeName
        );
    }

    public function setItemClassName( $class ) 
    {
        $this->itemClassName = $class;
    }
    
    public function setSettingsClassName( $class ) 
    {
        $this->settingsCmpClass = $class;
    }

    protected function initializeJs( $responderController, $dragAndDropJsConstructor, $sharedData = array() )
    {
        $baseStaticJsUrl = PEEP::getPluginManager()->getPlugin('BASE')->getStaticJsUrl();

        PEEP::getDocument()->addScript($baseStaticJsUrl . 'jquery-ui.min.js');
        PEEP::getDocument()->addScript($baseStaticJsUrl . 'drag_and_drop_slider.js');
        PEEP::getDocument()->addScript($baseStaticJsUrl . 'ajax_utils.js');
        PEEP::getDocument()->addScript($baseStaticJsUrl . 'drag_and_drop_handler.js');
        PEEP::getDocument()->addScript($baseStaticJsUrl . 'component_drag_and_drop.js');

        PEEP::getLanguage()->addKeyForJs('base', 'widgets_delete_component_confirm');
        PEEP::getLanguage()->addKeyForJs('base', 'widgets_reset_position_confirm');
        $urlAjaxResponder = PEEP::getRouter()->urlFor($responderController, 'processQueue');

        $sharedData = array_merge(array(
            "settingsCmpClass" => $this->settingsCmpClass
        ), $sharedData);
        
        $js = new UTIL_JsGenerator();
        $js->newObject('handler', 'PEEP_Components_DragAndDropAjaxHandler', array($urlAjaxResponder, $sharedData));
        $js->newObject('dragAndDrop', $dragAndDropJsConstructor);
        $js->addScript("dragAndDrop.setHandler(handler)");

        PEEP::getDocument()->addOnloadScript($js);
    }

    public function setSettingList( array $settingList )
    {
        $this->settingList = $settingList;
    }

    public function setPositionList( array $positionList )
    {
        $this->positionList = $positionList;
    }

    public function setScheme( $scheme )
    {
        $this->scheme = (array) $scheme;
    }

    public function setSchemeList( array $schemeList )
    {
        $this->schemeList = $schemeList;
    }

    protected function getCurrentScheme( $scheme )
    {
        return $scheme;
    }

    private function makeTplComponentList()
    {
        $resultList = array();
        $tplPanelComponents = & $resultList['place'];
        $tplSectionComponents = & $resultList['section'];
        $tplClonableComponents = & $resultList['clonable'];

        $tplPanelComponents = array();
        $tplSectionComponents = array();
        $tplClonableComponents = array();

        foreach ( $this->componentList as $uniqName => $component )
        {
            if ( isset($this->positionList[$uniqName]) )
            {
                $position = $this->positionList[$uniqName];
                $tplSectionComponents[$position['section']][] = $component;
            }
            else
            {
                if ( $component['clonable'] && !$component['clone'] )
                {
                    $tplClonableComponents[] = $component;
                }
                else
                {
                    $tplPanelComponents[] = $component;
                }
            }
        }

        krsort($tplClonableComponents); //TODO clonable component order

        foreach ( $tplSectionComponents as &$section )
        {
            usort($section, array($this, 'sectionSortDelegate'));
        }

        return $resultList;
    }

    protected function sectionSortDelegate( $a, $b )
    {
        $x = ( isset($this->settingList[$a['uniqName']]['freeze']) && $this->settingList[$a['uniqName']]['freeze'] ) ? 0 : 1;
        $y = ( isset($this->settingList[$b['uniqName']]['freeze']) && $this->settingList[$b['uniqName']]['freeze'] ) ? 0 : 1;

        $r = $x - $y;

        if ( $r === 0 )
        {
            $positionA = (int) $this->positionList[$a['uniqName']]['order'];
            $positionB = (int) $this->positionList[$b['uniqName']]['order'];

            return $positionA - $positionB;
        }

        return $r;
    }

    /*protected function sectionSortDelegate( $a, $b )
    {
        //TODO refactoring: bad place to call static method
        $widgetA = $this->componentList[$a['uniqName']];
        $widgetB = $this->componentList[$b['uniqName']];

        $standardSettingsA = call_user_func(array($widgetA['className'], 'getStandardSettingValueList'));
        $standardSettingsB = call_user_func(array($widgetB['className'], 'getStandardSettingValueList'));

        $freezedA = empty($standardSettingsA['freeze']) ? 0 : 1;
        $freezedB = empty($standardSettingsB['freeze']) ? 0 : 1;

        $x = empty($this->settingList[$a['uniqName']]['freeze']) ? $freezedA : 1;
        $y = empty($this->settingList[$b['uniqName']]['freeze']) ? $freezedB : 1;

        $r = $y - $x;

        if ( $r === 0 )
        {
            $positionA = (int) $this->positionList[$a['uniqName']]['order'];
            $positionB = (int) $this->positionList[$b['uniqName']]['order'];

            return $positionA - $positionB;
        }

        return $r;
    }*/

    protected function makePositionList( $positionList )
    {
        return $positionList;
    }

    protected function makeComponentList( $componentList )
    {
        return $componentList;
    }

    protected function makeSettingList( $settingList )
    {
        foreach ( $this->componentList as $widget )
        {
            $standartSettings = empty($this->standartSettings[$widget['className']])
                ? array()
                : $this->standartSettings[$widget['className']];

            $settingList[$widget['uniqName']] = empty($settingList[$widget['uniqName']])
                ? $standartSettings
                : array_merge($standartSettings, $settingList[$widget['uniqName']]);
        }

        return $settingList;
    }

    public function onBeforeRender()
    {
        BASE_CLASS_Widget::setPlaceData($this->sharedData);

        $this->settingList = $this->makeSettingList($this->settingList);
        $this->positionList = $this->makePositionList($this->positionList);
        $this->componentList = $this->makeComponentList($this->componentList);

        $componentList = $this->makeTplComponentList();

        $currentShceme = $this->getCurrentScheme($this->scheme);
        if ( !empty($currentShceme) )
        {
            $this->assign('activeScheme', $currentShceme);
        }

        $this->assign('componentList', $componentList);
        $this->assign('schemeList', $this->schemeList);
    }

    public function setAdditionalSettingList( array $settingList = array() )
    {
        $this->additionalSettingList = $settingList;
    }

    public function tplComponent( $params )
    {
        $uniqName = $params['uniqName'];

        $isClone = $this->componentList[$uniqName]['clone'];

        $viewInstance = new $this->itemClassName($uniqName, $isClone, 'drag_and_drop_item_customize', $this->sharedData);
        $viewInstance->setSettingList(empty($this->settingList[$uniqName]) ? array() : $this->settingList[$uniqName]);
        $viewInstance->componentParamObject->additionalParamList = $this->additionalSettingList;
        $viewInstance->componentParamObject->customizeMode = null;

        if ( !empty($this->standartSettings[$this->componentList[$uniqName]['className']]) )
        {
            $viewInstance->setStandartSettings($this->standartSettings[$this->componentList[$uniqName]['className']]);
        }

        $viewInstance->setContentComponentClass($this->componentList[$uniqName]['className']);

        return $viewInstance->renderScheme();
    }
}