<?php

class SEARCHSYS_CLASS_Credits
{
    private $action;
    
    public function __construct()
    {
        $this->action = array('pluginKey' => 'searchsys', 'action' => 'search_system', 'amount' => 0);
    }

    /**
     * @param BASE_CLASS_EventCollector $e
     */
    public function bindCreditActionCollect( BASE_CLASS_EventCollector $e )
    {
        $e->add($this->action);
    }
    
    public function triggerCreditActionAdd()
    {
        $e = new BASE_CLASS_EventCollector('usercredits.action_add');
        $e->add($this->action);

        PEEP::getEventManager()->trigger($e);
    }
}