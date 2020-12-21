<?php

class SPOTLIGHT_CLASS_Credits
{
    private $actions;

    public function __construct()
    {
        $this->actions[] = array('pluginKey' => 'spotlight', 'action' => 'add_to_list', 'amount' => 0);

        $this->authActions['add_to_list'] = 'add_to_list';
    }

    public function getActionCost()
    {
        return $this->actions[0]['amount'];
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
