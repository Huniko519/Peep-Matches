<?php

class CONTACTIMPORTER_CTRL_InvitePage extends PEEP_ActionController

{

public function index()

{

            $this->setPageHeading(PEEP::getLanguage()->text('contactimporter', 'widget_title'));
            $this->setPageTitle(PEEP::getLanguage()->text('contactimporter', 'widget_title'));

            $staticUrl = PEEP::getPluginManager()->getPlugin('contactimporter')->getStaticUrl();
        $document = PEEP::getDocument();
        $document->addStyleSheet($staticUrl . 'css/page.css');
     
        $invico = PEEP::getPluginManager()->getPlugin('contactimporter')->getStaticUrl() . 'img/invite_t_ico.png';
        $this->assign('invico', $invico);


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

}