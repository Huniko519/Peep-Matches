<?php

class BASE_CTRL_AjaxUpdateStatus extends PEEP_ActionController
{

    function update()
    {
        $service = UserStatusService::getInstance();

        $userId = PEEP::getUser()->getId();

        if ( empty($userId) || empty($_POST['status']) )
        {
            exit('{}');
        }

        if ( !($status = $service->findByUserId($userId)) )
        {
            $status = new UserStatus();
            $status->setUserId($userId);
        }

        $statusContent = htmlspecialchars($_POST['status']);
        $status->setStatus($statusContent);

        $service->save($status);

        if ( PEEP::getPluginManager()->isPluginActive('activity') && trim($status->getStatus()) !== '' )
        {
            $action = new ACTIVITY_BOL_Action();

            $data = array(
                'string' => PEEP::getLanguage()->text('user_status', 'activity_string',
                    array(
                        'actor' => BOL_UserService::getInstance()->getDisplayName($status->getUserId()),
                        'actorUrl' => BOL_UserService::getInstance()->getUserUrl($status->getUserId()),
                        'status' => $status->getStatus()
                    )
                ),
                'content_comment' => '',
            );

            $action->setUserId($status->getUserId())
                ->setTimestamp(time())
                ->setType('status-update')
                ->setEntityId($status->getUserId())
                ->setData($data);

            ACTIVITY_BOL_Service::getInstance()->addAction($action);
        }

        exit(json_encode(array(
                'result' => 'success',
                'js' => 'PEEP.info("' . PEEP::getLanguage()->text('user_status', 'updated') . '")'
            )));
    }
}