<?php

class SPOTLIGHT_CLASS_EventHandler
{
    /**
     *
     * @var SPOTLIGHT_BOL_Service 
     */
    private $service;
    
    public function __construct() 
    {
        $this->service = SPOTLIGHT_BOL_Service::getInstance();
    }

    public function getCount( PEEP_Event $event )
    {
        $count = $this->service->getUserCount();
        $event->setData($count);

        return $count;
    }
    
    public function getListIdList( PEEP_Event $event )
    {
        $params = $event->getParams();
        $offset = empty($params["offset"]) ? 0 : $params["offset"];
        $count = empty($params["count"]) ? null : $params["count"];
        
        $dtoList = $this->service->getSpotLight($offset, $count);
        $list = array();
        foreach ( $dtoList as $dto )
        {
            $list[] = $dto->userId;
        }
        
        $event->setData($list);
        
        return $list;
    }
    
    public function addToList( PEEP_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        
        $data = array(
            "result" => true,
            "message" => null,
            "buyCredits" => false
        );
        
        $available = true;
        
        if ( !isset($params["checkCredits"]) || $params["checkCredits"] )
        {
            if ( !PEEP::getUser()->isAuthorized("spotlight", "add_to_list") )
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus("spotlight", "add_to_list");
                $available = false;
                $data["result"] = false;
                
                if ( $status["status"] == BOL_AuthorizationService::STATUS_PROMOTED )
                {
                    $data["message"] = $status["msg"];
                    $data["buyCredits"] = true;
                }
            }
        }
        
        if ( $available )
        {
            $this->service->addUser($userId);
            BOL_AuthorizationService::getInstance()->trackAction('spotlight', 'add_to_list');
            
            $data["result"] = true;
            $data['message'] = PEEP::getLanguage()->text('spotlight', 'user_added');
        }
        
        $event->setData($data);
        
        return $data;
    }
    
    public function removeFromList( PEEP_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        
        $this->service->deleteUser($userId);
        
        $data = array(
            "result" => true,
            "message" => PEEP::getLanguage()->text('spotlight', 'user_removed')
        );

        $event->setData($data);
        
        return $data;
    }
    
    public function isUserAdded( PEEP_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        
        $userDto = $this->service->findUserById($userId);
        
        $data = $userDto !== null;
        $event->setData($data);
        
        return $data;
    }

    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = PEEP::getLanguage();

        $event->add(
            array(
                'spotlight' => array(
                    'label' => $language->text('spotlight', 'auth_group_label'),
                    'actions' => array(
                        'add_to_list'=>$language->text('spotlight', 'auth_action_label_add_to_list')
                    )
                )
            )
        );
    }
    
    public function init()
    {
        PEEP::getEventManager()->bind("spotlight.count", array($this, "getCount"));
        PEEP::getEventManager()->bind("spotlight.get_id_list", array($this, "getListIdList"));
        PEEP::getEventManager()->bind("spotlight.add_to_list", array($this, "addToList"));
        PEEP::getEventManager()->bind("spotlight.remove_from_list", array($this, "removeFromList"));
        PEEP::getEventManager()->bind("spotlight.is_user_added", array($this, "isUserAdded"));
        PEEP::getEventManager()->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
    }
}