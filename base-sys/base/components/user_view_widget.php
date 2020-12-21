<?php

class BASE_CMP_UserViewWidget extends BASE_CLASS_Widget
{
    const USER_VIEW_PRESENTATION_TABS = 'tabs';

    const USER_VIEW_PRESENTATION_TABLE = 'table';

    /**
     * @param BASE_CLASS_WidgetParameter $params
     * @return \BASE_CMP_UserViewWidget
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $userId = $params->additionalParamList['entityId'];

        $viewerId = PEEP::getUser()->getId();

        $ownerMode = $userId == $viewerId;
        $adminMode = PEEP::getUser()->isAdmin() || PEEP::getUser()->isAuthorized('base');
        $isSuperAdmin = BOL_AuthorizationService::getInstance()->isSuperModerator($userId);

        $user = BOL_UserService::getInstance()->findUserById($userId);
        $accountType = $user->accountType;
        $questionService = BOL_QuestionService::getInstance();

        $questions = self::getUserViewQuestions($userId, $adminMode);
        
        if ( empty($questions['questions']) && $adminMode )
        {
            $list = BOL_QuestionService::getInstance()->getRequiredQuestionsForNewAccountType();
            
            $questions = self::getUserViewQuestions($userId, $adminMode, array_keys($list) );
        }

        $sectionsHtml = $questions['sections'];

        $sections = array_keys($sectionsHtml);

        $template = PEEP::getPluginManager()->getPlugin('base')->getViewDir() . 'components' . DS . 'user_view_widget_table.html';

        $userViewPresntation = PEEP::getConfig()->getValue('base', 'user_view_presentation');

        if ( $userViewPresntation === self::USER_VIEW_PRESENTATION_TABS )
        {
            $template = PEEP::getPluginManager()->getPlugin('base')->getViewDir() . 'components' . DS . 'user_view_widget_tabs.html';

            PEEP::getDocument()->addOnloadScript(" view = new UserViewWidget(); ");

            $jsDir = PEEP::getPluginManager()->getPlugin("base")->getStaticJsUrl();
            PEEP::getDocument()->addScript($jsDir . "user_view_widget.js");

            $this->addMenu($sections);
        }

        $script = ' $(".profile_hidden_field").hover(function(){PEEP.showTip($(this), {timeout:150, show: "'.PEEP::getLanguage()->text('base', 'base_invisible_profile_field_tooltip').'"})}, function(){PEEP.hideTip($(this))});';

        PEEP::getDocument()->addOnloadScript($script);

        $this->setTemplate($template);

        $accountTypes = $questionService->findAllAccountTypes();

        if ( !isset($sections[0]) )
        {
            $sections[0] = 0;
        }

        if ( count($accountTypes) > 1 )
        {
            if ( !isset($questionArray[$sections[0]]) )
            {
                $questionArray[$sections[0]] = array();
            }

            array_unshift($questionArray[$sections[0]], array('name' => 'accountType', 'presentation' => 'select'));
            $questionData[$userId]['accountType'] = $questionService->getAccountTypeLang($accountType);
        }

        if ( !isset($questionData[$userId]) )
        {
            $questionData[$userId] = array();
        } 

        $this->assign('firstSection', $sections[0]);
        //$this->assign('questionArray', $questionArray);
        $this->assign('sectionsHtml', $sectionsHtml);
        //$this->assign('questionData', $questionData[$userId]);
        $this->assign('ownerMode', $ownerMode);
        $this->assign('adminMode', $adminMode);
        $this->assign('superAdminProfile', $isSuperAdmin);
        $this->assign('profileEditUrl', PEEP::getRouter()->urlForRoute('base_edit'));

        if ( $adminMode && !$ownerMode )
        {
            $this->assign('profileEditUrl', PEEP::getRouter()->urlForRoute('base_edit_user_datails', array('userId' => $userId) ));
        }

        $this->assign('avatarUrl', BOL_AvatarService::getInstance()->getAvatarUrl($userId) );
        $this->assign('displayName', BOL_UserService::getInstance()->getDisplayName($userId) );
        //$this->assign('questionLabelList', $questionLabelList);
        $this->assign('userId', $userId);
    }

    public static function getStandardSettingValueList()
    {
        $language = PEEP::getLanguage();
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_TITLE => $language->text('base', 'view_index'),
            self::SETTING_FREEZE => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public function addMenu( $sections )
    {
        $menuItems = array();

        foreach ( $sections as $key => $section )
        {
            $item = new BASE_MenuItem();

            $item->setLabel(BOL_QuestionService::getInstance()->getSectionLang($section))
                ->setKey($section)
                ->setUrl('javascript://')
                ->setPrefix('menu')
                ->setOrder($key);

            if ( $key == 0 )
            {
                $item->setActive(true);
            }

            $menuItems[] = $item;
            $script = '$(\'li.menu_' . $section . '\').click(function(){view.showSection(\'' . $section . '\');});';
            PEEP::getDocument()->addOnloadScript($script);
        }

        $this->addComponent('menu', new BASE_CMP_ContentMenu($menuItems));
    }

    public static function getUserViewQuestions( $userId, $adminMode = false, $questionNames = array(), $sectionNames = null )
    {
        $questions = BOL_UserService::getInstance()->getUserViewQuestions($userId, $adminMode, $questionNames, $sectionNames);

        if ( !empty($questions['data'][$userId]) )
        {
            $data = array();
            foreach ( $questions['data'][$userId] as $key => $value )
            {
                if ( is_array($value) )
                {
                    $questions['data'][$userId][$key] = implode(', ', $value);
                }
            }
        }

        $sectionList = array();

        $userViewPresntation = PEEP::getConfig()->getValue('base', 'user_view_presentation');

        if ( !empty($questions['questions']) )
        {
            $sections = array_keys($questions['questions']);
            $count = 0;

            $isHidden = false;

            foreach ( $sections as $section )
            {
                if ( $userViewPresntation === self::USER_VIEW_PRESENTATION_TABS && $count != 0 )
                {
                    $isHidden = true;
                }

                $sectionQuestions = !empty($questions['questions'][$section]) ? $questions['questions'][$section] : array();
                $data = !empty($questions['data'][$userId]) ? $questions['data'][$userId] : array();
                $component = PEEP::getClassInstance( 'BASE_CMP_UserViewSection', $section, $sectionQuestions, $data, $questions['labels'], $userViewPresntation, $isHidden, array('userId' => $userId) );

                if ( !empty($component) )
                {
                    $sectionList[$section] = $component->render();
                }
                $count++;
            }
        }

        $questions['sections'] = $sectionList;

        return $questions;
    }
}
