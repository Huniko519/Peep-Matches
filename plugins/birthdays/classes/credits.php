<?php

class BIRTHDAYS_CLASS_Credits
{
    private $actions;
    
    public function __construct()
    {
        $this->actions[] = array(
            'pluginKey' => 'birthdays',
            'action' => 'birthday',
            'amount' => 10
        );
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