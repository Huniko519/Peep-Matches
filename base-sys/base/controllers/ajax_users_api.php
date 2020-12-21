<?php

class BASE_CTRL_AjaxUsersApi extends PEEP_ActionController
{
    private function checkAdmin()
    {
        if ( !PEEP::getUser()->isAuthorized('base') )
        {
            throw Exception("Not authorized action");
        }
    }

    private function checkAuthenticated()
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw Exception("Not authenticated user");
        }
    }

    public function rsp()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = trim($_GET['command']);
        $query = json_decode($_GET['params'], true);

        $response = call_user_func(array($this, $command), $query);
        
        /*try
        {
            $response = call_user_func(array($this, $command), $query);
        }
        catch ( Exception $e )
        {
            $response = array(
                "error" => $e->getMessage(),
                'type' => 'error'
            );
        }*/

        $response = empty($response) ? array() : $response;
        echo json_encode($response);
        exit;
    }

    private function suspend( $params )
    {
        $this->checkAdmin();

        BOL_UserService::getInstance()->suspend($params["userId"], $params["message"]);

        return array(
            "info" => PEEP::getLanguage()->text('base', 'user_feedback_profile_suspended')
        );
    }
    
    private function deleteUser( $params )
    {
        $this->checkAdmin();
        
        BOL_UserService::getInstance()->deleteUser($params["userId"]);

        return array(
            "info" => PEEP::getLanguage()->text('base', 'user_deleted_page_message')
        );
    }

    private function unsuspend( $params )
    {
        $this->checkAdmin();

        BOL_UserService::getInstance()->unsuspend($params["userId"]);

        return array(
            "info" => PEEP::getLanguage()->text('base', 'user_feedback_profile_unsuspended')
        );
    }

    private function block( $params )
    {
        $this->checkAuthenticated();
        BOL_UserService::getInstance()->block($params["userId"]);

        return array(
            "info" => PEEP::getLanguage()->text('base', 'user_feedback_profile_blocked')
        );
    }

    private function unblock( $params )
    {
        $this->checkAuthenticated();
        BOL_UserService::getInstance()->unblock($params["userId"]);

        return array(
            "info" => PEEP::getLanguage()->text('base', 'user_feedback_profile_unblocked')
        );
    }

    private function feature( $params )
    {
        $this->checkAdmin();
        BOL_UserService::getInstance()->markAsFeatured($params["userId"]);

        return array(
            "info" => PEEP::getLanguage()->text('base', 'user_feedback_marked_as_featured')
        );
    }

    private function unfeature( $params )
    {
        $this->checkAdmin();
        BOL_UserService::getInstance()->cancelFeatured($params["userId"]);

        return array(
            "info" => PEEP::getLanguage()->text('base', 'user_feedback_unmarked_as_featured')
        );
    }

}