<?php

class COREVISITOR_CMP_UserFade extends PEEP_Component
{
    public function __construct()
    {
        $service = COREVISITOR_BOL_UserListService::getInstance();
        $plugin = PEEP::getPluginManager()->getPlugin('corevisitor');
        $userList = $service->getUserList(COREVISITOR_BOL_UserListService::LIST_LATEST, 0, COREVISITOR_BOL_UserListService::USER_COUNT);

        if ( ($count = count($userList)) < COREVISITOR_BOL_UserListService::USER_MIN_REQUIRED )
        {
            $this->setVisible(false);

            return;
        }
        elseif ( $count == COREVISITOR_BOL_UserListService::USER_MIN_REQUIRED || $count == COREVISITOR_BOL_UserListService::USER_MAX_REQUIRED )
        {
            $length = $count;
        }
        else
        {
            if ( $count < COREVISITOR_BOL_UserListService::USER_MAX_REQUIRED )
            {
                $length = COREVISITOR_BOL_UserListService::USER_MIN_REQUIRED;
            }
            else
            {
                $length = COREVISITOR_BOL_UserListService::USER_MAX_REQUIRED;
            }

            PEEP::getDocument()->addScriptDeclarationBeforeIncludes(
                UTIL_JsGenerator::composeJsString(';window.fadeUserParams = {$params};', array(
                    'params' => array(
                        'min' => COREVISITOR_BOL_UserListService::USER_MIN_REQUIRED,
                        'max' => $length,
                        'userList' => $userList
                    )
                ))
            );

            PEEP::getDocument()->addScript($plugin->getStaticJsUrl() . 'user_fade.js');
        }

        PEEP::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'user_fade.css');

        $this->assign('userList', array_slice($userList, 0, $length));
    }
}
