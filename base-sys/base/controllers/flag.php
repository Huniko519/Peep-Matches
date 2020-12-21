<?php

class BASE_CTRL_Flag extends PEEP_ActionController
{

    public function flag()
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            exit(json_encode(array(
                'result' => 'success',
                'js' => 'PEEP.error(' . json_encode(PEEP::getLanguage()->text('base', 'sing_in_to_flag')) . ')'
            )));
        }

        $entityType = $_POST["entityType"];
        $entityId = $_POST["entityId"];
        
        $data = BOL_ContentService::getInstance()->getContent($entityType, $entityId);
        $ownerId = $data["userId"];
        $userId = PEEP::getUser()->getId();
        
        if ( $ownerId == $userId )
        {
            exit(json_encode(array(
                'result' => 'success',
                'js' => 'PEEP.error("' . PEEP::getLanguage()->text('base', 'flag_own_content_not_accepted') . '")'
            )));
        }

        $service = BOL_FlagService::getInstance();
        $service->addFlag($entityType, $entityId, $_POST['reason'], $userId);
                
        exit(json_encode(array(
            'result' => 'success',
            'js' => 'PEEP.info("' . PEEP::getLanguage()->text('base', 'flag_accepted') . '")'
        )));
    }

    public function delete( $params )
    {
        if ( !(PEEP::getUser()->isAdmin() || BOL_AuthorizationService::getInstance()->isModerator()) )
        {
            throw new Redirect403Exception;
        }

        BOL_FlagService::getInstance()->deleteFlagById($params['id']);
        PEEP::getFeedback()->info(PEEP::getLanguage()->text('base', 'flags_deleted'));
        $this->redirect($_SERVER['HTTP_REFERER']);
    }
}