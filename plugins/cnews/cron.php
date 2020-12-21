<?php

class CNEWS_Cron extends PEEP_Cron
{
    /**
     *
     * @var CNEWS_BOL_Service
     */
    private $service;
    private $commands = array();

    public function __construct()
    {
        parent::__construct();

        $this->addJob('deleteActionSet', 60); // 1 hour
        $this->addJob('deleteExpired', 7 * 3600 * 24); // once a week

        $this->service = CNEWS_BOL_Service::getInstance();

        $this->commands['changePrivacy'] = 'changePrivacy';
        $this->commands['updateFollowPermission'] = 'updateFollowPermission';
        $this->commands['update3500CronJob'] = 'update3500CronJob';
        $this->commands['deleteActions'] = 'deleteActions';
    }

    private function getActionInactivePeriod()
    {
        return 1;
    }

    public function run()
    {
        $commands = $this->service->findCronCommands();
        $completedCommands = array();

        foreach ( $commands as $commandDto )
        {
            /* @var $commandDto CNEWS_BOL_CronCommand */
            $command = trim($commandDto->command);

            if ( empty($this->commands[$command]) )
            {
                continue;
            }

            $method = $this->commands[$command];

            $data = json_decode($commandDto->data, true);
            $processData = json_decode($commandDto->processData, true);
            $r = $this->$method($data, $processData);

            if ( $r === true )
            {
                $completedCommands[] = $commandDto->id;
            }
            else
            {
                $commandDto->processData = json_encode($r);
                $this->service->saveCronCommand($commandDto);
            }
        }

        if ( !empty($completedCommands) )
        {
            $this->service->deleteCronCommands($completedCommands);
        }
    }

    // Commands

    private function deleteActions( $data, $processData )
    {
        $actionsCount = 10;

        $actionIds = empty($data['actionIds']) ? array() : $data['actionIds'];
        $processData = empty($processData) ? array() : $processData;

        $currentActions = array_diff($actionIds, $processData);
        $currentActions = array_values($currentActions);

        if ( empty($currentActions) )
        {
            return true;
        }

        $iterationsCount = count($currentActions);
        $iterationsCount = $iterationsCount > $actionsCount ? $actionsCount : $iterationsCount;

        for ( $i = 0; $i < $iterationsCount; $i++ )
        {
            $this->service->removeActionById($currentActions[$i]);
            $processData[] = $currentActions[$i];
        }

        return $processData;
    }

    private function changePrivacy( $data, $processData )
    {
        $userId = (int) $data['userId'];
        $privacyList = $data['privacy'];

        foreach ( $privacyList as $privacy => $activityKeys )
        {
            foreach ( $activityKeys as & $key )
            {
                $key = $userId . ':' . $key;
            }

            $this->service->setActivityPrivacyByKeyList($activityKeys, $privacy);
        }

        return true;
    }

    private function updateFollowPermission( $data, $processData )
    {
        $event = new BASE_CLASS_EventCollector('feed.collect_follow');
        PEEP::getEventManager()->trigger($event);

        foreach ( $event->getData() as $follow )
        {
            $follow['permission'] = empty($follow['permission']) ? CNEWS_BOL_Service::PRIVACY_EVERYBODY : $follow['permission'];

            $this->service->addFollow((int) $follow['userId'], trim($follow['feedType']), (int) $follow['feedId'], $follow['permission']);
        }

        return true;
    }

    private function update3500CronJob( $data, $processData )
    {
        $friends = PEEP::getEventManager()->call('plugin.friends.find_all_active_friendships');

        foreach ( $friends as $f )
        {
            $this->service->addFollow((int) $f->userId, 'user', (int) $f->friendId, 'friends_only');
            $this->service->addFollow((int) $f->friendId, 'user', (int) $f->userId, 'friends_only');
        }

        return true;
    }

    public function deleteActionSet()
    {
       CNEWS_BOL_Service::getInstance()->deleteActionSetByTimestamp( time() - (60 * 60) );
    }

    public function deleteExpired()
    {
        $this->service->markExpiredForDelete();
    }

}