<?php

class BASE_CMP_TotalScore extends PEEP_Component
{

    public function __construct( $entityId, $entityType, $maxRate = 5 )
    {
        parent::__construct();

        $service = BOL_RateService::getInstance();

        $info = $service->findRateInfoForEntityItem($entityId, $entityType);

        $info['width'] = !isset($info['avg_score']) ? null : (int) floor((float) $info['avg_score'] / $maxRate * 100);
        $info['avgScore'] = !isset($info['avg_score']) ? '' : round($info['avg_score'], 2);
        $info['ratesCount'] = !isset($info['rates_count']) ? 0 : (int) $info['rates_count'];

        $this->assign('info', $info);
    }
}