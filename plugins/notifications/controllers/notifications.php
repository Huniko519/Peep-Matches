<?php

class NOTIFICATIONS_CTRL_Notifications extends PEEP_ActionController
{
    /**
     *
     * @var NOTIFICATIONS_BOL_Service
     */
    private $service;
    private $userId;

    public function __construct()
    {
        parent::__construct();

        $this->service = NOTIFICATIONS_BOL_Service::getInstance();
        $this->userId = PEEP::getUser()->getId();
    }

    public function settings()
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $contentMenu = new BASE_CMP_PreferenceContentMenu();
        $contentMenu->getElement('email_notifications')->setActive(true);
        $this->addComponent('contentMenu', $contentMenu);

        PEEP::getDocument()->setHeading(PEEP::getLanguage()->text('notifications', 'setup_page_heading'));
        PEEP::getDocument()->setHeadingIconClass('peep_ic_mail');
        PEEP::getDocument()->setTitle(PEEP::getLanguage()->text('notifications', 'setup_page_title'));

        $actions = $this->service->collectActionList();
        $settings = $this->service->findRuleList($this->userId);

        $form = new NOTIFICATIONS_SettingForm();
        $this->addForm($form);

        $processActions = array();

        foreach ( $actions as $action )
        {
            $field = new CheckboxField($action['action']);
            $field->setValue(!empty($action['selected']));

            if ( isset($settings[$action['action']]) )
            {
                $field->setValue((bool) $settings[$action['action']]->checked);
            }

            $form->addElement($field);

            $processActions[] = $action['action'];
        }

        if ( PEEP::getRequest()->isPost() )
        {
            $result = $form->process($_POST, $processActions, $settings);
            if ( $result )
            {
                PEEP::getFeedback()->info(PEEP::getLanguage()->text('notifications', 'settings_changed'));
            }
            else
            {
                PEEP::getFeedback()->warning(PEEP::getLanguage()->text('notifications', 'settings_not_changed'));
            }

            $this->redirect();
        }

        $tplActions = array();

        foreach ( $actions as $action )
        {
            if ( empty($tplActions[$action['section']]) )
            {
                $tplActions[$action['section']] = array(
                    'label' => $action['sectionLabel'],
                    'icon' => empty($action['sectionIcon']) ? '' : $action['sectionIcon'],
                    'actions' => array()
                );
            }

            $tplActions[$action['section']]['actions'][$action['action']] = $action;
        }



        $this->assign('actions', $tplActions);
    }

    public function unsubscribe( $params )
    {
        if ( isset($_GET['confirm-result']) && $_GET['confirm-result'] === "0" )
        {
            throw new RedirectException(PEEP_URL_HOME);
        }
        
        $code = $params['code'];
        $userId = $this->service->findUserIdByUnsubscribeCode($code);
        $lang = PEEP::getLanguage();

        if ( empty($userId) )
        {
            throw new RedirectAlertPageException($lang->text('notifications', 'unsubscribe_code_expired'));
        }

        if ( empty($_GET['confirm-result']) )
        {
            throw new RedirectConfirmPageException($lang->text('notifications', 'unsubscribe_confirm_msg'));
        }
        
        $activeActions = $this->service->collectActionList();
        $rules = $this->service->findRuleList($userId);

        $action = $params['action'] == 'all' ? null : $params['action'];

        foreach ( $activeActions as $actionInfo )
        {
            if ( $params['action'] != 'all' && $actionInfo['action'] != $params['action'] )
            {
                continue;
            }

            if ( empty($rules[$actionInfo['action']]) )
            {
                $rule = new NOTIFICATIONS_BOL_Rule();
                $rule->action = $actionInfo['action'];
                $rule->userId = $userId;
            }
            else
            {
                $rule = $rules[$actionInfo['action']];
            }

            $rule->checked = false;

            $this->service->saveRule($rule);
        }

        throw new RedirectAlertPageException($lang->text('notifications', 'unsubscribe_completed'));
    }

    public function test()
    {

        /* PEEP::getConfig()->addConfig('notifications', 'schedule_dhour', '00', 'Schedule hour');
          PEEP::getConfig()->addConfig('notifications', 'schedule_wday', '1', 'Schedule week day'); */

        require_once dirname(dirname(__FILE__)) . DS . 'cron.php';

        $cron = new NOTIFICATIONS_Cron();
        //$cron->run();
        $cron->deleteExpired();
        exit;
    }

    public function apiUnsubscribe( $params )
    {
        if ( empty($params['emails']) || !is_array($params['emails']) )
        {
            throw new InvalidArgumentException('Invalid email list');
        }

        foreach ( $params['emails'] as $email )
        {
            $user = BOL_UserService::getInstance()->findByEmail($email);

            if ( $user === null )
            {
                throw new LogicException('User with email ' . $email . ' not found');
            }

            $userId = $user->getId();

            $activeActions = $this->service->collectActionList();
            $rules = $this->service->findRuleList($userId);

            $action = empty($params['action']) ? null : $params['action'];

            foreach ( $activeActions as $actionInfo )
            {
                if ( $action !== null && $actionInfo['action'] != $action )
                {
                    continue;
                }

                if ( empty($rules[$actionInfo['action']]) )
                {
                    $rule = new NOTIFICATIONS_BOL_Rule();
                    $rule->action = $actionInfo['action'];
                    $rule->userId = $userId;
                }
                else
                {
                    $rule = $rules[$actionInfo['action']];
                }

                $rule->checked = false;

                $this->service->saveRule($rule);
            }
        }
    }
}

class NOTIFICATIONS_SettingForm extends Form
{

    public function __construct()
    {
        parent::__construct('notificationSettingForm');

        $language = PEEP::getLanguage();

        $field = new RadioField('schedule');

        
        $field->addOption(NOTIFICATIONS_BOL_Service::SCHEDULE_AUTO, $language->text('notifications', 'schedule_automatic'));
        $field->addOption(NOTIFICATIONS_BOL_Service::SCHEDULE_NEVER, $language->text('notifications', 'schedule_never'));

        $schedule = NOTIFICATIONS_BOL_Service::getInstance()->getSchedule(PEEP::getUser()->getId());
        $field->setValue($schedule);
        $this->addElement($field);

        $btn = new Submit('save');
        $btn->setValue($language->text('notifications', 'save_setting_btn_label'));

        $this->addElement($btn);
    }

    public function process( $data, $actions, $dtoList )
    {
        $userId = PEEP::getUser()->getId();
        $result = 0;
        $service = NOTIFICATIONS_BOL_Service::getInstance();

        if ( !empty($data['schedule']) )
        {
            $result += (int) $service->setSchedule($userId, $data['schedule']);

            unset($data['schedule']);
        }

        foreach ( $actions as $action )
        {
            /* @var $dto NOTIFICATIONS_BOL_Rule */
            if ( empty($dtoList[$action]) )
            {
                $dto = new NOTIFICATIONS_BOL_Rule();
                $dto->userId = $userId;
                $dto->action = $action;
            }
            else
            {
                $dto = $dtoList[$action];
            }

            $checked = (int) !empty($data[$action]);

            if ( !empty($dto->id) && $dto->checked == $checked )
            {
                continue;
            }

            $dto->checked = $checked;
            $result++;

            $service->saveRule($dto);
        }

        return $result;
    }
}

