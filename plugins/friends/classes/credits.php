<?php

class FRIENDS_CLASS_Credits
{
    private $actions;
    
    public function __construct()
    {
        $this->actions[] = array('pluginKey' => 'friends', 'action' => 'add_friend', 'amount' => 0);

        $this->authActions['add_friend'] = 'add_friend';
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
}