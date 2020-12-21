<?php

class USERCREDITS_CTRL_Ajax extends PEEP_ActionController
{
    public function setCredits()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        if ( !PEEP::getUser()->isAuthorized('usercredits') )
        {
            throw new AuthenticateException();
        }
        
        $form = new USERCREDITS_CLASS_SetCreditsForm();

        if ( $form->isValid($_POST) )
        {
            $lang = PEEP::getLanguage();
            $creditService = USERCREDITS_BOL_CreditsService::getInstance();

            $values = $form->getValues();
            $userId = (int) $values['userId'];
            $balance = abs((int) $values['balance']);

            $balanceValues = $creditService->getBalanceForUserList(array($userId));
            $oldBalance = 0;
            
            if ( !empty($balanceValues[$userId]) )
            {
                $oldBalance = (int)$balanceValues[$userId];
            }
            
            $amount = $balance - $oldBalance;
            
            $creditService->setBalance($userId, $balance);

            $data = array('amount' => $amount, 'balance' => $balance ,'userId' => $userId);
            $event = new PEEP_Event('usercredits.set_by_moderator', $data);
            PEEP::getEventManager()->trigger($event);

            $balance = $creditService->getCreditsBalance($userId);
            exit(json_encode(array(
                "message" => $lang->text('usercredits', 'credit_balance_updated'),
                "credits" => $balance,
                "text" => PEEP::getLanguage()->text('usercredits', 'profile_toolbar_item_credits', array('credits' => $balance))
            )));
        }
    }

    public function grantCredits()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $form = new USERCREDITS_CLASS_GrantCreditsForm();

        if ( $form->isValid($_POST) )
        {
            $lang = PEEP::getLanguage();
            $creditService = USERCREDITS_BOL_CreditsService::getInstance();

            $grantorId = PEEP::getUser()->getId();
            $values = $form->getValues();
            $userId = (int) $values['userId'];
            $amount = abs((int) $values['amount']);

            $granted = $creditService->grantCredits($grantorId, $userId, $amount);
            $credits = $creditService->getCreditsBalance($grantorId);

            if ( $granted )
            {
                $data = array('amount' => $amount, 'grantorId' => $grantorId, 'userId' => $userId);
                $event = new PEEP_Event('usercredits.grant', $data);
                PEEP::getEventManager()->trigger($event);

                $data = array(
                    'message' => $lang->text('usercredits', 'credits_granted', array('amount' => $amount)),
                    'credits' => $credits
                );

            }
            else
            {
                $data = array('error' => $lang->text('usercredits', 'credits_grant_error'));
            }

            exit(json_encode($data));
        }
    }
}