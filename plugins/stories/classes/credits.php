<?php

class STORIES_CLASS_Credits
{
    private $actions = array();

    private $authActions = array();
    
    public function __construct()
    {
        $this->actions[] = array('pluginKey' => 'stories', 'action' => 'add_story', 'amount' => 0);
        $this->actions[] = array('pluginKey' => 'stories', 'action' => 'add_comment', 'amount' => 0);

        $this->authActions['add'] = 'add_story';
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

        if ( $params['groupName'] != 'stories' )
        {
            return;
        }

        if ( !empty($this->authActions[$authAction]) )
        {
            $e->setData($this->authActions[$authAction]);
        }
    }
}