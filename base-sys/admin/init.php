<?php
/* Peepmatches Light By Peepdev co */

$plugin = PEEP::getPluginManager()->getPlugin('admin');

PEEP::getRouter()->addRoute(new PEEP_Route('admin_default', 'admin-board', 'ADMIN_CTRL_Base', 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('admin_finance', 'admin/earnings', 'ADMIN_CTRL_Finance', 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('admin_settings_language', 'admin/languagephrases', 'ADMIN_CTRL_Languages', 'index'));

PEEP::getRouter()->addRoute(new PEEP_Route('admin_settings_language_mod', 'admin/languages/mod', 'ADMIN_CTRL_Languages', 'mod'));

PEEP::getRouter()->addRoute(new PEEP_Route('admin_developer_tools_language', 'admin/dev-tools/languagephrases', 'ADMIN_CTRL_Languages', 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('admin_developer_tools_language_mod', 'admin/dev-tools/languages/mod', 'ADMIN_CTRL_Languages', 'mod'));

PEEP::getAutoloader()->addClass('ColorField', $plugin->getClassesDir() . 'form_fields.php');
PEEP::getAutoloader()->addClass('ADMIN_UserListParams', $plugin->getCmpDir() . 'user_list.php');

$router = PEEP::getRouter();

$router->addRoute(new PEEP_Route('admin_permissions', 'admin/permissions', 'ADMIN_CTRL_Permissions', 'index'));
$router->addRoute(new PEEP_Route('admin_permissions_roles', 'admin/permissions/usergroups', 'ADMIN_CTRL_Permissions', 'roles'));
$router->addRoute(new PEEP_Route('admin_permissions_moderators', 'admin/permissions/moderators', 'ADMIN_CTRL_Permissions', 'moderators'));
$router->addRoute(new PEEP_Route('admin_user_roles', 'admin/users/roles', 'ADMIN_CTRL_Users', 'roles'));
$router->addRoute(new PEEP_Route('admin_users_browse_membership_owners', 'admin/users/role/:roleId', 'ADMIN_CTRL_Users', 'role'));

$router->addRoute(new PEEP_Route('questions_index', 'admin/questions/account-types', 'ADMIN_CTRL_Questions', 'accountTypes'));
$router->addRoute(new PEEP_Route('questions_account_types', 'admin/questions/account-types', 'ADMIN_CTRL_Questions', 'accountTypes'));
$router->addRoute(new PEEP_Route('questions_properties', 'admin/questions/pages', 'ADMIN_CTRL_Questions', 'pages'));


$router->addRoute(new PEEP_Route('admin_themes_choose', 'admin/themes', 'ADMIN_CTRL_Themes', 'chooseTheme'));
$router->addRoute(new PEEP_Route('admin_themes_edit', 'admin/theme', 'ADMIN_CTRL_Theme', 'settings'));
$router->addRoute(new PEEP_Route('admin_themes_add_new', 'admin/themes/add', 'ADMIN_CTRL_Themes', 'addTheme'));

$router->addRoute(new PEEP_Route('admin_pages_edit_external', 'admin/pages/edit-external/id/:id', 'ADMIN_CTRL_PagesEditExternal', 'index'));
$router->addRoute(new PEEP_Route('admin_pages_edit_local', 'admin/pages/edit-local/id/:id', 'ADMIN_CTRL_PagesEditLocal', 'index'));
$router->addRoute(new PEEP_Route('admin_pages_edit_plugin', 'admin/pages/edit-plugin/id/:id', 'ADMIN_CTRL_PagesEditPlugin', 'index'));

$router->addRoute(new PEEP_Route('admin_pages_add', 'admin/pages/add/type/:type', 'ADMIN_CTRL_Pages', 'index'));
$router->addRoute(new PEEP_Route('admin_pages_main', 'admin/pages/manage', 'ADMIN_CTRL_Pages', 'manage'));
$router->addRoute(new PEEP_Route('admin_pages_splash_screen', 'admin/pages/splash-screen', 'ADMIN_CTRL_Pages', 'splashScreen'));
$router->addRoute(new PEEP_Route('admin_pages_maintenance', 'admin/pages/maintenance', 'ADMIN_CTRL_Pages', 'maintenance'));

$router->addRoute(new PEEP_Route('admin_pages_user_dashboard', 'admin/user-dashboard', 'ADMIN_CTRL_Components', 'dashboard'));
$router->addRoute(new PEEP_Route('admin_pages_user_profile', 'admin/user-profile', 'ADMIN_CTRL_Components', 'profile'));

$router->addRoute(new PEEP_Route('admin_pages_user_settings', 'admin/user-settings', 'ADMIN_CTRL_UserSettings', 'index'));

$router->addRoute(new PEEP_Route('admin_plugins_installed', 'admin/plugins', 'ADMIN_CTRL_Plugins', 'index'));
$router->addRoute(new PEEP_Route('admin_plugins_available', 'admin/available', 'ADMIN_CTRL_Plugins', 'available'));
$router->addRoute(new PEEP_Route('admin_plugins_add', 'admin/plugins/add', 'ADMIN_CTRL_Plugins', 'add'));

$router->addRoute(new PEEP_Route('admin_delete_roles', 'admin/users/delete-roles', 'ADMIN_CTRL_Users', 'deleteRoles'));
$router->addRoute(new PEEP_Route('admin.roles.reorder', 'admin/users/ajax-reorder', 'ADMIN_CTRL_Users', 'ajaxReorder'));
$router->addRoute(new PEEP_Route('admin.roles.edit-role', 'admin/users/ajax-edit-role', 'ADMIN_CTRL_Users', 'ajaxEditRole'));
$router->addRoute(new PEEP_Route('admin_users_browse', 'admin/users/:list', 'ADMIN_CTRL_Users', 'index', array('list' => array('default' => 'recent'))));

$router->addRoute(new PEEP_Route('admin_settings_main', 'admin/settings', 'ADMIN_CTRL_Settings', 'index'));
$router->addRoute(new PEEP_Route('admin_settings_user', 'admin/settings/user', 'ADMIN_CTRL_Settings', 'user'));
$router->addRoute(new PEEP_Route('admin_settings_mail', 'admin/settings/email', 'ADMIN_CTRL_Settings', 'mail'));
$router->addRoute(new PEEP_Route('admin_settings_page', 'admin/settings/page', 'ADMIN_CTRL_Settings', 'page'));
$router->addRoute(new PEEP_Route('admin_settings_user_input', 'admin/settings/user-input', 'ADMIN_CTRL_Settings', 'userInput'));

$router->addRoute(new PEEP_Route('admin_massmailing', 'admin/mass-mailing', 'ADMIN_CTRL_MassMailing', 'index'));
$router->addRoute(new PEEP_Route('admin_restrictedusernames', 'admin/restricted-usernames', 'ADMIN_CTRL_RestrictedUsernames', 'index'));

$router->addRoute(new PEEP_Route('admin_languages_index', 'admin/languages', 'ADMIN_CTRL_Languages', 'index'));
$router->addRoute(new PEEP_Route('admin_theme_css', 'admin/theme/css', 'ADMIN_CTRL_Theme', 'css'));
$router->addRoute(new PEEP_Route('admin_theme_settings', 'admin/theme/settings', 'ADMIN_CTRL_Theme', 'settings'));


$router->addRoute(new PEEP_Route('admin_core_update_request', 'admin/update-core', 'ADMIN_CTRL_Plugins', 'coreUpdateRequest'));


function admin_on_application_finalize( PEEP_Event $event )
{
    PEEP::getLanguage()->addKeyForJs('admin', 'edit_language');
}
PEEP::getEventManager()->bind(PEEP_EventManager::ON_FINALIZE, 'admin_on_application_finalize');

function admin_add_auth_labels( BASE_CLASS_EventCollector $event )
{
    $language = PEEP::getLanguage();
    $event->add(
        array(
            'admin' => array(
                'label' => $language->text('admin', 'auth_group_label'),
                'actions' => array()
            )
        )
    );
}
PEEP::getEventManager()->bind('admin.add_auth_labels', 'admin_add_auth_labels');

$handler = new ADMIN_CLASS_EventHandler();
$handler->init();