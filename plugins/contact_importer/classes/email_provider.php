<?php

class CONTACTIMPORTER_CLASS_EmailProvider extends CONTACTIMPORTER_CLASS_Provider
{
    public function __construct()
    {
        $staticUrl = PEEP::getPluginManager()->getPlugin('contactimporter')->getStaticUrl();

        parent::__construct(array(
            'key' => 'email'
        ));
    }

    public function prepareButton( $params )
    {
       
           $staticUrl = PEEP::getPluginManager()->getPlugin('contactimporter')->getStaticUrl();

        return array(
            'iconUrl' => $staticUrl . 'img/send.png',
            'langLabel' => PEEP::getLanguage()->text('contactimporter', 'mail_btn_invite'),
            'class' => 'mail_invite_icon',
            'onclick' => "window.ciMailFloatBox = new PEEP_FloatBox({ \$title: '" . PEEP::getLanguage()->text('contactimporter', 'email_invite_floatbox_title') . "', width: 600, \$contents:$('.contactimporter_email_invite_cont') });"
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
