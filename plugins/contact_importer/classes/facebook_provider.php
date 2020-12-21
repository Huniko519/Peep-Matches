<?php

require_once PEEP_DIR_LIB . 'facebook' . DS . 'facebook.php';

class CONTACTIMPORTER_CLASS_FacebookProvider extends CONTACTIMPORTER_CLASS_Provider
{
    public function __construct()
    {
        $staticUrl = PEEP::getPluginManager()->getPlugin('contactimporter')->getStaticUrl();

        parent::__construct(array(
            'key' => 'facebook',
            'title' => 'Facebook',
            'settigsUrl' => PEEP::getRouter()->urlForRoute('contactimporter_facebook_settings'),
            'iconClass' => 'peep_ic_gear_wheel'
        ));
    }

    public function prepareButton( $params )
    {
        $appId = PEEP::getConfig()->getValue('contactimporter', 'facebook_app_id');

        if ( empty($appId) )
        {
            return;
        }

        $staticUrl = PEEP::getPluginManager()->getPlugin('contactimporter')->getStaticUrl();
        $document = PEEP::getDocument();
        $document->addScript($staticUrl . 'js/facebook.js');

        $userId = PEEP::getUser()->getId();
        $fbLibUrl = 'http://connect.facebook.net/en_US/all.js';
        
        $code = UTIL_String::getRandomString(20);
        BOL_UserService::getInstance()->saveUserInvitation($userId, $code);
        $urlForInvite = PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlForRoute('base_join'), array('code' => $code));

        $js = UTIL_JsGenerator::newInstance();
        $js->newObject(array('window', 'CONTACTIMPORTER_FaceBook'), 'CI_Facebook', array($fbLibUrl, $userId, $urlForInvite));

        $fbParams = array(
            'appId' => $appId,
            'status' => true, // check login status
            'cookie' => true, // enable cookies to allow the server to access the session
            'xfbml'  => true
        );

        $js->callFunction(array('CONTACTIMPORTER_FaceBook', 'init'), array($fbParams));
        $document->addOnloadScript((string) $js);

	PEEP::getLanguage()->addKeyForJs('contactimporter', 'facebook_inv_message_text');
        PEEP::getLanguage()->addKeyForJs('contactimporter', 'facebook_after_invite_feedback');

        return array(
            'iconUrl' => $staticUrl . 'img/face.png',
            'widgeticonUrl' => $staticUrl . 'img/bluef.png',
            'langLabel' => PEEP::getLanguage()->text('contactimporter', 'fb_btn_invite'),
            'class' => 'facebook_invite_icon',
            'onclick' => "CONTACTIMPORTER_FaceBook.request(); return false;"
        );
    }

    public function getInviters( $code )
    {
	$data = base64_decode($code);
        $data = json_decode($data, true);

        $requestIds = empty($data['requestIds']) ? array() : $data['requestIds'];

        if ( !empty($requestIds) )
        {
            $appId = PEEP::getConfig()->getValue('contactimporter', 'facebook_app_id');
            $appSecret = PEEP::getConfig()->getValue('contactimporter', 'facebook_app_secret');
            $facebook = new Facebook(array(
                'appId' => $appId,
                'secret' => $appSecret
            ));

            foreach ( $requestIds as $id )
            {
                try
                {
                    $facebook->api('/' . $id, 'DELETE');
                }
                catch ( Exception $e )
                {}
            }
        }

        return empty($data['inviters']) ? array() : $data['inviters'];
    }
}
