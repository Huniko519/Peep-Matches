<?php

class SOCIALSHARING_CLASS_EventHandler
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

    private function __construct() { }

    public function getSharingButtons( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        $entityId = !empty($params['entityId']) ? $params['entityId'] : null;
        $entityType = !empty($params['entityType']) ? $params['entityType'] : null;

        if ( !empty($entityId) && !empty($entityType) )
        {
            $sharingInfoEvent = new PEEP_Event('socialsharing.get_entity_info', $params, $params);
            PEEP::getEventManager()->trigger($sharingInfoEvent);

            $data = $sharingInfoEvent->getData();

            $params = array_merge($params, $data);
        }

        $display= isset($params['display']) ? $params['display'] : true;

        if ( !$display )
        {
            return;
        }

        $url = !empty($params['url']) ? $params['url'] : null;
        $description= !empty($params['description']) ? $params['description'] : PEEP::getDocument()->getDescription();
        $title= !empty($params['title']) ? $params['title'] : PEEP::getDocument()->getTitle();
        $image= !empty($params['image']) ? $params['image'] : null;
        $class= !empty($params['class']) ? $params['class'] : null;

        $displayBlock = false;//isset($params['displayBlock']) ? $params['displayBlock'] : true;

        $cmp = PEEP::getClassInstance('SOCIALSHARING_CMP_ShareButtons');
        $cmp->setCustomUrl($url);
        $cmp->setDescription($description);
        $cmp->setTitle($title);
        $cmp->setImageUrl($image);

        $cmp->setDisplayBlock($displayBlock);

        if ( !empty($class) )
        {
            $cmp->setBoxClass($class);
        }

        $event->add($cmp->render());
    }

    public function addJsDeclarations( PEEP_Event $e )
    {
        //Langs
        PEEP::getLanguage()->addKeyForJs('socialsharing', 'share_title');
    }

    public function genericInit()
    {
        PEEP::getEventManager()->bind('socialsharing.get_sharing_buttons', array($this, 'getSharingButtons'));
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_FINALIZE, array($this, 'addJsDeclarations'));
    }
}
