<?php

class BASE_CMP_Rate extends PEEP_Component
{

    public function __construct( $pluginKey, $entityType, $entityId, $ownerId )
    {
        parent::__construct();

        $service = BOL_RateService::getInstance();

        $maxRate = $service->getConfig(BOL_RateService::CONFIG_MAX_RATE);

        $cmpId = uniqid();

        $entityId = (int) $entityId;
        $entityType = trim($entityType);
        $ownerId = (int) $ownerId;

        if ( PEEP::getUser()->isAuthenticated() )
        {
            $userRateItem = $service->findRate($entityId, $entityType, PEEP::getUser()->getId());

            if ( $userRateItem !== null )
            {
                $userRate = $userRateItem->getScore();
            }
            else
            {
                $userRate = null;
            }
        }
        else
        {
            $userRate = null;
        }

        $this->assign('maxRate', $maxRate);
        $this->addComponent('totalScore', new BASE_CMP_TotalScore($entityId, $entityType, $maxRate));
        $this->assign('cmpId', $cmpId);

        $jsParamsArray = array(
            'cmpId' => $cmpId,
            'userRate' => $userRate,
            'entityId' => $entityId,
            'entityType' => $entityType,
            'itemsCount' => $maxRate,
            'respondUrl' => PEEP::getRouter()->urlFor('BASE_CTRL_Rate', 'updateRate'),
            'ownerId' => $ownerId
        );

        PEEP::getDocument()->addOnloadScript("var rate$cmpId = new PeepRate(" . json_encode($jsParamsArray) . "); rate$cmpId.init();");
    }
}