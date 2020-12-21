<?php

class CONTACTIMPORTER_CMP_Widget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $staticUrl = PEEP::getPluginManager()->getPlugin('contactimporter')->getStaticUrl();
        $document = PEEP::getDocument();
        $document->addStyleSheet($staticUrl . 'css/popup.css');
        $document->addStyleSheet($staticUrl . 'css/block.css');
        $document->addScript($staticUrl . 'js/popup.js');
        $mailico = PEEP::getPluginManager()->getPlugin('contactimporter')->getStaticUrl() . 'img/cmail.png';
        $this->assign('mailico', $mailico);

        $event = new BASE_CLASS_EventCollector(CONTACTIMPORTER_CLASS_EventHandler::EVENT_COLLECT_PROVIDERS);
        PEEP::getEventManager()->trigger($event);
        $providers = $event->getData();

        $btns = array();
        foreach ( $providers as $provider )
        {
            $event = new PEEP_Event(CONTACTIMPORTER_CLASS_EventHandler::EVENT_RENDER_BUTTON, array(
                'provider' => $provider['key'],
                'callbackUrl' => PEEP::getRouter()->urlFor('CONTACTIMPORTER_CTRL_Import', 'login', array(
                    'provider' => $provider['key']
                ))
            ));

            PEEP::getEventManager()->trigger($event);

            $data = $event->getData();

            if ( !empty($data) )
            {
                $btns[] = array_merge(array(
                    'iconUrl' => '',
                    'onclick' => '',
                    'href' => 'javascript://',
                    'class' => '',
                    'id' => 'contactimporter-' . $provider['key'],
                    'markup' => null
                ), $data);
            }
        }
        
        $this->assign('btns', $btns);
        $this->addComponent('eicmp', new CONTACTIMPORTER_CMP_EmailInvite());

    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => PEEP::getLanguage()->text('contactimporter', 'widget_title'),
            self::SETTING_ICON => self::ICON_ADD
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}