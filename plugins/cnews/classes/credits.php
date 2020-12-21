<?php

class CNEWS_CLASS_Credits
{
    private $actions;
    
    public function __construct()
    {
        $this->actions[] = array('pluginKey' => 'cnews', 'action' => 'add_comment', 'amount' => 0);
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