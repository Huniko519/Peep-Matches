<?php

class BASE_CTRL_Rate extends PEEP_ActionController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function updateRate()
    {
        if ( empty($_POST['entityId']) || empty($_POST['entityType']) || empty($_POST['rate']) || empty($_POST['ownerId']) )
        {
            exit(json_encode(array('errorMessage' => 'Invalid request')));
        }

        $service = BOL_RateService::getInstance();

        $entityId = (int) $_POST['entityId'];
        $entityType = trim($_POST['entityType']);
        $rate = (int) $_POST['rate'];
        $ownerId = (int) $_POST['ownerId'];
        $userId = PEEP::getUser()->getId();

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            exit(json_encode(array('errorMessage' => PEEP::getLanguage()->text('base', 'rate_cmp_auth_error_message'))));
        }

        if ( $userId === $ownerId )
        {
            exit(json_encode(array('errorMessage' => PEEP::getLanguage()->text('base', 'rate_cmp_owner_cant_rate_error_message'))));
        }

        if ( false )
        {
            //TODO add authorization error
            exit(json_encode(array('errorMessage' => 'Auth error')));
        }

        if ( BOL_UserService::getInstance()->isBlocked(PEEP::getUser()->getId(), $ownerId) )
        {
            exit(json_encode(array('errorMessage' => PEEP::getLanguage()->text('base', 'user_block_message'))));
        }

        $rateItem = $service->findRate($entityId, $entityType, $userId);

        if ( $rateItem === null )
        {
            $rateItem = new BOL_Rate();
            $rateItem->setEntityId($entityId)->setEntityType($entityType)->setUserId($userId)->setActive(true);
        }

        $rateItem->setScore($rate)->setTimeStamp(time());

        $service->saveRate($rateItem);

        $totalScoreCmp = new BASE_CMP_TotalScore($entityId, $entityType);

        exit(json_encode(array('totalScoreCmp' => $totalScoreCmp->render(), 'message' => PEEP::getLanguage()->text('base', 'rate_cmp_success_message'))));
    }

    public static function displayRate( array $params )
    {
        $service = BOL_RateService::getInstance();

        $minRate = 1;
        $maxRate = $service->getConfig(BOL_RateService::CONFIG_MAX_RATE);

        if ( !isset($params['avg_rate']) || (float) $params['avg_rate'] < $minRate || (float) $params['avg_rate'] > $maxRate )
        {
            return '_INVALID_RATE_PARAM_';
        }

        $width = (int) floor((float) $params['avg_rate'] / $maxRate * 100);

        return '<div class="inactive_rate_list"><div class="active_rate_list" style="width:' . $width . '%;"></div></div>';
    }
}