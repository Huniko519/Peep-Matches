<?php

class BASE_CMP_GiveUserRole extends PEEP_Component
{

    /**
     * @param integer $userId
     */
    public function __construct( $userId )
    {
        parent::__construct();

        $user = BOL_UserService::getInstance()->findUserById((int) $userId);

        if ( !PEEP::getUser()->isAuthorized('base') || $user === null )
        {
            $this->setVisible(false);
            return;
        }

        $aService = BOL_AuthorizationService::getInstance();
        $roleList = $aService->findNonGuestRoleList();

        $form = new Form('give-role');
        $form->setAjax(true);
        $form->setAction(PEEP::getRouter()->urlFor('BASE_CTRL_User', 'updateUserRoles'));
        $hidden = new HiddenField('userId');
        $form->addElement($hidden->setValue($userId));
        $userRoles = $aService->findUserRoleList($user->getId());

        $userRolesIdList = array();
        foreach ( $userRoles as $role )
        {
            $userRolesIdList[] = $role->getId();
        }

        $tplRoleList = array();

        /* @var $role BOL_AuthorizationRole */
        foreach ( $roleList as $role )
        {
            $field = new CheckboxField('roles[' . $role->getId() . ']');
            $field->setLabel(PEEP::getLanguage()->text('base', 'authorization_role_' . $role->getName()));
            $field->setValue(in_array($role->getId(), $userRolesIdList));
            if (in_array($role->getId(), $userRolesIdList) && $role->getSortOrder() == 1)
            {
                $field->addAttribute('disabled', 'disabled');
            }

            $form->addElement($field);

            $tplRoleList[$role->sortOrder] = $role;
        }

        ksort($tplRoleList);

        $form->addElement(new Submit('submit'));

        PEEP::getDocument()->addOnloadScript(
            "peepForms['{$form->getName()}'].bind('success', function(data){
                if( data.result ){
                    if( data.result == 'success' ){
                         window.baseChangeUserRoleFB.close();
                         window.location.reload();
                         //PEEP.info(data.message);
                    }
                    else if( data.result == 'error'){
                        PEEP.error(data.message);
                    }
                }
		})");

        $this->addForm($form);
        $this->assign('list', $tplRoleList);
    }
}