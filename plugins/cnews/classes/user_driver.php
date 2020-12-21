<?php

class CNEWS_CLASS_UserDriver extends CNEWS_CLASS_Driver
{
    protected function findActionList( $params )
    {
        return CNEWS_BOL_ActionDao::getInstance()->findByUser($params['feedId'], array($params['offset'], $params['displayCount']), $params['startTime'], $params['formats']);
    }

    protected function findActionCount( $params )
    {
        return CNEWS_BOL_ActionDao::getInstance()->findCountByUser($params['feedId'], $params['startTime'], $params['formats']);
    }

    protected function findActivityList( $params, $actionIds )
    {
        return CNEWS_BOL_ActivityDao::getInstance()->findUserFeedActivity($params['feedId'], $actionIds);
    }
}