<?php

abstract class CNEWS_CLASS_Driver
{
    protected $params = array(), $defaultParams = array();
    protected $actionList = array();
    protected $count = false;

    protected $context = array();
    protected $formats = null;
    protected $actionIdList = array();

    /**
     *
     * @var CNEWS_BOL_Service
     */
    protected $service;

    public function __construct()
    {
        $this->service = CNEWS_BOL_Service::getInstance();

        $this->defaultParams = array(
            'offset' => 0,
            'length' => false,
            'displayCount' => 20,
            "formats" => null
        );
    }

    public function setup( $params )
    {
        $this->params = array_merge($this->defaultParams, $params);

        $this->count = $this->params['length'];
        $this->context = empty($params['context']) ? array() : $params['context'];
    }
    
    public function setFormats( $formats )
    {
        $this->params['formats'] = $formats;
        $this->params['formats'][] = CNEWS_CLASS_FormatManager::FORMAT_EMPTY;
    }

    public function moveCursor( $to = null )
    {
        $this->params['offset'] = empty($to) ? $this->params['offset'] + $this->params['displayCount'] : $to;
    }

    public function getState()
    {
        $this->params['length'] = $this->count;

        return array(
            'class' => get_class($this),
            'params' => $this->params
        );
    }

    public function getActionList()
    {
        $actionList = $this->findActionList($this->params);

        if ( empty($actionList) )
        {
            $this->count = 0;

            return array();
        }

        $this->count = $this->getActionCount();

        foreach ( $actionList as $actionDto )
        {
            $this->actionIdList[$actionDto->entityType . ':' . $actionDto->entityId] = $actionDto->id;
        }

        $activityList = $this->findActivityList($this->params, array_values($this->actionIdList));

        $actionActivityList = array();
        foreach ( $activityList as $activity )
        {
            $actionActivityList[$activity->actionId][$activity->id] = $activity;
        }

        $createActivityIdList = array();
        
        foreach ( $actionList as $actionDto )
        {
            $aList = empty($actionActivityList[$actionDto->id]) 
                    ? array() 
                    : $actionActivityList[$actionDto->id];
            
            /* @var $actionDto CNEWS_BOL_Action */
           $action = $this->makeAction($actionDto, $aList);

           if ( $action !== null )
           {
                $this->actionList[$actionDto->id] = $action;
                
                $createActivity = $action->getCreateActivity();
                
                if ( !empty($createActivity) )
                {
                   $createActivityIdList[] = $createActivity->id;
                }
           }
        }

        $feedList = $this->service->findFeedListByActivityids($createActivityIdList);
        
        foreach ( $this->actionList as $action )
        {
            /* @var $actionDto CNEWS_BOL_Action */
            $createActivity = $action->getCreateActivity();
                
            if ( !empty($createActivity) && isset($feedList[$createActivity->id]) )
            {
               $action->setFeedList($feedList[$createActivity->id]);
            }
        }
        
        return $this->actionList;
    }

    /**
     *
     * @param int $actionId
     * @return CNEWS_CLASS_Action
     */
    public function getActionById( $actionId )
    {
        if ( empty($this->actionList[$actionId]) )
        {
            $action = CNEWS_BOL_ActionDao::getInstance()->findActionById($actionId);

            if ( $action === null )
            {
                return null;
            }

            $activityList = $this->findActivityList($this->params, array($actionId));
            $action = $this->makeAction($action, $activityList);

            if ( $action === null )
            {
                return null;
            }

            $actionKey = $action->getEntity()->type .':'. $action->getEntity()->id;

            $this->actionList[$action->getId()] = $action;
            $this->actionIdList[$actionKey] = $action->getId();
        }

        return $this->actionList[$actionId];
    }

    public function getAction( $entityType, $entityId )
    {
        $actionKey = $entityType .':'. $entityId;

        if ( empty($this->actionIdList[$actionKey]) )
        {
            $action = CNEWS_BOL_ActionDao::getInstance()->findAction($entityType, $entityId);

            if ( $action === null )
            {
                return null;
            }

            $activityList = $this->findActivityList($this->params, array($action->id));
            $action = $this->makeAction($action, $activityList);

            if ( $action === null )
            {
                return null;
            }

            $this->actionList[$action->getId()] = $action;
            $this->actionIdList[$actionKey] = $action->getId();
        }

        return $this->actionList[$this->actionIdList[$actionKey]];
    }

    public function getActionCount()
    {
        if ( $this->count === false )
        {
            return $this->findActionCount($this->params);
        }

        return $this->count;
    }

    abstract protected function findActionList( $params );
    abstract protected function findActionCount( $params );
    abstract protected function findActivityList( $params, $actionIdList );

    /**
     *
     * @param CNEWS_BOL_Action $dto
     * @return CNEWS_CLASS_Action
     */
    private function makeAction( $actionDto, $activityList )
    {
        if ( empty($activityList) )
        {
            return null;
        }
        
        $action = new CNEWS_CLASS_Action();
        $action->setId($actionDto->id);
        $action->setData( json_decode($actionDto->data, true) );
        $action->setEntity($actionDto->entityType, $actionDto->entityId);
        $action->setPluginKey($actionDto->pluginKey);
        $action->setFormat($actionDto->format);

        $action->setActivityList($activityList);

        return $action;
    }
}