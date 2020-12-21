<?php

class BASE_CTRL_ComponentPanel extends PEEP_ActionController
{
    /**
     *
     * @var BOL_ComponentAdminService
     */
    private $componentAdminService;
    /**
     *
     * @var BOL_ComponentEntityService
     */
    private $componentEntityService;

    public function __construct()
    {
        $this->componentAdminService = BOL_ComponentAdminService::getInstance();
        $this->componentEntityService = BOL_ComponentEntityService::getInstance();

        $controllersTemplate = PEEP::getPluginManager()->getPlugin('BASE')->getCtrlViewDir() . 'component_panel.html';
        $this->setTemplate($controllersTemplate);
    }

    public function render()
    {
        return parent::render();
    }

    private function action( $place, $userId, $customizeMode, $customizeRouts, $componentTemplate, $responderController = null )
    {
        $userCustomizeAllowed = (bool) $this->componentAdminService->findPlace($place)->editableByUser;

        if ( !$userCustomizeAllowed && $customizeMode )
        {
            $this->redirect($customizeRouts['normal']);
        }

        $schemeList = $this->componentAdminService->findSchemeList();

        $state = $this->componentAdminService->findCache($place);
        if ( empty($state) )
        {
            $state = array();
            $state['defaultComponents'] = $this->componentAdminService->findPlaceComponentList($place);
            $state['defaultPositions'] = $this->componentAdminService->findAllPositionList($place);
            $state['defaultSettings'] = $this->componentAdminService->findAllSettingList();
            $state['defaultScheme'] = (array) $this->componentAdminService->findSchemeByPlace($place);

            $this->componentAdminService->saveCache($place, $state);
        }

        $defaultComponents = $state['defaultComponents'];
        $defaultPositions = $state['defaultPositions'];
        $defaultSettings = $state['defaultSettings'];
        $defaultScheme = $state['defaultScheme'];

        if ( $userCustomizeAllowed )
        {
            $userCache = $this->componentEntityService->findEntityCache($place, $userId);

            if ( empty($userCache) )
            {
                $userCache = array();
                $userCache['userComponents'] = $this->componentEntityService->findPlaceComponentList($place, $userId);
                $userCache['userSettings'] = $this->componentEntityService->findAllSettingList($userId);
                $userCache['userPositions'] = $this->componentEntityService->findAllPositionList($place, $userId);

                $this->componentEntityService->saveEntityCache($place, $userId, $userCache);
            }

            $userComponents = $userCache['userComponents'];
            $userSettings = $userCache['userSettings'];
            $userPositions = $userCache['userPositions'];
        }
        else
        {
            $userComponents = array();
            $userSettings = array();
            $userPositions = array();
        }

        if ( empty($defaultScheme) && !empty($schemeList) )
        {
            $defaultScheme = reset($schemeList);
        }

        $componentPanel = new BASE_CMP_DragAndDropEntityPanel($place, $userId, $defaultComponents, $customizeMode, $componentTemplate, $responderController);
        $componentPanel->setAdditionalSettingList(array(
            'entityId' => $userId,
            'entity' => 'user'
        ));

        if ( !empty($customizeRouts) )
        {
            $componentPanel->allowCustomize($userCustomizeAllowed);
            $componentPanel->customizeControlCunfigure($customizeRouts['customize'], $customizeRouts['normal']);
        }

        $componentPanel->setSchemeList($schemeList);
        $componentPanel->setPositionList($defaultPositions);
        $componentPanel->setSettingList($defaultSettings);
        $componentPanel->setScheme($defaultScheme);

        /*
         * This feature was disabled for users
         * if ( !empty($userScheme) )
          {
          $componentPanel->setEntityScheme($userScheme);
          } */

        if ( !empty($userComponents) )
        {
            $componentPanel->setEntityComponentList($userComponents);
        }

        if ( !empty($userPositions) )
        {
            $componentPanel->setEntityPositionList($userPositions);
        }

        if ( !empty($userSettings) )
        {
            $componentPanel->setEntitySettingList($userSettings);
        }

        $this->assign('componentPanel', $componentPanel->render());
    }

    public function dashboard( $paramList )
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $this->setPageHeading(PEEP::getLanguage()->text('base', 'dashboard_heading'));
        $this->setPageHeadingIconClass('peep_ic_house');

        $customize = !empty($paramList['mode']) && $paramList['mode'] == 'customize';

        $place = BOL_ComponentService::PLACE_DASHBOARD;

        $template = $customize ? 'drag_and_drop_entity_panel_customize' : 'drag_and_drop_entity_panel';

        $customizeUrls = array(
            'customize' => PEEP::getRouter()->urlForRoute('base_member_dashboard_customize', array('mode' => 'customize')),
            'normal' => PEEP::getRouter()->urlForRoute('base_member_dashboard')
        );

        $userId = PEEP::getUser()->getId();

        $this->action($place, $userId, $customize, $customizeUrls, $template);

        $controllersTemplate = PEEP::getPluginManager()->getPlugin('BASE')->getCtrlViewDir() . 'widget_panel_dashboard.html';

        $this->setTemplate($controllersTemplate);

        $this->assign('isAdmin', PEEP::getUser()->isAdmin());
        $this->assign('isModerator', BOL_AuthorizationService::getInstance()->isModerator());
        
        $this->setDocumentKey('member_home');
    }

    public function myProfile( $paramList )
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $displayName = BOL_UserService::getInstance()->getDisplayName(PEEP::getUser()->getId());
        $this->setPageTitle(PEEP::getLanguage()->text('base', 'my_profile_title', array('username' => $displayName)));
        $this->setPageHeading(PEEP::getLanguage()->text('base', 'my_profile_heading', array('username' => $displayName)));

        $this->setPageTitle(PEEP::getLanguage()->text('base', 'profile_view_title', array('username' => $displayName)));
        PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('base', 'profile_view_description', array('username' => $displayName)));

        $event = new PEEP_Event('base.on_get_user_status', array('userId' => PEEP::getUser()->getId()));
        PEEP::getEventManager()->trigger($event);
        $status = $event->getData();

        if ( $status !== null )
        {
            $heading = PEEP::getLanguage()->text('base', 'user_page_heading_status', array('status' => $status, 'username' => $displayName));
            $this->setPageHeading($heading);
        }
        else
        {
            $this->setPageHeading(PEEP::getLanguage()->text('base', 'profile_view_heading', array('username' => $displayName)));
        }

        $this->setPageHeadingIconClass('peep_ic_user');

        $customize = !empty($paramList['mode']) && $paramList['mode'] == 'customize';

        if ( $customize )
        {
            PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, 'base', 'main_menu_my_profile');
        }

        $place = BOL_ComponentService::PLACE_PROFILE;

        $template = $customize ? 'drag_and_drop_entity_panel_customize' : 'drag_and_drop_entity_panel';

        $customizeUrls = array(
            'customize' => PEEP::getRouter()->urlForRoute('base_member_profile_customize', array('mode' => 'customize')),
            'normal' => PEEP::getRouter()->urlForRoute('base_member_profile')
        );

        $userId = PEEP::getUser()->getId();

        $cmp = PEEP::getClassInstance("BASE_CMP_ProfileActionToolbar", $userId);
        $this->addComponent('profileActionToolbar', $cmp);

        $this->action($place, $userId, $customize, $customizeUrls, $template);
    }

    public function profile( $paramList )
    {
        $userService = BOL_UserService::getInstance();
        /* @var $userDao BOL_User */
        $userDto = $userService->findByUsername($paramList['username']);

        if ( $userDto === null )
        {
            throw new Redirect404Exception();
        }

        if ( $userDto->id == PEEP::getUser()->getId() )
        {
            $this->myProfile($paramList);

            return;
        }

        if ( !PEEP::getUser()->isAuthorized('base', 'view_profile') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'view_profile');
            throw new AuthorizationException($status['msg']);
        }

        $eventParams = array(
            'action' => 'base_view_profile',
            'ownerId' => $userDto->id,
            'viewerId' => PEEP::getUser()->getId()
        );

        try
        {
            PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $ex )
        {
            $exception = new RedirectException(PEEP::getRouter()->urlForRoute('base_user_privacy_no_permission', array('username' => $userDto->username)));

            throw $exception;
        }

        $displayName = BOL_UserService::getInstance()->getDisplayName($userDto->id);

        $this->setPageTitle(PEEP::getLanguage()->text('base', 'profile_view_title', array('username' => $displayName)));
        PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('base', 'profile_view_description', array('username' => $displayName)));

        $event = new PEEP_Event('base.on_get_user_status', array('userId' => $userDto->id));
        PEEP::getEventManager()->trigger($event);
        $status = $event->getData();

        $headingSuffix = "";
        
        if ( !BOL_UserService::getInstance()->isApproved($userDto->id) )
        {
            $headingSuffix = ' <span class="peep_remark peep_small">(' . PEEP::getLanguage()->text("base", "pending_approval") . ')</span>';
        }
        
        if ( $status !== null )
        {
            $heading = PEEP::getLanguage()->text('base', 'user_page_heading_status', array('status' => $status, 'username' => $displayName));
            $this->setPageHeading($heading . $headingSuffix);
        }
        else
        {
            $this->setPageHeading(PEEP::getLanguage()->text('base', 'profile_view_heading', array('username' => $displayName)) . $headingSuffix);
        }

        $this->setPageHeadingIconClass('peep_ic_user');

        $this->assign('isSuspended', $userService->isSuspended($userDto->id));
        $this->assign('isAdminViewer', PEEP::getUser()->isAuthorized('base'));

        $place = BOL_ComponentService::PLACE_PROFILE;

        $cmp = PEEP::getClassInstance("BASE_CMP_ProfileActionToolbar", $userDto->id);
        $this->addComponent('profileActionToolbar', $cmp);

        $template = 'drag_and_drop_entity_panel';

        $this->action($place, $userDto->id, false, array(), $template);

        $controllersTemplate = PEEP::getPluginManager()->getPlugin('BASE')->getCtrlViewDir() . 'widget_panel_profile.html';
        $this->setTemplate($controllersTemplate);

        $this->setDocumentKey('member_profile');
    }

    public function privacyMyProfileNoPermission( $params )
    {
        $username = $params['username'];

        $user = BOL_UserService::getInstance()->findByUsername($username);

        if ( $user === null )
        {
            throw new Redirect404Exception();
        }

        if ( PEEP::getSession()->isKeySet('privacyRedirectExceptionMessage') )
        {
            $this->assign('message', PEEP::getSession()->get('privacyRedirectExceptionMessage'));
        }

        $avatarService = BOL_AvatarService::getInstance();

        $viewerId = PEEP::getUser()->getId();

        $userId = $user->id;

        $this->setPageHeading(PEEP::getLanguage()->text('base', 'profile_view_heading', array('username' => BOL_UserService::getInstance()->getDisplayName($userId))));
        $this->setPageHeadingIconClass('peep_ic_user');

        $avatar = $avatarService->getAvatarUrl($userId, 2);
        $this->assign('avatar', $avatar ? $avatar : $avatarService->getDefaultAvatarUrl(2));
        $roles = BOL_AuthorizationService::getInstance()->getRoleListOfUsers(array($userId));
        $this->assign('role', !empty($roles[$userId]) ? $roles[$userId] : null);

        $userService = BOL_UserService::getInstance();

        $this->assign('username', $username);

        $this->assign('avatarSize', PEEP::getConfig()->getValue('base', 'avatar_big_size'));
        
        $cmp = PEEP::getClassInstance("BASE_CMP_ProfileActionToolbar", $userId);
        $this->addComponent('profileActionToolbar', $cmp);

        $this->setTemplate(PEEP::getPluginManager()->getPlugin('base')->getCtrlViewDir() . 'user_view_privacy_no_permission.html');
    }

    public function index( $paramList )
    {
        $place = BOL_ComponentService::PLACE_INDEX;
        $customize = !empty($paramList['mode']) && $paramList['mode'] == 'customize';
        $allowCustomize = PEEP::getUser()->isAdmin();
        $template = 'drag_and_drop_index';

        if ( $customize )
        {
            if ( !PEEP::getUser()->isAuthenticated() )
            {
                throw new AuthenticateException();
            }

            if ( !$allowCustomize )
            {
                $this->redirect(PEEP::getRouter()->uriForRoute('base_index'));
            }
        }

        if ( $allowCustomize )
        {
            $template = $customize ? 'drag_and_drop_index_customize' : 'drag_and_drop_index';

            if ( $customize )
            {
                PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, 'base', 'main_menu_index');
            }
        }

        if ( $customize )
        {
            $masterPageFileDir = PEEP::getThemeManager()->getMasterPageTemplate('index-landing-arrange');
            PEEP::getDocument()->getMasterPage()->setTemplate($masterPageFileDir);
            
            $this->setDocumentKey('index_landing_arrange');
        }
        else
        {
            $this->setDocumentKey('landing_page');
        }
        
        $schemeList = $this->componentAdminService->findSchemeList();
        $state = $this->componentAdminService->findCache($place);

        if ( empty($state) )
        {
            $state = array();
            $state['defaultComponents'] = $this->componentAdminService->findPlaceComponentList($place);
            $state['defaultPositions'] = $this->componentAdminService->findAllPositionList($place);
            $state['defaultSettings'] = $this->componentAdminService->findAllSettingList();
            $state['defaultScheme'] = (array) $this->componentAdminService->findSchemeByPlace($place);

            $this->componentAdminService->saveCache($place, $state);
        }

        $defaultComponents = $state['defaultComponents'];
        $defaultPositions = $state['defaultPositions'];
        $defaultSettings = $state['defaultSettings'];
        $defaultScheme = $state['defaultScheme'];

        if ( empty($defaultScheme) && !empty($schemeList) )
        {
            $defaultScheme = reset($schemeList);
        }

        $componentPanel = new BASE_CMP_DragAndDropIndex($place, $defaultComponents, $customize, $template);
        $componentPanel->allowCustomize($allowCustomize);

        $customizeUrls = array(
            'customize' => PEEP::getRouter()->urlForRoute('base_index_customize', array('mode' => 'customize')),
            'normal' => PEEP::getRouter()->urlForRoute('base_index')
        );

        $componentPanel->customizeControlCunfigure($customizeUrls['customize'], $customizeUrls['normal']);

        $componentPanel->setSchemeList($schemeList);
        $componentPanel->setPositionList($defaultPositions);
        $componentPanel->setSettingList($defaultSettings);
        $componentPanel->setScheme($defaultScheme);

        /* $themeName = PEEP_Config::getInstance()->getValue('base', 'selectedTheme');
          $sidebarPosition = BOL_ThemeService::getInstance()->findThemeByName($themeName)->getSidebarPosition(); */

        $sidebarPosition = PEEP::getThemeManager()->getCurrentTheme()->getDto()->getSidebarPosition();
        $componentPanel->setSidebarPosition($sidebarPosition);

        $componentPanel->assign('adminPluginsUrl', PEEP::getRouter()->urlForRoute('admin_plugins_installed'));

        $this->addComponent('componentPanel', $componentPanel);
    }

    public function ajaxSaveAboutMe()
    {

        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        echo json_encode(BASE_CMP_AboutMeWidget::processForm($_POST));

        exit();
    }
}