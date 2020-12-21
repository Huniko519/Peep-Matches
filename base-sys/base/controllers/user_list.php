<?php

class BASE_CTRL_UserList extends PEEP_ActionController
{
    private $usersPerPage;

    public function __construct()
    {
        parent::__construct();
        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, 'base', 'users_main_menu_item');

        $this->setPageHeading(PEEP::getLanguage()->text('base', 'users_browse_page_heading'));
        $this->setPageTitle(PEEP::getLanguage()->text('base', 'users_browse_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_user');
        $this->usersPerPage = (int)PEEP::getConfig()->getValue('base', 'users_count_on_page');
        
        $this->assign('totalUsers', BOL_UserService::getInstance()->count(true));
    }

    public function index( $params )
    {
        $listType = empty($params['list']) ? 'latest' : strtolower(trim($params['list']));
        $language = PEEP::getLanguage();
        $this->addComponent('menu', self::getMenu($listType));

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? intval($_GET['page']) : 1;
        list($list, $itemCount) = $this->getData($listType, (($page - 1) * $this->usersPerPage), $this->usersPerPage);

        //$cmp = new BASE_Members($list, $itemCount, $this->usersPerPage, true, $listType);
        $cmp = PEEP::getClassInstance("BASE_Members", $list, $itemCount, $this->usersPerPage, true, $listType);
        
        $this->addComponent('cmp', $cmp);

        $this->assign('listType', $listType);

        $description = '';
        try
        {
            $description = BOL_LanguageService::getInstance()->getText(BOL_LanguageService::getInstance()->getCurrent()->getId(), 'base', 'users_list_'.$listType.'_meta_description');
        }
        catch ( Exception $e )
        {

        }

        if ( !empty($description) )
        {
            PEEP::getDocument()->setDescription($description);
        }
    }

    public function forApproval()
    {
        $this->setTemplate(PEEP::getPluginManager()->getPlugin('base')->getCtrlViewDir() . 'user_list_index.html');

        $language = PEEP::getLanguage();

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        list($list, $itemCount) = $this->getData('waiting-for-approval', (($page - 1) * $this->usersPerPage), $this->usersPerPage);

        //$cmp = new BASE_Members($list, $itemCount, $this->usersPerPage, false, 'waiting-for-approval');
        $cmp = PEEP::getClassInstance("BASE_Members", $list, $itemCount, $this->usersPerPage, false, 'waiting-for-approval');
        
        $this->addComponent('cmp', $cmp);
        
        $this->assign('listType', 'waiting-for-approval');
    }

    private function getData( $listKey, $first, $count )
    {
        $service = BOL_UserService::getInstance();
        return $service->getDataForUsersList($listKey, $first, $count);
    }

    public static function getMenu( $activeListType )
    {
        $language = PEEP::getLanguage();

        $menuArray = array(
            array(
                'label' => $language->text('base', 'user_list_menu_item_latest'),
                'url' => PEEP::getRouter()->urlForRoute('base_user_lists', array('list' => 'latest')),
                'iconClass' => 'peep_ic_clock',
                'key' => 'latest',
                'order' => 1
            ),
            array(
                'label' => $language->text('base', 'user_list_menu_item_online'),
                'url' => PEEP::getRouter()->urlForRoute('base_user_lists', array('list' => 'online')),
                'iconClass' => 'peep_ic_push_pin',
                'key' => 'online',
                'order' => 3
            ),
            array(
                'label' => $language->text('base', 'user_search_menu_item_label'),
                'url' => PEEP::getRouter()->urlForRoute('users-search'),
                'iconClass' => 'peep_ic_lens',
                'key' => 'search',
                'order' => 4
            )
        );

        if ( BOL_UserService::getInstance()->countFeatured() > 0 )
        {
            $menuArray[] =  array(
                'label' => $language->text('base', 'user_list_menu_item_featured'),
                'url' => PEEP::getRouter()->urlForRoute('base_user_lists', array('list' => 'featured')),
                'iconClass' => 'peep_ic_push_pin',
                'key' => 'featured',
                'order' => 2
            );
        }

        $event = new BASE_CLASS_EventCollector('base.add_user_list');
        PEEP::getEventManager()->trigger($event);
        $data = $event->getData();

        if ( !empty($data) )
        {
            $menuArray = array_merge($menuArray, $data);
        }

        $menu = new BASE_CMP_ContentMenu();

        foreach ( $menuArray as $item )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setLabel($item['label']);
            $menuItem->setIconClass($item['iconClass']);
            $menuItem->setUrl($item['url']);
            $menuItem->setKey($item['key']);
            $menuItem->setOrder(empty($item['order']) ? 999 : $item['order']);
            $menu->addElement($menuItem);

            if ( $activeListType == $item['key'] )
            {
                $menuItem->setActive(true);
            }
        }

        return $menu;
    }
}

class BASE_Members extends BASE_CMP_Users
{
    private $listKey;

    public function __construct( $list, $itemCount, $usersOnPage, $showOnline, $listKey )
    {
        $this->listKey = $listKey;

        if ( $this->listKey == 'birthdays' )
        {
            $showOnline = false;
        }

        parent::__construct($list, $itemCount, $usersOnPage, $showOnline);
    }

    public function getFields( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate->onView )
        {
            $qs[] = 'birthdate';
        }

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
        {
            $qs[] = 'sex';
        }

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $question )
        {

            $fields[$uid] = array();

            $age = '';

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);

            }

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 31; $i++ )
                {
                    $val = pow(2, $i);
                    if ( (int) $sex & $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) )
            {
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => $sexValue 
                );
            }
if ( !empty($age) )
            {
                $fields[$uid][] = array(
                    'label' => '',
                    'value' =>  $age . ' ' . PEEP::getLanguage()->text('base', 'questions_age_year_old')
                );
            }

            if ( !empty($question['birthdate']) )
            {
                $dinfo = date_parse($question['birthdate']);

                if ( $this->listKey == 'birthdays' )
                {
                    $birthdate = '';

                    if ( intval(date('d')) + 1 == intval($dinfo['day']) )
                    {
                        $questionList[$uid]['birthday'] = PEEP::getLanguage()->text('base', 'date_time_tomorrow');

                        $birthdate = '<span class="peep_green" style="font-weight: bold; text-transform: uppercase;">' . $questionList[$uid]['birthday'] . '</a>';
                    }
                    else if ( intval(date('d')) == intval($dinfo['day']) )
                    {
                        $questionList[$uid]['birthday'] = PEEP::getLanguage()->text('base', 'date_time_today');

                        $birthdate = '<span class="peep_green" style="font-weight: bold; text-transform: uppercase;">' . $questionList[$uid]['birthday'] . '</span>';
                    }
                    else
                    {
                        $birthdate = UTIL_DateTime::formatBirthdate($dinfo['year'], $dinfo['month'], $dinfo['day']);
                    }

                    $fields[$uid][] = array(
                        'label' => PEEP::getLanguage()->text('birthdays', 'birthday'),
                        'value' => $birthdate
                    );
                }
            }
        }

        return $fields;
    }
}