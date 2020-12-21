<?php

class FRIENDS_CTRL_Action extends PEEP_ActionController
{
    /**
     * Request new friendship controller
     *
     * @param array $params
     * @throws Redirect404Exception
     * @throws AuthenticateException
     */
    public function request( $params )
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
           throw new AuthenticateException();
        }

        $requesterId = PEEP::getUser()->getId();

        $userId = (int) $params['id'];

        if ( BOL_UserService::getInstance()->isBlocked(PEEP::getUser()->getId(), $userId) )
        {
            throw new Redirect404Exception();
        }

        if (!PEEP::getUser()->isAuthorized('friends', 'add_friend'))
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('friends', 'add_friend');
            PEEP::getFeedback()->error($status['msg']);
        }

        $service = FRIENDS_BOL_Service::getInstance();

        if ( $service->findFriendship($requesterId, $userId) === null )
        {
            $service->request($requesterId, $userId);

            $service->onRequest($requesterId, $userId);

            PEEP::getFeedback()->info(PEEP::getLanguage()->text('friends', 'feedback_request_was_sent'));
        }
        else
        {
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('friends', 'feedback_request_already_sent_error_message'));
        }

        if ( isset( $params['backUri'] ) )
        {
            $this->redirect($params['backUri']);
        }

        $this->redirect($_SERVER['HTTP_REFERER']);
    }



    /**
     * Accept new friendship request
     *
     * @param array $params
     * @throws AuthenticateException
     */
    public function accept( $params )
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $requesterId = (int) $params['id'];
        $userId = PEEP::getUser()->getId();

        $service = FRIENDS_BOL_Service::getInstance();

        $frendshipDto = $service->accept($userId, $requesterId);

        if ( !empty($frendshipDto) )
        {
            $service->onAccept($userId, $requesterId, $frendshipDto);

            PEEP::getFeedback()->info(PEEP::getLanguage()->text('friends', 'feedback_request_accepted'));
        }

        if ( !empty($params['backUrl']) )
        {
            $this->redirect($params['backUrl']);
        }

        if ( $service->count(null, $userId, FRIENDS_BOL_Service::STATUS_PENDING) > 0 )
        {
            $backUrl = PEEP::getRouter()->urlForRoute('friends_lists', array('list'=>'got-requests'));
        }
        else
        {
            $backUrl = PEEP::getRouter()->urlForRoute('friends_list');
        }

        $this->redirect($backUrl);
    }

    /**
     * Ignore new friendship request
     *
     * @param array $params
     * @throws AuthenticateException
     */
    public function ignore( $params )
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $requesterId = (int) PEEP::getUser()->getId();
        $userId = (int) $params['id'];

        $service = FRIENDS_BOL_Service::getInstance();

        $service->ignore($userId, $requesterId);

        PEEP::getFeedback()->info(PEEP::getLanguage()->text('friends', 'feedback_request_ignored'));

        $this->redirect( PEEP::getRouter()->urlForRoute('friends_lists', array('list'=>'got-requests')) );
    }

    /**
     * Cancel friendship
     *
     * @param array $params
     * @throws AuthenticateException
     */
    public function cancel( $params )
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $requesterId = (int) $params['id'];
        $userId = (int) PEEP::getUser()->getId();

        $event = new PEEP_Event('friends.cancelled', array(
            'senderId' => $requesterId,
            'recipientId' => $userId
        ));

        PEEP::getEventManager()->trigger($event);

        PEEP::getFeedback()->info(PEEP::getLanguage()->text('friends', 'feedback_cancelled'));

        if ( isset($params['redirect']) )
        {
            $username = BOL_UserService::getInstance()->getUserName($requesterId);
            $backUrl = PEEP::getRouter()->urlForRoute('base_user_profile', array('username'=>$username));
            $this->redirect($backUrl);
        }

        $this->redirect( PEEP::getRouter()->urlForRoute('friends_lists', array('list'=>'sent-requests')) );
    }

    public function activate( $params )
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $requesterId = (int) $params['id'];
        $userId = (int) PEEP::getUser()->getId();

        FRIENDS_BOL_Service::getInstance()->activate($userId, $requesterId);

        PEEP::getFeedback()->info(PEEP::getLanguage()->text('friends', 'new_friend_added'));
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function ajax()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect403Exception();
        }

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $command = $_POST['command'];
        $data = json_decode($_POST['data'], true);

        $result = '';

        switch($command)
        {
            case 'friends-accept':
                $userId = (int) PEEP::getUser()->getId();
                $requesterId = (int) $data['id'];

                $service = FRIENDS_BOL_Service::getInstance();

                $frendshipDto = $service->accept($userId, $requesterId);

                if ( !empty($frendshipDto) )
                {
                    $service->onAccept($userId, $requesterId, $frendshipDto);
                }

                $feedback = PEEP::getLanguage()->text('friends', 'feedback_request_accepted');
                $result = "PEEP.info('{$feedback}');";
                break;
            
            case 'friends-ignore':
                $userId = (int) PEEP::getUser()->getId();
                $requesterId = (int) $data['id'];

                $service = FRIENDS_BOL_Service::getInstance();

                $service->ignore($requesterId, $userId);

                $feedback = PEEP::getLanguage()->text('friends', 'feedback_request_ignored');
                $result = "PEEP.info('{$feedback}');";
                break;
        }

        echo json_encode(array(
            'script' => $result
        ));

        exit;
    }
}
