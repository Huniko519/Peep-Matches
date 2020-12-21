<?php

class CNEWS_CLASS_FeedDriver extends CNEWS_CLASS_Driver
{
    protected function findActionList( $params )
    {
        return CNEWS_BOL_ActionDao::getInstance()->findByFeed($params['feedType'], $params['feedId'], array($params['offset'], $params['displayCount']), $params['startTime'], $params['formats']);
    }

    protected function findActionCount( $params )
    {
        return CNEWS_BOL_ActionDao::getInstance()->findCountByFeed($params['feedType'], $params['feedId'], $params['startTime'], $params['formats']);
    }

    protected function findActivityList( $params, $actionIds )
    {
        return CNEWS_BOL_ActivityDao::getInstance()->findFeedActivity($params['feedType'], $params['feedId'], $actionIds);
    }
}