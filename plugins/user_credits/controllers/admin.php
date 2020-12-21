<?php

class USERCREDITS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private function getMenu( $active = 'actions' )
    {
        $language = PEEP::getLanguage();

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('usercredits', 'tab_actions'));
        $item->setUrl(PEEP::getRouter()->urlForRoute('usercredits.admin'));
        $item->setKey('actions');
        $item->setOrder(1);
        $item->setActive($active == 'actions');
        $item->setIconClass('peep_ic_info');

        $item2 = new BASE_MenuItem();
        $item2->setLabel($language->text('usercredits', 'packs'));
        $item2->setUrl(PEEP::getRouter()->urlForRoute('usercredits.admin_packs'));
        $item2->setKey('packs');
        $item2->setOrder(2);
        $item2->setActive($active == 'packs');
        $item2->setIconClass('peep_ic_folder');
        
        $item3 = new BASE_MenuItem();
        $item3->setLabel($language->text('usercredits', 'settings'));
        $item3->setUrl(PEEP::getRouter()->urlForRoute('usercredits.admin_settings'));
        $item3->setKey('settings');
        $item3->setOrder(3);
        $item3->setActive($active == 'settings');
        $item3->setIconClass('peep_ic_gear_wheel');
        
        return new BASE_CMP_ContentMenu(array($item, $item2, $item3));
    } 
    
    /**
     * Default action
     */
    public function index()
    {
        $menu = $this->getMenu('actions');
        $this->addComponent('menu', $menu);
        $lang = PEEP::getLanguage();
        
        $creditService = USERCREDITS_BOL_CreditsService::getInstance();
        
        $accTypes = $creditService->findAccountTypes();
        if ( count($accTypes) > 1 )
        {
            $this->assign('route', PEEP::getRouter()->urlForRoute('usercredits.admin'));
        }
        $this->assign('types', $accTypes);
        $this->assign('showTypes', count($accTypes) > 1);
        $accType = !empty($_GET['type']) ? $_GET['type'] : null;
        if ( !$accType )
        {
            /* @var $def BOL_QuestionAccountType */
            $def = BOL_QuestionService::getInstance()->getDefaultAccountType();
            $accType = $def->id;
        }

        $this->assign('accType', $accType);

        $losing = $creditService->findCreditsActions('lose', $accType);
        $this->assign('losing', $losing);
        
        $earning = $creditService->findCreditsActions('earn', $accType);
        $this->assign('earning', $earning);
        
        $unset = $creditService->findCreditsActions('unset', $accType);
        $this->assign('unset', $unset);
        
        $this->setPageHeading($lang->text('usercredits', 'admin_config'));
        $this->setPageHeadingIconClass('peep_ic_gear_wheel');
        
        $script = '$("a.peep_action_price").click(function(){
            var actionId = $(this).attr("rel");
            var $input = $("#price_input_" + actionId);
            $(this).hide();
            $input.show();
            $input.focus();
            
            $input.bind("blur", function (){
                $(this).data("peepTipHide", true);
            });
            
            PEEP.showTip($input, {side: "right", width:200, timeout:7000, hideEvent: "blur",  show: "'.$lang->text('usercredits', 'setup_price_tip').'"});
        });';
        
        $script .= 'var func = function(){
            var $input = $(this);
            var $link = $input.parent().find("a.peep_action_price");
            var actionId = $link.attr("rel");

            PEEP.hideTip($input);
            $input.hide();
            
            if ( $link.html() == $input.val() )
            {
                $link.show();
                return;
            }
            
            $link.html($input.val());
            $link.show();
            
            PEEP.inProgressNode($input.parent());
            
            $.ajax({
                type: "POST",
                url: ' . json_encode(PEEP::getRouter()->urlFor('USERCREDITS_CTRL_Admin', 'ajaxUpdateAmount')) . ',
                data: "actionId=" + actionId + "&accountTypeId=" + ' .json_encode($accType). ' + "&price=" + $input.val(),
                dataType: "json",
                success : function(data){
                    if ( data.reload != undefined && data.reload ){
                        document.location.reload();
                    }
                    else { PEEP.activateNode($input.parent()); }
                }
            });
        };
        
        $("input.price_input").keyup(function(e) { 
            if ( e.which == 13 )
            {
                func.apply(this);
            } 
        });
        
        $("input.price_input").blur(function(){
            func.apply(this);
        });

        $(".lbutton_wrap .action_enable").click(function(){
            $(this).closest("tr").removeClass("peep_disabled_state").addClass("peep_enabled_state");
            $.ajax({
                type: "POST",
                url: ' . json_encode(PEEP::getRouter()->urlFor('USERCREDITS_CTRL_Admin', 'ajaxUpdateStatus')) . ',
                data: "disabled=0&action=" + $(this).data("action") + "&accountTypeId=" + ' . json_encode($accType). ',
                dataType: "json",
                success: function(data){ }
            });
        });

        $(".lbutton_wrap .action_disable").click(function(){
            $(this).closest("tr").removeClass("peep_enabled_state").addClass("peep_disabled_state");
            $.ajax({
                type: "POST",
                url: ' . json_encode(PEEP::getRouter()->urlFor('USERCREDITS_CTRL_Admin', 'ajaxUpdateStatus')) . ',
                data: "disabled=1&action=" + $(this).data("action") + "&accountTypeId=" + ' . json_encode($accType). ',
                dataType: "json",
                success: function(data){ }
            });
        });
        ';
        
        PEEP::getDocument()->addOnloadScript($script);
        
        $this->assign('imagesUrl', PEEP::getThemeManager()->getCurrentTheme()->getStaticImagesUrl());
    }
    
    public function ajaxUpdateAmount( )
    {
        if ( !empty($_POST['actionId']) )
        {
            $creditService = USERCREDITS_BOL_CreditsService::getInstance();
            $actionId = (int) $_POST['actionId'];
            $accTypeId = (int) $_POST['accountTypeId'];
            
            $action = $creditService->findActionById($actionId);
            
            if ( $action )
            {
                $actionPrice = $creditService->findActionPrice($actionId, $accTypeId);
                
                $oldAmount = $actionPrice->amount;
                $actionPrice->amount = (int) $_POST['price'];
                
                if ( $oldAmount == $actionPrice->amount )
                {
                    $result['reload'] = true;
                    exit(json_encode($result));
                }
                
                $creditService->updateCreditsActionPrice($actionPrice);
                $params = array(
                    'pluginKey' => $action->pluginKey,
                    'actionKey' => $action->actionKey,
                    'amount' => $actionPrice->amount
                );
                $event = new PEEP_Event('usercredits.action_update_amount', $params);
                PEEP::getEventManager()->trigger($event);
                
                $result['reload'] = false;
                
                if ( $oldAmount * $actionPrice->amount <= 0 )
                {
                    $result['reload'] = true;
                }
                
                exit(json_encode($result));
            }
        }
    }

    public function ajaxUpdateStatus()
    {
        if ( !empty($_POST['action']) )
        {
            $creditService = USERCREDITS_BOL_CreditsService::getInstance();

            $accTypeId = (int) $_POST['accountTypeId'];
            $actionId = (int) $_POST['action'];
            $actionPrice = $creditService->findActionPrice($actionId, $accTypeId);
            $action = $creditService->findActionById($actionId);

            if ( $actionPrice )
            {
                $actionPrice->disabled = (int) $_POST['disabled'];

                $creditService->updateCreditsActionPrice($actionPrice);

                $params = array(
                    'pluginKey' => $action->pluginKey,
                    'actionKey' => $action->actionKey,
                    'actionId' => $actionId,
                    'disabled' => $actionPrice->disabled
                );
                $event = new PEEP_Event('usercredits.action_update_disabled_status', $params);
                PEEP::getEventManager()->trigger($event);

                exit(json_encode(array()));
            }
        }

        exit(json_encode(array()));
    }
    
    public function packs()
    {
        $menu = $this->getMenu('packs');
        $this->addComponent('menu', $menu);

        $creditService = USERCREDITS_BOL_CreditsService::getInstance();
        $lang = PEEP::getLanguage();
        
        if ( !empty($_GET['delPack']) )
        {
            if ( $creditService->deletePackById((int)$_GET['delPack']) )
            {
                PEEP::getFeedback()->info($lang->text('usercredits', 'pack_deleted'));
            }
            
            $this->redirectToAction('packs');
        }
        
        $form = new AddPackForm();
        $this->addForm($form);
        
        if ( PEEP::getRequest()->isPost() )
        {
            if ( $_POST['form_name'] == 'add-pack-form' && $form->isValid($_POST) )
            {
                $values = $form->getValues();
                
                $pack = new USERCREDITS_BOL_Pack();
                $pack->credits = (int) $values['credits'];
                $pack->accountTypeId = !empty($values['accType']) ? $values['accType'] : null;
                $pack->price = floatval($values['price']);
                
                if ( $creditService->addPack($pack) )
                {
                    PEEP::getFeedback()->info($lang->text('usercredits', 'pack_added'));
                }
                
                $this->redirect();
            }
            else if ( $_POST['form_name'] == 'update-packs-form' )
            {
                if ( !empty($_POST['credits']) && !empty($_POST['price']) )
                {
                    foreach ( $_POST['credits'] as $packId => $credits )
                    {
                        if ( !$pack = $creditService->findPackById($packId) )
                        {
                            continue;
                        }

                        $pack->credits = (int) $credits;
                        $pack->price = floatval($_POST['price'][$packId]);
                        $creditService->addPack($pack);
                    }
                    
                    PEEP::getFeedback()->info($lang->text('usercredits', 'packs_updated'));
                }
                
                $this->redirect();
            }
        }
        
        $accTypes = $creditService->findAccountTypes();
        if ( count($accTypes) > 1 )
        {
            $this->assign('route', PEEP::getRouter()->urlForRoute('usercredits.admin_packs'));
        }
        $this->assign('types', $accTypes);
        $accType = !empty($_GET['type']) ? $_GET['type'] : null;
        $this->assign('accType', $accType);
        $form->getElement('accType')->setValue($accType);
        
        $this->setPageHeading(PEEP::getLanguage()->text('usercredits', 'admin_config'));
        $this->setPageHeadingIconClass('peep_ic_gear_wheel');
        
        $packs = $creditService->getPackList($accType);
        $this->assign('packs', $packs);
        
        $this->assign('currency', BOL_BillingService::getInstance()->getActiveCurrency());
    }
    
    public function settings()
    {
        $menu = $this->getMenu('settings');
        $this->addComponent('menu', $menu);

        $creditService = USERCREDITS_BOL_CreditsService::getInstance();
        $lang = PEEP::getLanguage();
        
        $form = new SaveConfigForm();
        $this->addForm($form);
        
        if ( PEEP::getRequest()->isPost() )
        {
            PEEP::getConfig()->saveConfig('usercredits', 'allow_grant_credits', !empty($_POST['allow_grant_credits']) ? 1 : 0 );
                
            PEEP::getFeedback()->info($lang->text('usercredits', 'settings_saved'));
            $this->redirect();
        }
    }
}


class AddPackForm extends Form
{
    public function __construct()
    {
        parent::__construct('add-pack-form');
        
        $lang = PEEP::getLanguage();

        $credits = new TextField('credits');
        $credits->setRequired(true);
        $credits->setLabel($lang->text('usercredits', 'credits'));
        $this->addElement($credits);
        
        $accType = new HiddenField('accType');
        $this->addElement($accType);
        
        $price = new TextField('price');
        $price->setRequired(true);
        $price->setLabel($lang->text('usercredits', 'price'));
        $this->addElement($price);
        
        $submit = new Submit('add');
        $submit->setValue($lang->text('usercredits', 'add'));
        $this->addElement($submit);
    }
}

class SaveConfigForm extends Form
{
    public function __construct()
    {
        parent::__construct('save-config-form');
        
        $lang = PEEP::getLanguage();
        
        $element = new CheckboxField('allow_grant_credits');
        $element->setLabel($lang->text('usercredits', 'allow_grant_credits_label'));
        $element->setValue(PEEP::getConfig()->getValue('usercredits', 'allow_grant_credits'));
        $this->addElement($element);
        
        $submit = new Submit('save');
        $submit->setValue($lang->text('usercredits', 'save'));
        $this->addElement($submit);
    }
}
