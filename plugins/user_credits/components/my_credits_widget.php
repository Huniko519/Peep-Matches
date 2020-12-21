<?php

class USERCREDITS_CMP_MyCreditsWidget extends BASE_CLASS_Widget
{
    /**
     * @var USERCREDITS_BOL_CreditsService
     */
    private $creditsService;

    /**
     * Class constructor
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $this->creditsService = USERCREDITS_BOL_CreditsService::getInstance();
        
        $userId = PEEP::getUser()->getId();
        $lang = PEEP::getLanguage();
        
        if ( !$userId )
        {
            $this->setVisible(false); 
            return;
        }
        
        $balance = $this->creditsService->getCreditsBalance($userId);
                
        $this->assign('balance', $balance);
                
        $this->setSettingValue(
            self::SETTING_TOOLBAR,
            array(
                array(
                    'label' => $lang->text('usercredits', 'buy_more'),
                    'href' => PEEP::getRouter()->urlForRoute('usercredits.buy_credits')
                )
            )
        );

        $accountTypeId = $this->creditsService->getUserAccountTypeId($userId);
        $earning = (bool) $this->creditsService->findCreditsActions('earn', $accountTypeId);
        $losing = (bool) $this->creditsService->findCreditsActions('lose', $accountTypeId);
        $showCostOfActions = ($earning || $losing);
        
        $this->assign('showCostOfActions', $showCostOfActions);

        $script = '';
        if ( $showCostOfActions )
        {
            $script .=
            '$("#credits-link-cost-of-actions").click(function(){
                document.creditsEarnFloatbox = PEEP.ajaxFloatBox(
                    "USERCREDITS_CMP_CostOfActions", {}, { width : 432, title: '.json_encode($lang->text('usercredits', 'cost_of_actions')).'}
                );
            });
            ';
        }

        $history = (bool) $this->creditsService->countUserLogEntries($userId);
        $this->assign('showHistory', $history);

        if ( $history )
        {
            $script .=
            '$("#credits-link-history").click(function(){
                document.creditsHistoryFloatbox = PEEP.ajaxFloatBox(
                    "USERCREDITS_CMP_History", {}, { width : 500, title: '.json_encode($lang->text('usercredits', 'history')).'}
                );
            });
            ';
        }

        if ( mb_strlen($script) )
        {
            PEEP::getDocument()->addOnloadScript($script);
        }
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('usercredits', 'my_credits'),
            self::SETTING_ICON => self::ICON_INFO,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }
}