<?php

class CNEWS_CMP_Feed extends PEEP_Component
{
    private static $feedCounter = 0;

    /**
     *
     * @var CNEWS_CLASS_Driver
     */
    protected $driver;

    protected $data = array();
    protected $displayType = 'action';
    protected $autoId;
    protected $focused = false;

    protected $actionList = null;
    
    /**
     *
     * @var CNEWS_CMP_UpdateStatus
     */
    protected $statusCmp;

    const DISPLAY_TYPE_ACTION = 'action';
    const DISPLAY_TYPE_ACTIVITY = 'activity';
    const DISPLAY_TYPE_PAGE = 'page';

    public function __construct( CNEWS_CLASS_Driver $driver, $feedType, $feedId )
    {
        parent::__construct();
        self::$feedCounter++;

        $this->autoId = 'feed' . self::$feedCounter;
        $this->driver = $driver;

        $this->data['feedType'] = $feedType;
        $this->data['feedId'] = $feedId;
        $this->data['feedAutoId'] = $this->autoId;
        $this->data['startTime'] = time();
        $this->data['displayType'] = $this->displayType;
    }

    public function addAction( CNEWS_CLASS_Action $action )
    {
        if ( $this->actionList === null )
        {
            $this->actionList = array();
        }

        $this->actionList[$action->getId()] = $action;
    }

    public function focusOnInput( $focused = true )
    {
        $this->focused = $focused;
    }
    
    public function setDisplayType( $type )
    {
        $this->displayType = $type;
    }

    public function addStatusForm( $type, $id, $visibility = null )
    {
        $event = new PEEP_Event('feed.get_status_update_cmp', array(
            'entityType' => $type,
            'entityId' => $id,
            'feedAutoId' => $this->autoId,
            'visibility' => $visibility
        ));
        
        PEEP::getEventManager()->trigger($event);
        
        $status = $event->getData();

        if ( $status === null )
        {
            $cmp = $this->createNativeStatusForm($this->autoId, $type, $id, $visibility);
        }
        else
        {
            $cmp = $status;
        }
        
        
        
        if ( !empty($cmp) )
        {
            $this->statusCmp = $cmp;
        }
    }
    
    /**
     * 
     * @param string $autoId
     * @param string $type
     * @param int $id
     * @param int $visibility
     * @return CNEWS_CMP_UpdateStatus
     */
    protected function createNativeStatusForm($autoId, $type, $id, $visibility)
    {
        return PEEP::getClassInstance("CNEWS_CMP_UpdateStatus", $autoId, $type, $id, $visibility);
    }
    
    public function addStatusMessage( $message )
    {
        $this->assign('statusMessage', $message);
    }

    public function setup( $data )
    {
        $this->data = array_merge($this->data, $data);
        $driverOptions = $this->data;

        $driverOptions['offset'] = 0;

        $this->driver->setup($driverOptions);
    }

    protected function initJsConstants( $rsp = 'CNEWS_CTRL_Ajax' )
    {
        $js = UTIL_JsGenerator::composeJsString('
            window.peep_cnews_const.LIKE_RSP = {$like};
            window.peep_cnews_const.UNLIKE_RSP = {$unlike};
            window.peep_cnews_const.DELETE_RSP = {$delete};
            window.peep_cnews_const.LOAD_ITEM_RSP = {$loadItem};
            window.peep_cnews_const.LOAD_ITEM_LIST_RSP = {$loadItemList};
            window.peep_cnews_const.REMOVE_ATTACHMENT = {$removeAttachment};
        ', array(
            'like' => PEEP::getRouter()->urlFor($rsp, 'like'),
            'unlike' => PEEP::getRouter()->urlFor($rsp, 'unlike'),
            'delete' => PEEP::getRouter()->urlFor($rsp, 'remove'),
            'loadItem' => PEEP::getRouter()->urlFor($rsp, 'loadItem'),
            'loadItemList' => PEEP::getRouter()->urlFor($rsp, 'loadItemList'),
            'removeAttachment' => PEEP::getRouter()->urlFor($rsp, 'removeAttachment')
        ));

        PEEP::getDocument()->addOnloadScript($js, 50);
    }
    
    protected function initializeJs( $jsConstructor = "CNEWS_Feed", $ajaxRsp = 'CNEWS_CTRL_Ajax', $scriptFile = null )
    {
        if ( $scriptFile === null )
        {
            PEEP::getDocument()->addScript( PEEP::getPluginManager()->getPlugin('cnews')->getStaticJsUrl() . 'cnews.js' );
        }
        else
        {
            PEEP::getDocument()->addScript($scriptFile);
        }
        
        $this->initJsConstants($ajaxRsp);

        $total = $this->getActionsCount();
        
        $js = UTIL_JsGenerator::composeJsString('
            window.peep_cnews_feed_list[{$autoId}] = new ' . $jsConstructor . '({$autoId}, {$data});
            window.peep_cnews_feed_list[{$autoId}].totalItems = {$total};
        ', array(
            'total' => $total,
            'autoId' => $this->autoId,
            'data' => array( 'data' => $this->data, 'driver' => $this->driver->getState() )
        ));

        PEEP::getDocument()->addOnloadScript($js, 50);
    }

    protected function getActionsList()
    {
        if ( $this->actionList === null )
        {
            $this->actionList = $this->driver->getActionList();
        }

        return $this->actionList;
    }

    protected function getActionsCount()
    {
        return $this->driver->getActionCount();
    }

    /**
     * 
     * @param array $actionList
     * @param array $data
     * @return CNEWS_CMP_FeedList
     */
    protected function createFeedList( $actionList, $data )
    {
        return PEEP::getClassInstance("CNEWS_CMP_FeedList", $actionList, $data);
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        if ( $this->statusCmp !== null )
        {
            if ( method_exists($this->statusCmp, "focusOnInput") )
            {
                $this->statusCmp->focusOnInput($this->focused);
            }
            
            $this->addComponent('status', $this->statusCmp);
        }
    }
    
    public function render()
    {
        $this->data['displayType'] = $this->displayType;
        
        $this->actionList = $this->getActionsList();
        $this->initializeJs();

        $list = $this->createFeedList($this->actionList, $this->data);
        $list->setDisplayType($this->displayType);

        $this->assign('list', $list->render());
        $this->assign('autoId', $this->autoId);
        $this->assign('data', $this->data);

        if ( $this->displayType == self::DISPLAY_TYPE_PAGE || !$this->data['viewMore'] )
        {
            $viewMore = 0;
        }
        else
        {
            $viewMore = $this->getActionsCount() - $this->data['displayCount'];
            $viewMore = $viewMore < 0 ? 0 : $viewMore;
        }
        
        $this->assign('viewMore', $viewMore);

        return parent::render();
    }

}