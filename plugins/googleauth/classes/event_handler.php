<?php

class GOOGLEAUTH_CLASS_EventHandler
{
    public function afterUserRegistered( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( $params['method'] != 'google' )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $event = new PEEP_Event('feed.action', array(
                'pluginKey' => 'base',
                'entityType' => 'user_join',
                'entityId' => $userId,
                'userId' => $userId,
                'replace' => true,
                ), array(
                'string' => PEEP::getLanguage()->text('googleauth', 'feed_user_join'),
                'view' => array(
                    'iconClass' => 'peep_ic_user'
                )
            ));
        PEEP::getEventManager()->trigger($event);
    }

    public function afterUserSynchronized( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( !PEEP::getPluginManager()->isPluginActive('activity') || $params['method'] !== 'google' )
        {
            return;
        }
        $event = new PEEP_Event(PEEP_EventManager::ON_USER_EDIT, array('method' => 'native', 'userId' => $params['userId']));
        PEEP::getEventManager()->trigger($event);
    }

    public function genericInit()
    {
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_USER_REGISTER, array($this, "afterUserRegistered"));
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_USER_EDIT, array($this, "afterUserSynchronized"));
    }

    public function init()
    {
        $this->genericInit();
    }
}
