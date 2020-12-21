<?php

class USERCREDITS_CLASS_UserCreditsPackProductAdapter implements PEEP_BillingProductAdapter
{
    const PRODUCT_KEY = 'user_credits_pack';

    const RETURN_ROUTE = 'usercredits.buy_credits';

    public function getProductKey()
    {
        return self::PRODUCT_KEY;
    }

    public function getProductOrderUrl()
    {
        return PEEP::getRouter()->urlForRoute(self::RETURN_ROUTE);
    }

    public function deliverSale( BOL_BillingSale $sale )
    {
        $packId = $sale->entityId;
        
        $creditsService = USERCREDITS_BOL_CreditsService::getInstance();
        
        $pack = $creditsService->findPackById($packId);
        
        if ( !$pack )
        {
            return false;
        }
        
        if ( $creditsService->increaseBalance($sale->userId, $pack->credits) )
        {
            $creditsService->sendPackPurchasedNotification($sale->userId, $pack->credits, $sale->totalAmount);
            
            $actionDto = USERCREDITS_BOL_CreditsService::getInstance()->findAction('usercredits', 'buy_credits');
        
            if ( !empty($actionDto) && !empty($actionDto->id) )
            {
                $creditsService->logAction($actionDto->id, $sale->userId, $pack->credits);
            }
            
            return true;
        }
        
        return false;
    }
}