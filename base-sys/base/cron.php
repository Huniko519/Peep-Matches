<?php

class BASE_Cron extends PEEP_Cron
{
    const EMAIL_VARIFY_CODE_REMOVE_TIMEOUT = 432000; // 5 days
    const BILLING_SALES_EXPIRE_JOB_RUN_INTERVAL = 30;

    // minutes


    public function __construct()
    {
        parent::__construct();

        $this->addJob('dbCacheProcess', 1);
        $this->addJob('mailQueueProcess', 5);

        $this->addJob('deleteExpiredOnlineUserProcess', 1);

        //$this->addJob('expireUnverifiedSalesProcess', self::BILLING_SALES_EXPIRE_JOB_RUN_INTERVAL);

        $this->addJob('deleteExpiredOnlineUserProcess', 1);
        $this->addJob('checkPluginUpdates', 60 * 24);
        $this->addJob('deleteExpiredPasswordResetCodes', 10);
        $this->addJob('resetCronFlag', 1);
        $this->addJob('rmTempAttachments', 60 * 24);
        $this->addJob('rmTempAvatars', 60 * 24);
        $this->addJob('deleteExpiredCache', 60 * 24);
        $this->addJob('dropLogFile', 60 * 24);
        $this->addJob('clearMySqlSearchIndex', 60 * 24);

        $this->addJob('checkRealCron');
    }

    public function run()
    {
        //clean email varify code table
        BOL_EmailVerifyService::getInstance()->deleteByCreatedStamp(time() - self::EMAIL_VARIFY_CODE_REMOVE_TIMEOUT);
        BOL_UserService::getInstance()->cronSendWellcomeLetter();
    }

    public function dbCacheProcess()
    {
        // Delete expired db cache entry
        BOL_DbCacheService::getInstance()->deleteExpiredList();
    }

    public function mailQueueProcess()
    {
        // Send mails from mail queue
        BOL_MailService::getInstance()->processQueue();
    }

    public function deleteExpiredOnlineUserProcess()
    {
        BOL_UserService::getInstance()->deleteExpiredOnlineUsers();
    }

    public function expireUnverifiedSalesProcess()
    {
        BOL_BillingService::getInstance()->deleteExpiredSales();
    }

    public function expireSearchResultList()
    {
        BOL_SearchService::getInstance()->deleteExpireSearchResult();
    }

    public function clearMySqlSearchIndex()
    {
        $mysqlSearchStorage = new BASE_CLASS_MysqlSearchStorage();
        $mysqlSearchStorage->realDeleteEntities();
    }

    public function checkPluginUpdates()
    {
        BOL_PluginService::getInstance()->checkUpdates();
    }

    public function deleteExpiredPasswordResetCodes()
    {
        BOL_UserService::getInstance()->deleteExpiredResetPasswordCodes();
    }

    public function resetCronFlag()
    {
        if ( PEEP::getConfig()->configExists('base', 'cron_is_active') && (int) PEEP::getConfig()->getValue('base', 'cron_is_active') === 0 )
        {
            PEEP::getConfig()->saveConfig('base', 'cron_is_active', 1);
        }
    }

    public function rmTempAttachments()
    {
        BOL_AttachmentService::getInstance()->deleteExpiredTempImages();
    }

    public function rmTempAvatars()
    {
        BOL_AvatarService::getInstance()->deleteTempAvatars();
    }

    public function deleteExpiredCache()
    {
        PEEP::getCacheManager()->clean(array(), PEEP_CacheManager::CLEAN_OLD);
    }

    public function dropLogFile()
    {
        $logFilePath = PEEP_DIR_LOG . 'error.log';

        if ( file_exists($logFilePath) )
        {
            $logFileSize = filesize($logFilePath);

            if ( $logFileSize !== false && $logFileSize / 1024 / 1024 >= (int) PEEP::getConfig()->getValue('base', 'log_file_max_size_mb') )
            {
                unlink($logFilePath);
            }
        }
    }

    public function checkRealCron()
    {
        if ( !isset($_GET['peep-light-cron']) )
        {
            if ( PEEP::getConfig()->configExists('base', 'cron_is_configured') )
            {
                PEEP::getConfig()->saveConfig('base', 'cron_is_configured', 1);
            }
            else
            {
                PEEP::getConfig()->addConfig('base', 'cron_is_configured', 1);
            }
        }
    }
}
