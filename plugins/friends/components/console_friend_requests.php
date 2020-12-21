<?php

class FRIENDS_CMP_ConsoleFriendRequests extends BASE_CMP_ConsoleDropdownList
{
    public function __construct()
    {
        parent::__construct( PEEP::getLanguage()->text('friends', 'console_requests_title'), 'friend_requests' );


        $this->addClass('peep_friend_request_list');
    }

    public function initJs()
    {
        parent::initJs();

        $jsUrl = PEEP::getPluginManager()->getPlugin('friends')->getStaticJsUrl() . 'friend_request.js';
        PEEP::getDocument()->addScript($jsUrl);

        $js = UTIL_JsGenerator::newInstance();
        $js->addScript('PEEP.FriendRequest = new PEEP_FriendRequest({$key}, {$params});', array(
            'key' => $this->getKey(),
            'params' => array(
                'rsp' => PEEP::getRouter()->urlFor('FRIENDS_CTRL_Action', 'ajax')
            )
        ));

        PEEP::getDocument()->addOnloadScript($js);
    }
}