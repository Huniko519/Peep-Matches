<?php

class ADS_CLASS_EventHandler
{
   
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {

    }

    public function addPageTopBanner( BASE_CLASS_EventCollector $event )
    {
        $cmp = new ADS_CMP_Ads(array('position' => 'top'));
        $event->add($cmp->render());
    }

    public function addPageBottomBanner( BASE_CLASS_EventCollector $event )
    {
        $cmp = new ADS_CMP_Ads(array('position' => 'bottom'));
        $event->add($cmp->render());
    }

    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = PEEP::getLanguage();
        $event->add(
            array(
                'ads' => array(
                    'label' => $language->text('ads', 'auth_group_label'),
                    'actions' => array(
                        'hide_ads' => $language->text('ads', 'auth_action_label_hide_ads')
                    )
                )
            )
        );
    }


    public function genericInit()
    {
        PEEP::getEventManager()->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
    }

    public function init()
    {
        $this->genericInit();

        PEEP::getEventManager()->bind('base.add_page_top_content', array($this, 'addPageTopBanner'));
        PEEP::getEventManager()->bind('base.add_page_bottom_content', array($this, 'addPageBottomBanner'));
    }
}