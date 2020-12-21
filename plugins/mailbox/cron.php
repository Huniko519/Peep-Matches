<?php


class MAILBOX_Cron extends PEEP_Cron
{
    const UPLOAD_FILES_REMOVE_TIMEOUT = 86400; // 1 day

    public function __construct()
    {
        parent::__construct();

        if ( PEEP::getConfig()->configExists('mailbox', 'update_to_revision_7200') )
        {
            $this->addJob('mailboxUpdate', 2);
        }

        $this->addJob('resetAllUsersLastData', 1);
        $this->addJob('deleteAttachmentFiles', 1440); //1 day
    }

    public function run()
    {
        //ignore
    }

    public function mailboxUpdate()
    {
        MAILBOX_BOL_ConversationService::getInstance()->convertHtmlTags();
    }

    public function resetAllUsersLastData()
    {
        $sql = "SELECT COUNT(*) FROM `".MAILBOX_BOL_UserLastDataDao::getInstance()->getTableName()."` AS `uld`
LEFT JOIN `".BOL_UserOnlineDao::getInstance()->getTableName()."` AS uo ON uo.userId = uld.userId
WHERE uo.id IS NULL";

        $usersOfflineButOnline = PEEP::getDbo()->queryForColumn($sql);
        if ($usersOfflineButOnline > 0)
        {
            MAILBOX_BOL_ConversationService::getInstance()->resetAllUsersLastData();
        }
    }
    
    public function deleteAttachmentFiles()
    {
        MAILBOX_BOL_ConversationService::getInstance()->deleteAttachmentFiles();
    }
}