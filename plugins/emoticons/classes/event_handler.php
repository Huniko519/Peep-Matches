<?php

class EMOTICONS_CLASS_EventHandler
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
    
    private $service;

    private function __construct()
    {
        $this->service = EMOTICONS_BOL_Service::getInstance();
    }
    
    public function init()
    {
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_FINALIZE, array($this, 'onFinalize'));
        PEEP::getEventManager()->bind('base.comment_item_process', array($this, 'commentProcess'));
        PEEP::getEventManager()->bind('feed.on_item_render', array($this, 'onFeedItemRender'), 9999);
        PEEP::getEventManager()->bind('feed.after_render_format', array($this, 'onAfterRenderFormat'));
        PEEP::getEventManager()->bind('questions.on_list_item_render', array($this, 'onQuestionRender'));
        PEEP::getEventManager()->bind('questions.on_question_render', array($this, 'onQuestionRender'));
        
        PEEP::getEventManager()->bind('event_after_create_event', array($this, 'onEventSave'));
        PEEP::getEventManager()->bind('event_after_event_edit', array($this, 'onEventSave'));
        
        PEEP::getEventManager()->bind('groups_group_create_complete', array($this, 'onGroupSave'));
        PEEP::getEventManager()->bind('groups_group_edit_complete', array($this, 'onGroupSave'));
        
        PEEP::getEventManager()->bind('video.add_clip', array($this, 'onVideoCreate'), 1000);
        PEEP::getEventManager()->bind('video.after_edit', array($this, 'onVideoEdit'));
        
        PEEP::getEventManager()->bind('mailbox.before_create_conversation', array($this, 'onBeforeCreateConversation'));
        PEEP::getEventManager()->bind('acomments.repliesReady', array($this, 'onAcommentsRepliesReady'));
    }
    
    public function genericInit()
    {
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onBeforeDocumentRender'));
        PEEP::getEventManager()->bind('base.comment_item_process', array($this, 'commentProcess'));
        PEEP::getEventManager()->bind('feed.on_item_render', array($this, 'onFeedItemRender'), 9999);
        PEEP::getEventManager()->bind('feed.after_render_format', array($this, 'onAfterRenderFormat'));
    }

    public function onFinalize( PEEP_event $event )
    {
        if ( PEEP::getRequest()->isAjax() )
        {
            return;
        }
        
        $plugin = PEEP::getPluginManager()->getPlugin('emoticons');
        
        
        
        $cmp = new EMOTICONS_CMP_Panel();
        PEEP::getDocument()->appendBody($cmp->render());
    }
    
    public function onBeforeDocumentRender()
    {
        $pregQuote = array();
        $emoticons = $this->service->getEmoticonsKeyPairWrapInTag();
        
        foreach ( $emoticons as $code => $smile )
        {
            $pregQuote[] = preg_quote($code, '/');
        }
        
        $pattern = '/(?:' . implode('|', $pregQuote) . ')(?:(?![^<]*?>))/i';
        $body = preg_replace_callback($pattern, 'EMOTICONS_CLASS_EventHandler::mobileContentReplace', PEEP::getDocument()->getBody());
        PEEP::getDocument()->setBody($body);
    }
    
    public static function mobileContentReplace( $code )
    {
        static $emoticons = array();
        
        if ( empty($emoticons) )
        {
            $emoticons = EMOTICONS_BOL_Service::getInstance()->getEmoticonsKeyPairWrapInTag();
        }
        
        if ( isset($emoticons[$code[0]]) )
        {
            return $emoticons[$code[-0]];
        }
        
        return '';
    }

    public function commentProcess( BASE_CLASS_EventProcessCommentItem $event )
    {
        $message = $this->service->replace($event->getDataProp('content'));
        $event->setDataProp('content', $message);
    }
    
    public function onFeedItemRender( PEEP_Event $event )
    {
        $data = $event->getData();
        
        if ( !empty($data['string']) )
        {
            $data['string'] = $this->service->replace($data['string']);
        }
        
        $event->setData($data);
    }
    
    public function onAfterRenderFormat( PEEP_Event $event )
    {
        $params = $event->getParams();
        
        if ( empty($params['vars']) || empty($params['vars']['status']) )
        {
            return;
        }
        
        static $pattern = NULL;
        
        if ( $pattern === NULL )
        {
            $pregQuote = array();
            $emoticons = $this->service->getEmoticonsKeyPairWrapInTag();

            foreach ( $emoticons as $code => $smile )
            {
                $pregQuote[] = preg_quote($code, '/');
            }

            $pattern = '/(?:' . implode('|', $pregQuote) . ')(?:(?![^<]*?>))/i';
        }
        
        $data = preg_replace_callback($pattern, 'EMOTICONS_CLASS_EventHandler::mobileContentReplace', $event->getData());
        $event->setData($data);
    }

    public function onQuestionRender( PEEP_Event $event )
    {
        $data = $event->getData();
        
        if ( !empty($data['text']) )
        {
            $data['text'] = $this->service->replace($data['text']);
        }
        
        $event->setData($data);
    }
    
    private function replace( $text )
    {
        return BOL_TextFormatService::getInstance()->processWsForOutput($text, array('buttons' => array(
            BOL_TextFormatService::WS_BTN_BOLD,
            BOL_TextFormatService::WS_BTN_ITALIC,
            BOL_TextFormatService::WS_BTN_UNDERLINE,
            BOL_TextFormatService::WS_BTN_LINK,
            BOL_TextFormatService::WS_BTN_ORDERED_LIST,
            BOL_TextFormatService::WS_BTN_UNORDERED_LIST,
            BOL_TextFormatService::WS_BTN_IMAGE
        )));
    }

    public function onEventSave( PEEP_Event $event )
    {
        $params = $event->getParams();
        
        if ( PEEP::getPluginManager()->isPluginActive('eventx') )
        {
            $dto = EVENTX_BOL_EventDao::getInstance();
            $id = $params['eventDto']->id;
        }
        else
        {
            $dto = EVENT_BOL_EventDao::getInstance();
            $id = $params['eventId'];
        }
        
        $eventDto = $dto->findById($id);
        
        if ( $eventDto === NULL )
        {
            return;
        }
        
        $eventDto->setDescription($this->replace($_POST['desc']));
        $dto->save($eventDto);
    }
    
    public function onGroupSave( PEEP_Event $event )
    {
        $params = $event->getParams();
        $groupDto = GROUPS_BOL_GroupDao::getInstance()->findById($params['groupId']);
        
        if ( $groupDto === NULL )
        {
            return;
        }
        
        $groupDto->description = $this->replace($_POST['description']);
        GROUPS_BOL_GroupDao::getInstance()->save($groupDto);
    }
    
    public function onVideoCreate( PEEP_Event $event )
    {
        $data = $event->getData();
        
        if ( empty($data['id']) || ($clipDto = VIDEO_BOL_ClipDao::getInstance()->findById($data['id'])) === NULL )
        {
            return;
        }
        
        if ( isset($_POST['feedType']) && $_POST['feedType'] == 'user' )
        {
            $clipDto->description = $this->service->replace($_POST['status']);
        }
        else
        {
            $clipDto->description = $this->replace($_POST['description']);
        }
        
        VIDEO_BOL_ClipDao::getInstance()->save($clipDto);
    }
    
    public function onVideoEdit( PEEP_Event $event )
    {
        $params = $event->getParams();
        
        if ( empty($params['clipId']) || ($clipDto = VIDEO_BOL_ClipDao::getInstance()->findById($params['clipId'])) === NULL )
        {
            return;
        }
        
        $clipDto->description = $this->replace($_POST['description']);
        VIDEO_BOL_ClipDao::getInstance()->save($clipDto);
    }
    
    public function onBeforeCreateConversation( PEEP_Event $event )
    {
        $data = $event->getData();
        
        if ( empty($data['message']) ) return;
        
        $emoticons = $this->service->getEmoticonsKeyPair();
        $pattern = '/<img src="' . preg_quote($this->service->getEmoticonsUrl(), '/') . '([^"]+)" \/>/i';
        $message = $this->replace($_POST['message']);
        
        $data['message'] = preg_replace_callback($pattern, 'EMOTICONS_CLASS_EventHandler::pregReplace', $message);
        
        $event->setData($data);
    }
    
    public static function pregReplace( $match )
    {
        static $emoticons = NULL;
        
        if ( empty($emoticons) )
        {
            $emoticons = EMOTICONS_BOL_Service::getInstance()->getEmoticonsKeyPair();
        }
        
        if ( ($key = array_search($match[1], $emoticons)) !== FALSE )
        {
            return $key;
        }

        return '';
    }
    
    public function onAcommentsRepliesReady( PEEP_Event $event )
    {
        $data = array();
        
        foreach ( $event->getData() as $replie )
        {
            $data[$replie['id']] = $replie;
            $data[$replie['id']]['message'] = $this->service->replace($replie['message']);
        }
        
        $event->setData($data);
    }
}
