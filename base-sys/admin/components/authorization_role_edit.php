<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CMP_AuthorizationRoleEdit extends PEEP_Component
{
    /**
     * @param int $roleId
     */
    public function __construct( $roleId )
    {
        parent::__construct();

        $language = PEEP::getLanguage();
        
        $role = BOL_AuthorizationService::getInstance()->getRoleById($roleId);
        
        $this->assign('role', $role);
        $this->assign('roleLabel', BOL_AuthorizationService::getInstance()->getRoleLabel($role->name));
        
        $form = new EditRoleForm($role);
        $this->addForm($form);
        
        $colors = array(
            '#999999', '#85db18', '#a7c520', '#046390', '#db4105', '#ff9800', '#01a2a6', 
            '#29d9c2', '#dc3522', '#1a9481', '#003d5c', '#046380', '#f23005', '#8b0f03', 
            '#2f6d7a', '#70a99a', '#b6d051', '#b52841', '#ff8939', '#e85f4d', '#590051', 
            '#303133', '#585956', '#99917c', '#ccc794', '#e66a00'
        );
        
        $this->assign('colors', $colors);
        
        $this->assign('defaultAvatarUrl', BOL_AvatarService::getInstance()->getDefaultAvatarUrl());
                
        $js = '$("input[name=displayLabel]", "#role-edit-cont").change(function(){
            if ( $(this).attr("checked") )
            {
                $("#color-select-cont").css("display", "block");
                $(".peep_avatar_label", "#role-edit-cont").css("display", "inline-block");
            }
            else
            {
                $("#color-select-cont").css("display", "none");
                $(".peep_avatar_label", "#role-edit-cont").css("display", "none");
            }
        });
        $(".color_sample", "#role-edit-cont").click(function(){
            var color = $(this).css("background-color");
            $("#label-color-field").val(color);
            $(".peep_avatar_label", "#role-edit-cont").css("background-color", color);
        });';
        
        if ( !$role->displayLabel )
        {
            $js .= '$(".peep_avatar_label", "#role-edit-cont").css("display", "none");';
        }
        if ( !empty($role->custom) )
        {
            $js .= '$(".peep_avatar_label", "#role-edit-cont").css("background-color", "'.$role->custom.'");';
        }
        
        PEEP::getDocument()->addOnloadScript($js);
    }
    
    public static function process( $data )
    {
        $authService = BOL_AuthorizationService::getInstance();
        
        $roleId = (int) $data['roleId'];
        $role = $authService->getRoleById($roleId);
        
        $resp = array();
        
        if ( !$role )
        {
            $resp['error'] = "Role not found";
            echo json_encode($resp);
            exit;
        }
        
        $role->displayLabel = $data['displayLabel'] == 'on' ? 1 : 0;
        $role->custom = $role->displayLabel ? $data['labelColor'] : null;
        
        $authService->saveRole($role);
        
        $resp['message'] = PEEP::getLanguage()->text('admin', 'permissions_role_updated');
        echo json_encode($resp);
        exit;
    }
}

class EditRoleForm extends Form
{
    public function __construct( BOL_AuthorizationRole $role )
    {
        parent::__construct('edit-role-form');
        
        $this->setAjax(true);
        $this->setAction(PEEP::getRouter()->urlFor('ADMIN_CTRL_Users', 'ajaxEditRole'));
        
        $roleId = new HiddenField('roleId');
        $roleId->setValue($role->id);
        $this->addElement($roleId);
        
        $displayLabel = new CheckboxField('displayLabel');
        $displayLabel->setValue($role->displayLabel);
        $this->addElement($displayLabel);
        
        $color = new HiddenField('labelColor');
        $color->setValue(!empty($role->custom) ? $role->custom : '#999999');
        $color->setId('label-color-field');
        $this->addElement($color);
        
        $submit = new Submit('save');
        $this->addElement($submit);
        
        $js = 'peepForms["'.$this->getName().'"].bind("success", function(data){
            if ( data.error != undefined ){
                PEEP.error(data.error);
            }
            if ( data.message != undefined ){
                PEEP.info(data.message);
            }
            document.location.reload();
        });';
        
        PEEP::getDocument()->addOnloadScript($js);
    }
}