<?php

class CNEWS_CLASS_SiteDriver extends CNEWS_CLASS_Driver
{
    protected function findActionList( $params )
    {
        return CNEWS_BOL_ActionDao::getInstance()->findSiteFeed(array($params['offset'], $params['displayCount']), $params['startTime'], $params['formats']);
    }

    protected function findActionCount( $params )
    {
        return CNEWS_BOL_ActionDao::getInstance()->findSiteFeedCount($params['startTime'], $params['formats']);
    }

    protected function findActivityList( $params, $actionIds )
    {
        return CNEWS_BOL_ActivityDao::getInstance()->findSiteFeedActivity($actionIds);
    }
}