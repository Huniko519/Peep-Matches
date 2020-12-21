<?php

class PHOTO_CLASS_Credits
{
    private $actions;

    private $authActions = array();
    
    public function __construct()
    {
        $this->actions[] = array('pluginKey' => 'photo', 'action' => 'add_photo', 'amount' => 0);
        $this->actions[] = array('pluginKey' => 'photo', 'action' => 'add_comment', 'amount' => 0);

        $this->authActions['upload'] = 'add_photo';
        $this->authActions['add_comment'] = 'add_comment';
    }
    
    public function bindCreditActionsCollect( BASE_CLASS_EventCollector $e )
    {
        foreach ( $this->actions as $action )
        {
            $e->add($action);
        }        
    }
    
    public function triggerCreditActionsAdd()
    {
        $e = new BASE_CLASS_EventCollector('usercredits.action_add');
        
        foreach ( $this->actions as $action )
        {
            $e->add($action);
        }

        PEEP::getEventManager()->trigger($e);
    }

    public function getActionKey( PEEP_Event $e )
    {
        $params = $e->getParams();
        $authAction = $params['actionName'];

        if ( $params['groupName'] != 'photo' )
        {
            return;
        }

        if ( !empty($this->authActions[$authAction]) )
        {
            $e->setData($this->authActions[$authAction]);
        }
    }
}