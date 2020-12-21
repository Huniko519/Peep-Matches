<?php

class SPOTLIGHT_CMP_Floatbox extends PEEP_Component
{
    public function __construct()
    {
        parent::__construct();

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new Redirect403Exception();
        }

        $service = SPOTLIGHT_BOL_Service::getInstance();

        if ($service->findUserById(PEEP::getUser()->getId()))
        {
            $this->assign('userInList', true);
            $this->assign('text_notification', PEEP::getLanguage()->text('spotlight', 'text_remove_from_list'));

            $removeFromListForm = new RemoveFromSpotLightForm();
            $this->addForm($removeFromListForm);
        }
        else
        {
            $this->assign('userInList', false);

            if (PEEP::getPluginManager()->isPluginActive('usercredits'))
            {
                $creditService = USERCREDITS_BOL_CreditsService::getInstance();
                $action = $creditService->findAction('spotlight', 'add_to_list');
                $actionPrice = $creditService->findActionPriceForUser($action->id, PEEP::getUser()->getId());
                $amount = $actionPrice->amount;
            }
            else
            {
                $userCreditsAction = new SPOTLIGHT_CLASS_Credits();
                $amount = $userCreditsAction->getActionCost();
            }

            $status = BOL_AuthorizationService::getInstance()->getActionStatus('spotlight', 'add_to_list');

            if (isset($status['authorizedBy']) && $status['authorizedBy'] == 'base')
            {
                $this->assign('floatbox_text', PEEP::getLanguage()->text('spotlight', 'floatbox_text_simple'));
            }
            else
            {
                $this->assign('floatbox_text', PEEP::getLanguage()->text('spotlight', 'floatbox_text', array('amount'=>abs($amount))));
            }

            $addToListForm = new AddToSpotLightForm();

            $this->addForm($addToListForm);
        }
    }

    public static function process( $data )
    {
        $resp = array();
        $lang = PEEP::getLanguage();
        $service = SPOTLIGHT_BOL_Service::getInstance();

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            $resp['error'] = $lang->text('base', 'base_sign_in_cap_label');
            echo json_encode($resp);
            exit;
        }

        if ($service->findUserById(PEEP::getUser()->getId()))
        {
            if ( $data['remove_from_list'] )
            {
                $service->deleteUser(PEEP::getUser()->getId());

//                        //Newsfeed
//                        PEEP::getEventManager()->trigger(new PEEP_Event('feed.delete_item', array(
//                            'entityType' => 'add_to_spotlight',
//                            'entityId' => PEEP::getUser()->getId()
//                        )));

                $resp['message'] = PEEP::getLanguage()->text('spotlight', 'user_removed');
                $resp['removed'] = 1;
                echo json_encode($resp);
                exit;
            }

        }
        else
        {
            if ( $data['add_to_list'] )
            {
                if (!PEEP::getUser()->isAuthorized('spotlight', 'add_to_list'))
                {
                    $status = BOL_AuthorizationService::getInstance()->getActionStatus('spotlight', 'add_to_list');
                    $resp['error'] = $status['msg'];
                    echo json_encode($resp);
                    exit;
                }

                BOL_AuthorizationService::getInstance()->trackAction('spotlight', 'add_to_list');
                $service->addUser(PEEP::getUser()->getId());

                //            //Newsfeed
                //            $event = new PEEP_Event('feed.action', array(
                //                'pluginKey' => 'spotlight',
                //                'entityType' => 'add_to_spotlight',
                //                'entityId' => PEEP::getUser()->getId(),
                //                'userId' => PEEP::getUser()->getId()
                //            ), array(
                //                'string' => PEEP::getLanguage()->text('spotlight', 'user_entered_spot_light', array('displayName'=>BOL_UserService::getInstance()->getDisplayName(PEEP::getUser()->getId()))),
                //                'view' => array('iconClass' => 'peep_ic_heart'),
                //                'toolbar' => array(array(
                //                    'href' => PEEP::getRouter()->urlForRoute('spotlight-add-to-list'),
                //                    'label' =>  PEEP::getLanguage()->text('spotlight', 'add_yourself_here')
                //                ))
                //            ));
                //            PEEP::getEventManager()->trigger($event);

                $resp['message'] = PEEP::getLanguage()->text('spotlight', 'user_added');
                $resp['added'] = 1;
                echo json_encode($resp);
                exit;
            }
        }
    }

}

class RemoveFromSpotLightForm extends Form
{
    public function __construct()
    {
        parent::__construct('removeFromSpotLightForm');

        $this->setAjax(true);
        $this->setAction(PEEP::getRouter()->urlFor('SPOTLIGHT_CTRL_Index', 'ajax'));

        $this->setId('removeFromSpotLightForm');

        $remove_from_list = new HiddenField('remove_from_list');
        $remove_from_list->setValue(1);
        $this->addElement($remove_from_list);

        $submit = new Submit('remove');
        $submit->addAttribute('class', 'peep_ic_delete');
        $submit->setValue(PEEP::getLanguage()->text('spotlight', 'label_remove_btn_label'));

        $this->addElement($submit);

        $js = 'peepForms["'.$this->getName().'"].bind("success", function(data){
            if ( data.error != undefined ){
                PEEP.error(data.error);
            }
            if ( data.message != undefined ){
                PEEP.info(data.message);
            }

            if ( data.removed != undefined && data.removed == 1)
            {
                //$("#add_to_list").html("'.PEEP::getLanguage()->text('spotlight', 'add_yourself_here').'");
                PEEP.loadComponent("SPOTLIGHT_CMP_Index", {},
                    {
                      onReady: function( html ){
                         $(".peep_box_empty.dashboard-SPOTLIGHT_CMP_IndexWidget").empty().html(html);

                      }
                    });
            }

            spotLightFloatBox.close()
        });';

        PEEP::getDocument()->addOnloadScript($js);
    }
}

class AddToSpotLightForm extends Form
{
    public function __construct()
    {
        parent::__construct('addToSpotLightForm');

        $this->setAjax(true);
        $this->setAction(PEEP::getRouter()->urlFor('SPOTLIGHT_CTRL_Index', 'ajax'));

        $this->setId('addToSpotLightForm');

        $add_to_list = new HiddenField('add_to_list');
        $add_to_list->setValue(1);
        $this->addElement($add_to_list);

        $submit = new Submit('add');
        $submit->addAttribute('class', 'peep_ic_add');

        $status = BOL_AuthorizationService::getInstance()->getActionStatus('spotlight', 'add_to_list');
        if (isset($status['authorizedBy']) && $status['authorizedBy'] == 'base')
        {
            $submit->setValue(PEEP::getLanguage()->text('spotlight', 'yes_btn_label'));
        }
        else
        {
            $submit->setValue(PEEP::getLanguage()->text('spotlight', 'label_add_btn_label'));
        }


        $this->addElement($submit);

        $js = 'peepForms["'.$this->getName().'"].bind("success", function(data){
            if ( data.error != undefined ){
                PEEP.error(data.error);
            }
            if ( data.message != undefined ){
                PEEP.info(data.message);
            }

            if ( data.added != undefined && data.added == 1)
            {
                //$("#add_to_list").html("'.PEEP::getLanguage()->text('spotlight', 'remove_from_spot_light').'");

                PEEP.loadComponent("SPOTLIGHT_CMP_Index", {},
                    {
                      onReady: function( html ){
                         $(".peep_box_empty.dashboard-SPOTLIGHT_CMP_IndexWidget").empty().html(html);

                      }
                    });
            }

            spotLightFloatBox.close()
        });';

        PEEP::getDocument()->addOnloadScript($js);
    }
}