<?php

class BASE_CTRL_SuspendedUser extends PEEP_ActionController
{

    public function index()
    {

    }

    public function suspend( $params )
    {
        if ( !PEEP::getUser()->isAuthorized('base') || empty($params['id']) || empty($params['message']) )
        {
            exit;
        }

        $id = (int) $params['id'];
        $message = $params['message'];

        $userService = BOL_UserService::getInstance();
        $userService->suspend($id, $message);

        PEEP::getFeedback()->info(PEEP::getLanguage()->text('base', 'user_feedback_profile_suspended'));

        $this->redirect($_GET['backUrl']);
    }

    public function unsuspend( $params )
    {
        if ( !PEEP::getUser()->isAuthorized('base') || empty($params['id']) )
        {
            exit;
        }

        $id = (int) $params['id'];

        $userService = BOL_UserService::getInstance();
        $userService->unsuspend($id);

        PEEP::getFeedback()->info(PEEP::getLanguage()->text('base', 'user_feedback_profile_unsuspended'));

        $this->redirect($_GET['backUrl']);
    }

    public function ajaxRsp()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect403Exception();
        }

        $response = array();

        if ( empty($_GET['userId']) || empty($_GET['command']) )
        {
            echo json_encode($response);
            exit;
        }

        $userId = (int) $_GET['userId'];
        $command = $_GET['command'];

        switch ( $command )
        {
            case "suspend":
                BOL_UserService::getInstance()->suspend($userId);
                $response["info"] = PEEP::getLanguage()->text('base', 'user_feedback_profile_suspended');
                break;

            case "unsuspend":
                BOL_UserService::getInstance()->unsuspend($userId);
                $response["info"] = PEEP::getLanguage()->text('base', 'user_feedback_profile_unsuspended');
                break;
        }

        echo json_encode($response);
        exit;
    }
}