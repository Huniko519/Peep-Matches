<?php

class BASE_CTRL_DeleteUser extends PEEP_ActionController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index( $params )
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        if ( PEEP::getUser()->isAdmin() )
        {
            throw new Redirect404Exception();
        }

        $language = PEEP::getLanguage();

        $this->setPageHeading($language->text('base', 'delete_user_index'));

        $userId = PEEP::getUser()->getId();


        if ( PEEP::getRequest()->isPost() && !(PEEP::getRequest()->isAjax()) )
        {
            if ( isset( $_POST['delete_user_button'] ) )
            {
                PEEP::getUser()->logout();

                BOL_UserService::getInstance()->deleteUser($userId, true);

                $this->redirect( PEEP::getRouter()->urlForRoute('base_index') );
            }

            if ( isset( $_POST['cansel_button'] ) )
            {
                $this->redirect( PEEP::getRouter()->urlForRoute('base_edit') );
            }
        }
    }
}
