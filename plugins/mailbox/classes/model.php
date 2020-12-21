<?php

class MAILBOX_CLASS_Model
{
    private $userId;
    private $response = array();

    /**
     * @var MAILBOX_BOL_ConversationService
     */
    private $conversationService;

    public function __construct()
    {
        $this->userId = PEEP::getUser()->getId();
        $this->conversationService = MAILBOX_BOL_ConversationService::getInstance();
    }

    public function updateWithData($params)
    {
        if (!isset($params['lastRequestTimestamp']))
        {
            return;
        }

        if ((int)$params['lastRequestTimestamp'] == 0)
        {
            $params['lastRequestTimestamp'] = time();
        }

        /***************************************************************************************************************/

        if (!empty($params['readMessageList']))
        {
            $readMessageIdList = array();
            foreach ($params['readMessageList'] as $message)
            {
                $readMessageIdList[] = $message["id"];
            }
            $this->conversationService->markMessageIdListRead($readMessageIdList);
        }

        /***************************************************************************************************************/

        $ignoreMessageList = array();
        if (!empty($params['ignoreMessageList']))
        {
            foreach ($params['ignoreMessageList'] as $message)
            {
                $ignoreMessageList[] = $message["id"];
            }
        }
        $m = $this->conversationService->findUnreadMessagesForApi($this->userId, $ignoreMessageList, $params['lastRequestTimestamp']);
        $this->setObject('messageList', $m);

        /***************************************************************************************************************/

        if (!isset($params['conversationListLength']))
        {
            $params['conversationListLength'] = 0;
        }

//        $count = $this->conversationService->countConversationListByUserId($this->userId);
//
//        if ((int)$params['conversationListLength'] != $count)
//        {
//            $list = $this->conversationService->getConversationListByUserId($this->userId);
//            $this->setObject('conversationList', $list);
//        }
        if (count($m) > 0)
        {
        $list = $this->conversationService->getChatUserList($this->userId, 0, 10); //TODO specify limits
        $this->setObject('conversationList', $list);
        }

        $this->setObject('lastRequestTimestamp', time());
    }

    private function setObject($key, $value)
    {
        $this->response[$key] = $value;
    }

    public function getResponse()
    {
        return $this->response;
    }
}