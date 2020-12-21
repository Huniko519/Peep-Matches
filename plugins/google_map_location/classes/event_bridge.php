<?php

class GOOGLELOCATION_CLASS_EventBridge {

    public function getAvatarData($entityIdList) 
    {
        if (empty($entityIdList)) {
            return array();
        }

        $eventService = EVENT_BOL_EventService::getInstance();

        $events = $eventService->findByIdList($entityIdList);
        $toolbarData = $eventService->getListingDataWithToolbar($events, array());

         /*               'content' => $content,
                'title' => $title,
                'eventUrl' => PEEP::getRouter()->urlForRoute('event.view', array('eventId' => $eventItem->getId())),
                'imageSrc' => ( $eventItem->getImage() ? $this->generateImageUrl($eventItem->getImage(), true) : $this->generateDefaultImageUrl() ),
                'imageTitle' => $title*/
        
        $data = array();
                                
        foreach ( $toolbarData as $key => $item )
        {
            /*$data[$userId]['urlInfo'] = array(
                'routeName' => 'base_user_profile',
                'vars' => array('username' => $usernameList[$userId])
            );*/
            $data[$key]['src'] = !empty($item['imageSrc']) ? $item['imageSrc'] : '_EVENT_AVATAR_SRC_';
            $data[$key]['url'] = !empty($item['eventUrl']) ? $item['eventUrl'] : '#_EVENT_URL_';
            $data[$key]['title'] = !empty($item['imageTitle']) ? $item['imageTitle'] : null;
            $data[$key]['label'] = !empty($item['label']) ? $item['label'] : null;
            $data[$key]['labelColor'] = null;
        }
        
        return $data;
    }
    
    public function getEventListCmp( $eventIdList )
    {
        $configs = EVENT_BOL_EventService::getInstance()->getConfigs();
        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        
        $events = EVENT_BOL_EventService::getInstance()->findByIdList($eventIdList);
        
        $cmp = new GOOGLELOCATION_CMP_Component();
        $cmp->setTemplate(PEEP::getPluginManager()->getPlugin('googlelocation')->getCmpViewDir().'event_list.html');
                
        $cmp->addComponent('paging', new BASE_CMP_Paging($page, ceil(count($eventIdList) / $configs[EVENT_BOL_EventService::CONF_EVENTS_COUNT_ON_PAGE]), 5));

        $cmp->assign('noButton', true);

        if ( empty($events) )
        {
            $cmp->assign('no_events', true);
        }
        
        $toolbarList = array();
        
        $cmp->assign('page', $page);
        $cmp->assign('events',  EVENT_BOL_EventService::getInstance()->getListingDataWithToolbar($events, $toolbarList));
        $cmp->assign('toolbarList', $toolbarList);
        
        return $cmp;
    }

}
