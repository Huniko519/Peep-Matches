<?php

class BASE_CMP_Sidebar extends PEEP_Component
{
    private $componentList = array();
    private $settingList = array();
    private $positionList = array();

    /**
     *
     * @var BOL_ComponentAdminService
     */
    private $service;

	public function __construct()
	{
            parent::__construct();

            $this->service = BOL_ComponentAdminService::getInstance();
            $this->fetchFromCache();

            PEEP_ViewRenderer::getInstance()->registerFunction('sb_component', array($this, 'tplComponent'));
	}

	private function fetchFromCache()
	{
            $place = BOL_ComponentAdminService::PLACE_INDEX;

	    $state = $this->service->findCache($place);

            if ( empty($state) )
            {
                $this->componentList = $this->service->findSectionComponentList($place, 'sidebar');
                $this->positionList = $this->service->findSectionPositionList($place, 'sidebar');
                $this->settingList = $this->service->findSettingListByComponentPlaceList($this->componentList);

                return;
            }

	    foreach ( $state['defaultPositions'] as $key => $item )
	    {
	        if ($item['section'] == 'sidebar')
	        {
                $this->positionList[$key] = $item;
                $this->componentList[$key] = $state['defaultComponents'][$key];
                if( !empty($state['defaultSettings'][$key]) )
                {
                    $this->settingList[$key] = $state['defaultSettings'][$key];
                }
	        }
	    }
	}

	public function render()
	{
        $tplComponentList = array();
        foreach ( $this->componentList as $item )
        {
            $position = $this->positionList[$item['uniqName']];
            $tplComponentList[$position['order']] = $item;
        }

        ksort($tplComponentList);

        $this->assign('componentList', $tplComponentList);

        return parent::render();
	}

    public function tplComponent( $params )
    {
        $uniqName = $params['uniqName'];

        $componentPlace = $this->componentList[$uniqName];

        $viewInstance = new BASE_CMP_DragAndDropItem($uniqName);
        $viewInstance->setSettingList( empty( $this->settingList[$uniqName] ) ? array() : $this->settingList[$uniqName] );
        $viewInstance->setContentComponentClass( $componentPlace['className'] );

        return $viewInstance->renderView();
    }
}