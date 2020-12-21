<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CTRL_Users extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns menu component
     *
     * @return BASE_CMP_ContentMenu
     */
    private function getMenu()
    {
        $language = PEEP::getLanguage();

        $menuItems = array();

        $keys = array('recent', 'suspended', 'unverified', 'unapproved');
        $labels = array('recently_active', 'suspended', 'unverified', 'unapproved');
        $icons = array('clock', 'delete', 'mail', 'ok');

        $approveEnabled = PEEP::getConfig()->getValue('base', 'mandatory_user_approve');
        foreach ( $keys as $ord => $key )
        {
            if ( $key == 'unapproved' && !$approveEnabled )
            {
                continue;
            }
            
            $urlParams = $key == 'recent' ? array() : array('list' => $key);

            $item = new BASE_MenuItem();
            $item->setLabel($language->text('admin', 'menu_item_users_' . $labels[$ord]));
            $item->setUrl(PEEP::getRouter()->urlForRoute('admin_users_browse', $urlParams));
            $item->setKey($key);
            $item->setIconClass('peep_ic_' . $icons[$ord]);
            $item->setOrder($ord);

            array_push($menuItems, $item);
        }

        return new BASE_CMP_ContentMenu($menuItems);
    }

    /**
     * User list page controller
     *
     * @param array $params
     */
    public function index( array $params )
    {
        $language = PEEP::getLanguage();
        $userService = BOL_UserService::getInstance();

        // invite members
        $form = new Form('invite-members');

        $hidden = new HiddenField('invite_members');
        $hidden->setValue('1');
        $form->addElement($hidden);

        $emails = new Textarea('emails');
        $form->addElement($emails);
        $emails->setRequired();
        $emails->setHasInvitation(true);
        $emails->setInvitation($language->text('admin', 'invite_members_textarea_invitation_text', array('limit' => (int)PEEP::getConfig()->getValue('base', 'user_invites_limit'))));

        $submit = new Submit('submit');
        $submit->setValue($language->text('admin', 'invite_members_submit_label'));
        $form->addElement($submit);

        $this->addForm($form);

        if ( PEEP::getRequest()->isPost() && isset($_POST['invite_members']) )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                $emails = array_unique(preg_split('/\n/', $data['emails']));

                $emailList = array();

                foreach ( $emails as $email )
                {
                    if ( UTIL_Validator::isEmailValid(trim($email)) )
                    {
                        $emailList[] = trim($email);
                    }
                }

                if ( sizeof($emailList) > (int)PEEP::getConfig()->getValue('base', 'user_invites_limit') )
                {
                    PEEP::getFeedback()->error($language->text('admin', 'invite_members_max_limit_message', array('limit' => (int)PEEP::getConfig()->getValue('base', 'user_invites_limit'))));
                    $form->getElement('emails')->setValue($data['emails']);
                    $this->redirect();
                }

                if ( empty($emailList) )
                {
                    PEEP::getFeedback()->error($language->text('admin', 'invite_members_min_limit_message'));
                    $form->getElement('emails')->setValue($data['emails']);
                    $this->redirect();
                }

                foreach ( $emailList as $email )
                {                    
                    BOL_UserService::getInstance()->sendAdminInvitation($email);
                }

                PEEP::getFeedback()->info($language->text('admin', 'invite_members_success_message'));
                $this->redirect();
            }
        }

        $language->addKeyForJs('admin', 'invite_members_cap_label');
        $language->addKeyForJs('admin', 'admin_suspend_floatbox_title');

        $menu = $this->getMenu();
        $this->addComponent('menu', $menu);

        if ( !empty($_GET['search']) && !empty($_GET['search_by']) )
        {
            $extra = array('question' => $_GET['search_by'], 'value' => $_GET['search']);
            $type = 'search';
        }
        else
        {
            $extra = null;
            $type = isset($params['list']) ? $params['list'] : 'recent';
        }
        
        $buttons['suspend'] = array('name' => 'suspend', 'id' => 'suspend_user_btn', 'label' => $language->text('base', 'suspend_user_btn'), 'class' => 'peep_mild_red');
        $buttons['suspend']['js'] = ' $("#suspend_user_btn").click(function(e){ 
            e.preventDefault();
            PEEP.ajaxFloatBox("ADMIN_CMP_SetSuspendMessage", [],{width: 520, title: PEEP.getLanguageText(\'admin\', \'admin_suspend_floatbox_title\')}); 
            return false;
        }); ';
        
        $buttons['unverify'] = array('name' => 'email_unverify', 'id' => 'email_unverify_user_btn', 'label' => $language->text('base', 'mark_email_unverified_btn'), 'class' => 'peep_mild_red');
        $buttons['unsuspend'] = array('name' => 'reactivate', 'id' => 'unsuspend_user_btn', 'label' => $language->text('base', 'unsuspend_user_btn'), 'class' => 'peep_mild_green');
        $buttons['verify'] = array('name' => 'email_verify', 'id' => 'email_verify_user_btn', 'label' => $language->text('base', 'mark_email_verified_btn'), 'class' => 'peep_mild_green');
        $buttons['approve'] = array('name' => 'approve', 'id' => 'approve_user_btn', 'label' => $language->text('base', 'approve_user_btn'), 'class' => 'peep_mild_green');
        //$buttons['disapprove'] = array('name' => 'disapprove', 'id' => 'disapprove_user_btn', 'label' => $language->text('base', 'disapprove_user_btn'), 'class' => 'peep_mild_red');
        
        $par = new ADMIN_UserListParams();
        $par->setType($type);
        $par->setExtra($extra);
        
        switch ( $type )
        {
            case 'recent';
            case 'search':
                $par->addButton($buttons['suspend']);
                $par->addButton($buttons['unsuspend']);
                $par->addButton($buttons['unverify']);
                $par->addButton($buttons['verify']);
                $par->addButton($buttons['approve']);
                //$par->addButton($buttons['disapprove']);
                break;
                
            case 'suspended':
                $par->addButton($buttons['unsuspend']);
                break;
                
            case 'unverified':
                $par->addButton($buttons['verify']);
                break;
                
            case 'unapproved':
                $par->addButton($buttons['approve']);
                break;
        }
        
        $usersCmp = new ADMIN_CMP_UserList($par);
        $this->addComponent('userList', $usersCmp);

        if ( !PEEP::getRequest()->isAjax() )
        {
            PEEP::getDocument()->setHeading(PEEP::getLanguage()->text('admin', 'heading_browse_users'));
            PEEP::getDocument()->setHeadingIconClass('peep_ic_user');

            $menuElement = $menu->getElement($type);
            if ( $menuElement )
            {
                $menuElement->setActive(true);
            }
        }
        
        $this->assign('totalUsers', BOL_UserService::getInstance()->count(true));
        
        $question = PEEP::getConfig()->getValue('base', 'display_name_question');
        
        $searchQ = array(
            $question => $language->text('base', 'questions_question_'.$question.'_label'),
            'email' => $language->text('base', 'questions_question_email_label')
        );
        $this->assign('searchQ', $searchQ);
        
        $this->assign('currentSearch', array(
            'question' => !empty($_GET['search_by']) ? $_GET['search_by'] : '',
            'value' => !empty($_GET['search']) ? htmlspecialchars($_GET['search']) : ''
        ));
        
        $this->assign('userSearchUrl', PEEP::getRouter()->urlForRoute('admin_users_browse'));
    }

    public function roles( array $params )
    {        
        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-ui.min.js');

        $service = BOL_UserService::getInstance();

        $roleService = BOL_AuthorizationService::getInstance();

        $roles = $roleService->findNonGuestRoleList();
       

        $list = array();

        $total = $service->count(true);

        foreach ( $roles as $role )
        {            
            $userCount = $roleService->countUserByRoleId($role->getId());           

            $list[$role->getId()] = array(
                'dto' => $role,
                'userCount' => $userCount,
            );
        }
        
        $this->assign( 'set', $list );

        $this->assign( 'total', $total );

        $addRoleForm = new AddRoleForm();

        if ( PEEP::getRequest()->isPost() && $addRoleForm->isValid( $_POST ) )
        {
            $addRoleForm->process($addRoleForm->getValues());

            $this->redirect();
        }

        $this->addForm( $addRoleForm );
        
        PEEP::getLanguage()->addKeyForJs('admin', 'permissions_edit_role_btn');

        PEEP::getDocument()->setHeadingIconClass('peep_ic_user');
        PEEP::getDocument()->setHeading(PEEP::getLanguage()->text('admin', 'heading_user_roles'));
    }

    public function role( array $params )
    {
        if ( !empty($params['roleId']) )
        {
            $par = new ADMIN_UserListParams();
            $par->setType('role');
            $par->setExtra(array('roleId' => (int) $params['roleId']));
            
            $this->addComponent('userList', new ADMIN_CMP_UserList($par));

            $role = BOL_AuthorizationService::getInstance()->getRoleById((int) $params['roleId']);
            $roleLabel = PEEP::getLanguage()->text('base', 'authorization_role_' . $role->name);

            PEEP::getDocument()->setHeading(PEEP::getLanguage()->text('admin', 'heading_user_role', array('role' => $roleLabel)));
        }

        PEEP::getDocument()->setHeadingIconClass('peep_ic_user');

        $js = UTIL_JsGenerator::newInstance()
                ->newVariable('rolesUrl', PEEP::getRouter()->urlForRoute('admin_user_roles'))
                ->jQueryEvent('#back-to-roles', 'click', 'document.location.href = rolesUrl');

        PEEP::getDocument()->addOnloadScript($js);
    }

    public function deleteRoles()
    {
        $service = BOL_AuthorizationService::getInstance();

        if (empty($_POST['role']) || !is_array($_POST['role']))
        {
            $this->redirect(PEEP::getRouter()->urlFor('ADMIN_CTRL_Users', 'roles'));
        }

        foreach ( $_POST['role'] as $id )
        {
            $service->deleteRoleById($id);
        }

        $languageService = BOL_LanguageService::getInstance();

        $languageService->generateCache($languageService->getCurrent()->getId());

        PEEP::getFeedback()->info(PEEP::getLanguage()->text('admin', 'permissions_roles_deleted_msg'));

        $this->redirect(PEEP::getRouter()->urlFor('ADMIN_CTRL_Users', 'roles'));
    }

    public function ajaxReorder()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        if ( empty($_POST) )
        {
            exit('{}');
        }

        BOL_AuthorizationService::getInstance()->reorderRoles($_POST['order']);
        exit();
    }
    
    public function ajaxEditRole( )
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
        
        ADMIN_CMP_AuthorizationRoleEdit::process($_POST);
    }
    
}

class AddRoleForm extends Form
{

    public function __construct()
    {
        parent::__construct('add-role');

        $textField = new TextField('label');

        $this->addElement($textField->setRequired(true)->setLabel(PEEP::getLanguage()->text('admin', 'permissions_add_form_role_lbl')));

        $submit = new Submit('submit');

        $submit->setValue(PEEP::getLanguage()->text('admin', 'permissions_add_role_btn'));

        $this->addElement($submit);
    }

    public function process( $data )
    {
        $label = $data['label'];

        $service = BOL_AuthorizationService::getInstance();

        $service->addRole($label);

        PEEP::getFeedback()->info(PEEP::getLanguage()->text('admin', 'permissions_role_added_msg'));
    }
}