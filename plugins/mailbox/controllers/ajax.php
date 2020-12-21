<?php

class MAILBOX_CTRL_Ajax extends PEEP_ActionController
{
    private $conversationService;

    public function init()
    {
        if (!PEEP::getRequest()->isAjax())
        {
            throw new Redirect404Exception();
        }

        if (!PEEP::getUser()->isAuthenticated())
        {
            echo json_encode('User is not authenticated');
            exit;
        }

        $this->conversationService = MAILBOX_BOL_ConversationService::getInstance();
    }

    public function getHistory()
    {
        $userId = PEEP::getUser()->getId();
        $conversationId = (int)$_POST['convId'];
        $beforeMessageId = (int)$_POST['messageId'];

        $data = $this->conversationService->getConversationHistory($conversationId, $beforeMessageId);

        exit(json_encode($data));
    }

    public function newMessage()
    {
        $form = PEEP::getClassInstance("MAILBOX_CLASS_NewMessageForm");
        /* @var $user MAILBOX_CLASS_NewMessageForm */

        if ($form->isValid($_POST))
        {
            $result = $form->process();
            exit(json_encode($result));
        }
        else
        {
            exit(json_encode(array($form->getErrors())));
        }
    }

    public function updateUserInfo()
    {
        //DDoS check
        if ( empty($_SESSION['lastUpdateRequestTimestamp']) )
        {
            $_SESSION['lastUpdateRequestTimestamp'] = time();
        }
        else if ( (time() - (int) $_SESSION['lastUpdateRequestTimestamp']) < 3 )
        {
            exit('{error: "Too much requests"}');
        }

        $_SESSION['lastUpdateRequestTimestamp'] = time();

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        if ($errorMessage = $conversationService->checkPermissions())
        {
            exit(json_encode(array('error'=>$errorMessage)));
        }

        /* @var BOL_User $user */
        $user = null;

        if ( !empty($_POST['userId']) )
        {
            $user = BOL_UserService::getInstance()->findUserById($_POST['userId']);

            if (!$user)
            {
                $info = array(
                    'warning' => true,
                    'message' => 'User not found',
                    'type' => 'error'
                );
                exit(json_encode($info));
            }

            if ( !PEEP::getAuthorization()->isUserAuthorized($user->getId(), 'mailbox', 'reply_to_chat_message') )
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', 'reply_to_chat_message', array('userId'=>$user->getId()));

                if ($status['status'] == BOL_AuthorizationService::STATUS_DISABLED)
                {
                    $info = array(
                        'warning' => true,
                        'message' => PEEP::getLanguage()->text('mailbox', 'user_is_not_authorized_chat', array('username' => BOL_UserService::getInstance()->getDisplayName($user->getId()))),
                        'type' => 'warning'
                    );
                    exit(json_encode($info));
                }
            }

            $eventParams = array(
                'action' => 'mailbox_invite_to_chat',
                'ownerId' => $user->getId(),
                'viewerId' => PEEP::getUser()->getId()
            );

            try
            {
                PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
            }
            catch ( RedirectException $e )
            {
                //TODOS return message that has been set in a privacy value
                $info = array(
                    'warning' => true,
                    'message' => PEEP::getLanguage()->text('mailbox', 'warning_user_privacy_friends_only', array('displayname' => BOL_UserService::getInstance()->getDisplayName($user->getId()))),
                    'type' => 'warning'
                );
                exit(json_encode($info));
            }

            if ( BOL_UserService::getInstance()->isBlocked(PEEP::getUser()->getId(), $user->getId()) )
            {
                $errorMessage = PEEP::getLanguage()->text('base', 'user_block_message');
                $info = array(
                    'warning' => true,
                    'message' => $errorMessage,
                    'type' => 'error'
                );
                exit(json_encode($info));
            }

            if (empty( $_POST['checkStatus'] ) || $_POST['checkStatus'] != 2)
            {
                $onlineStatus = BOL_UserService::getInstance()->findOnlineStatusForUserList(array($user->getId()));
                if (!$onlineStatus[$user->getId()])
                {
                    $displayname = BOL_UserService::getInstance()->getDisplayName($user->getId());
                    $info = array(
                        'warning' => true,
                        'message' => PEEP::getLanguage()->text('mailbox', 'user_went_offline', array('displayname'=>$displayname)),
                        'type' => 'warning'
                    );
                    exit(json_encode($info));
                }
            }

            $info = $conversationService->getUserInfo($user->getId());
            exit(json_encode($info));
        }

        exit();
    }

    public function settings()
    {
        if (isset($_POST['soundEnabled']))
        {
            $_POST['soundEnabled'] = $_POST['soundEnabled'] === 'false' ? false : true;

            BOL_PreferenceService::getInstance()->savePreferenceValue('mailbox_user_settings_enable_sound', $_POST['soundEnabled'], PEEP::getUser()->getId());
        }

        if (isset($_POST['showOnlineOnly']))
        {
            $_POST['showOnlineOnly'] = $_POST['showOnlineOnly'] === 'false' ? false : true;
            BOL_PreferenceService::getInstance()->savePreferenceValue('mailbox_user_settings_show_online_only', $_POST['showOnlineOnly'], PEEP::getUser()->getId());

        }

        exit('true');
    }

    public function authorization(){
        $result = MAILBOX_BOL_AjaxService::getInstance()->authorizeAction($_POST);
        exit(json_encode($result));
    }

    public function ping()
    {
        $params = json_decode($_POST['request'], true);

        $event = new PEEP_Event('mailbox.ping', array('params'=>$params, 'command'=>'mailbox_ping'));
        PEEP::getEventManager()->trigger($event);

        exit( json_encode($event->getData()) );
    }

    public function rsp()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect403Exception;
        }

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            echo json_encode(array());
            exit;
        }

        $kw = empty($_GET['term']) ? null : $_GET['term'];
        $idList = empty($_GET['idList']) ? null : $_GET['idList'];

        $context = empty($_GET["context"]) ? 'user' : $_GET["context"];
        $userId = PEEP::getUser()->getId();

        $entries = MAILBOX_BOL_AjaxService::getInstance()->getSuggestEntries($userId, $kw, $idList, $context);

        echo json_encode($entries);
        exit;
    }

    /**
     * Deprecated see AjaxService / bulkActions
     */
    public function bulkOptions()
    {
        $userId = PEEP::getUser()->getId();

        switch($_POST['actionName'])
        {
            case 'markUnread':
                $count = MAILBOX_BOL_ConversationService::getInstance()->markConversation($_POST['convIdList'], $userId, MAILBOX_BOL_ConversationService::MARK_TYPE_UNREAD);
                $message = PEEP::getLanguage()->text('mailbox', 'mark_unread_message', array('count'=>$count));
                break;
            case 'markRead':
                $count = MAILBOX_BOL_ConversationService::getInstance()->markConversation($_POST['convIdList'], $userId, MAILBOX_BOL_ConversationService::MARK_TYPE_READ);
                $message = PEEP::getLanguage()->text('mailbox', 'mark_read_message', array('count'=>$count));
                break;
            case 'delete':
                $count = MAILBOX_BOL_ConversationService::getInstance()->deleteConversation($_POST['convIdList'], $userId);
                $message = PEEP::getLanguage()->text('mailbox', 'delete_message', array('count'=>$count));
                break;
        }

        exit(json_encode(array('count'=>$count, 'message'=>$message)));
    }
}