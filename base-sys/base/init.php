<?php

$peepBasePlugin = PEEP::getPluginManager()->getPlugin('base');
$themeManager = PEEP::getThemeManager();
$baseDecoratorsToRegister = array('form_base', 'main_menu', 'box_toolbar', 'avatar_item', 'box_cap', 'box', 'ipc',
    'mini_ipc', 'tooltip', 'paging', 'floatbox', 'button', 'user_list_item', 'button_list_item', 'ic');

foreach ( $baseDecoratorsToRegister as $name )
{
    $themeManager->addDecorator($name, $peepBasePlugin->getKey());
}

$classesToAutoload = array(
    'BASE_Members' => $peepBasePlugin->getCtrlDir() . 'user_list.php',
    'BASE_MenuItem' => $peepBasePlugin->getCmpDir() . 'menu.php',
    'BASE_CommentsParams' => $peepBasePlugin->getCmpDir() . 'comments.php',
    'BASE_ContextAction' => $peepBasePlugin->getCmpDir() . 'context_action.php',
    'JoinForm' => $peepBasePlugin->getCtrlDir() . 'join.php'
);

PEEP::getAutoloader()->addClassArray($classesToAutoload);

$router = PEEP::getRouter();

$router->addRoute(new PEEP_Route('static_sign_in', 'login', 'BASE_CTRL_User', 'standardSignIn'));
$router->addRoute(new PEEP_Route('base_forgot_password', 'forgot-password', 'BASE_CTRL_User', 'forgotPassword'));
$router->addRoute(new PEEP_Route('base_sign_out', 'sign-out', 'BASE_CTRL_User', 'signOut'));
$router->addRoute(new PEEP_Route('ajax-form', 'ajax-form', 'BASE_CTRL_AjaxForm', 'index'));

$router->addRoute(new PEEP_Route('users', 'members', 'BASE_CTRL_UserList', 'index', array('list' => array(PEEP_Route::PARAM_OPTION_HIDDEN_VAR => 'latest'))));
$router->addRoute(new PEEP_Route('base_user_lists', 'members/:list', 'BASE_CTRL_UserList', 'index'));

$router->addRoute(new PEEP_Route('users-waiting-for-approval', 'members/waiting-for-approval', 'BASE_CTRL_UserList', 'forApproval'));

$router->addRoute(new PEEP_Route('users-search', 'members/search', 'BASE_CTRL_UserSearch', 'index'));
$router->addRoute(new PEEP_Route('users-search-result', 'members/search/result-found', 'BASE_CTRL_UserSearch', 'result'));

$router->addRoute(new PEEP_Route('base_join', 'createaccount', 'BASE_CTRL_Join', 'index'));
$router->addRoute(new PEEP_Route('base_edit', 'profile/edit', 'BASE_CTRL_Edit', 'index'));
$router->addRoute(new PEEP_Route('base_edit_user_datails', 'profile/:userId/edit/', 'BASE_CTRL_Edit', 'index'));

$router->addRoute(new PEEP_Route('base_email_verify', 'email-verify', 'BASE_CTRL_EmailVerify', 'index'));
$router->addRoute(new PEEP_Route('base_email_verify_code_form', 'email-verify-form', 'BASE_CTRL_EmailVerify', 'verifyForm'));
$router->addRoute(new PEEP_Route('base_email_verify_code_check', 'email-verify-check/:code', 'BASE_CTRL_EmailVerify', 'verify'));

$router->addRoute(new PEEP_Route('base_massmailing_unsubscribe', 'unsubscribe/:id/:code', 'BASE_CTRL_Unsubscribe', 'index'));


// Drag And Drop panels
$router->addRoute(new PEEP_Route('base_member_dashboard', 'dashboard', 'BASE_CTRL_ComponentPanel', 'dashboard'));
$router->addRoute(new PEEP_Route('base_member_dashboard_customize', 'dashboard/customize', 'BASE_CTRL_ComponentPanel', 'dashboard', array(
        'mode' => array(PEEP_Route::PARAM_OPTION_HIDDEN_VAR => 'customize'
        ))));

$router->addRoute(new PEEP_Route('base_member_profile_customize', 'my-profile/customize', 'BASE_CTRL_ComponentPanel', 'myProfile', array(
        'mode' => array(PEEP_Route::PARAM_OPTION_HIDDEN_VAR => 'customize'
        ))));

$router->addRoute(new PEEP_Route('base_index_customize', 'index/customize', 'BASE_CTRL_ComponentPanel', 'index', array(
        'mode' => array(PEEP_Route::PARAM_OPTION_HIDDEN_VAR => 'customize'
        ))));

$router->addRoute(new PEEP_Route('base_index', 'index', 'BASE_CTRL_ComponentPanel', 'index'));
$router->addRoute(new PEEP_Route('base_member_profile', 'my-profile', 'BASE_CTRL_ComponentPanel', 'myProfile'));

$router->addRoute(new PEEP_Route('base_user_profile', ':username', 'BASE_CTRL_ComponentPanel', 'profile'));
$router->addRoute(new PEEP_Route('base_page_404', '404', 'BASE_CTRL_BaseDocument', 'page404'));
$router->addRoute(new PEEP_Route('base_page_403', '403', 'BASE_CTRL_BaseDocument', 'page403'));
$router->addRoute(new PEEP_Route('base_page_auth_failed', 'authorization-failed', 'BASE_CTRL_BaseDocument', 'authorizationFailed'));
$router->addRoute(new PEEP_Route('base_page_splash_screen', 'adult-warning', 'BASE_CTRL_BaseDocument', 'splashScreen'));
$router->addRoute(new PEEP_Route('base_page_alert', 'alert-page', 'BASE_CTRL_BaseDocument', 'alertPage'));
$router->addRoute(new PEEP_Route('base_page_confirm', 'confirm-page', 'BASE_CTRL_BaseDocument', 'confirmPage'));
$router->addRoute(new PEEP_Route('base_page_install_completed', 'install/done', 'BASE_CTRL_BaseDocument', 'installCompleted'));

$router->addRoute(new PEEP_Route('base_delete_user', 'profile/delete', 'BASE_CTRL_DeleteUser', 'index'));
$router->addRoute(new PEEP_Route('base.reset_user_password', 'reset-password/:code', 'BASE_CTRL_User', 'resetPassword'));
$router->addRoute(new PEEP_Route('base.reset_user_password_request', 'reset-password-request', 'BASE_CTRL_User', 'resetPasswordRequest'));
$router->addRoute(new PEEP_Route('base.reset_user_password_expired_code', 'reset-password-code-expired', 'BASE_CTRL_User', 'resetPasswordCodeExpired'));

$router->addRoute(new PEEP_Route('base_billing_completed', 'order/:hash/completed', 'BASE_CTRL_Billing', 'completed'));
$router->addRoute(new PEEP_Route('base_billing_completed_st', 'order/completed', 'BASE_CTRL_Billing', 'completed'));
$router->addRoute(new PEEP_Route('base_billing_canceled', 'order/:hash/canceled', 'BASE_CTRL_Billing', 'canceled'));
$router->addRoute(new PEEP_Route('base_billing_canceled_st', 'order/canceled', 'BASE_CTRL_Billing', 'canceled'));
$router->addRoute(new PEEP_Route('base_billing_error', 'order/incomplete', 'BASE_CTRL_Billing', 'error'));

$router->addRoute(new PEEP_Route('base_preference_index', 'member/settings', 'BASE_CTRL_Preference', 'index'));

$router->addRoute(new PEEP_Route('base_user_privacy_no_permission', 'profile/:username/privcy-enabled', 'BASE_CTRL_ComponentPanel', 'privacyMyProfileNoPermission'));

$router->addRoute(new PEEP_Route('base-api-server', 'api-server', 'BASE_CTRL_ApiServer', 'request'));
$router->addRoute(new PEEP_Route('base.robots_txt', 'robots.txt', 'BASE_CTRL_Base', 'robotsTxt'));

$router->addRoute(new PEEP_Route('base.complete_account_type', 'fill/account_type', 'BASE_CTRL_CompleteProfile', 'fillAccountType'));
$router->addRoute(new PEEP_Route('base.complete_required_questions', 'fill/profile_questions', 'BASE_CTRL_CompleteProfile', 'fillRequiredQuestions'));

$router->addRoute(new PEEP_Route('base.moderation_flags', 'moderation/reports/:group', 'BASE_CTRL_Moderation', 'flags'));
$router->addRoute(new PEEP_Route('base.moderation_flags_index', 'moderation/reports', 'BASE_CTRL_Moderation', 'flags'));
$router->addRoute(new PEEP_Route('base.moderation_tools', 'moderation', 'BASE_CTRL_Moderation', 'index'));


PEEP_ViewRenderer::getInstance()->registerFunction('display_rate', array('BASE_CTRL_Rate', 'displayRate'));

$eventHandler = new BASE_CLASS_EventHandler();
$eventHandler->init();

//PEEP::getRegistry()->setArray('users_page_data', array());
BASE_CLASS_ConsoleEventHandler::getInstance()->init();
BASE_CLASS_InvitationEventHandler::getInstance()->init();

