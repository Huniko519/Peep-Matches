<?php

class CONTACTIMPORTER_CLASS_GoogleProvider extends CONTACTIMPORTER_CLASS_Provider
{
    public function __construct()
    {
        $staticUrl = PEEP::getPluginManager()->getPlugin('contactimporter')->getStaticUrl();
        parent::__construct(array(
            'key' => 'google',
            'title' => 'Google',
            'settigsUrl' => PEEP::getRouter()->urlForRoute('contactimporter_google_settings'),
            'iconClass' => 'peep_ic_gear_wheel'
        ));
    }

    public function prepareButton( $params )
    {
        $clientId = PEEP::getConfig()->getValue('contactimporter', 'google_client_id');
        $clientSecret = PEEP::getConfig()->getValue('contactimporter', 'google_client_secret');

        if ( empty($clientId) || empty($clientSecret) )
        {
            return;
        }

        $staticUrl = PEEP::getPluginManager()->getPlugin('contactimporter')->getStaticUrl();
        $document = PEEP::getDocument();

        $document->addScript($staticUrl . 'js/google.js');

        $userId = PEEP::getUser()->getId();
	$callbackUrl = PEEP::getRouter()->urlForRoute('contact-importer-google-oauth');
        $clientId = PEEP::getConfig()->getValue('contactimporter', 'google_client_id');
	$authUrl = PEEP::getRequest()->buildUrlQueryString('https://accounts.google.com/o/oauth2/auth', array(
		'response_type' => 'code',
		'client_id' => $clientId,
		'redirect_uri' => $callbackUrl,
		'state' => 'contacts',
		'scope' => 'https://www.google.com/m8/feeds/'
	));


        $jsParams = array(
            'popupUrl' => $authUrl
        );

        $js = UTIL_JsGenerator::newInstance();
        $js->newObject(array('window', 'CONTACTIMPORTER_Google'), 'CI_GoogleLuncher', array($jsParams));

        $document->addOnloadScript($js);
$language = PEEP::getLanguage();
        return array(
            'iconUrl' => $staticUrl . 'img/goo.png',
            'widgeticonUrl' => $staticUrl . 'img/gmail.png',
            'langLabel' => PEEP::getLanguage()->text('contactimporter', 'goo_btn_invite'),
             'class' => 'google_invite_icon',
            'onclick' => "CONTACTIMPORTER_Google.request(); return false;"
        );
    }

    public function getInviters( $code )
    {
	$inv = BOL_UserService::getInstance()->findInvitationInfo($code);

        if ( empty($inv) )
        {
            return array();
        }

        return array($inv->userId);
    }
}
