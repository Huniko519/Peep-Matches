<?php

class CNEWS_CMP_EntityFeedWidget extends CNEWS_CMP_FeedWidget
{
    private $feedId;
    private $feedType;

    protected $defaultParams = array(
        'statusForm' => true,
        'statusMessage' => null,
        'widget' => array()
    );

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct($paramObj);

        $this->feedId = $paramObj->additionalParamList['entityId'];
        $this->feedType = $paramObj->additionalParamList['entity'];

        $event = new PEEP_Event('feed.on_widget_construct', array(
            'feedId' => $this->feedId,
            'feedType' => $this->feedType
        ));
        PEEP::getEventManager()->trigger($event);
        $data = $event->getData();

        $data = array_merge($this->defaultParams, $data);

        foreach ( $data['widget'] as $setting => $value )
        {
            $this->setSettingValue($setting, $value);
        }

        $feed = $this->createFeed($this->feedType, $this->feedId);
        $feed->setDisplayType(CNEWS_CMP_Feed::DISPLAY_TYPE_ACTIVITY);

        if ( $data['statusForm'] )
        {
            $visibility = CNEWS_BOL_Service::VISIBILITY_FULL - CNEWS_BOL_Service::VISIBILITY_SITE;

            $feed->addStatusForm($this->feedType, $this->feedId, $visibility);
        } 
        else if (!empty($data['statusMessage'])) 
        {
            $feed->addStatusMessage($data['statusMessage']);
        }

        $this->setFeed( $feed );
    }
    
    /**
     * 
     * @param string $feedType
     * @param int $feedId
     * @return CNEWS_CMP_Feed
     */
    protected function createFeed( $feedType, $feedId )
    {
        $driver = PEEP::getClassInstance("CNEWS_CLASS_FeedDriver");
        
        return PEEP::getClassInstance("CNEWS_CMP_Feed", $driver, $feedType, $feedId);
    }
}