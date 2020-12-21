<?php

class PEEP_Mailer
{
    /**
     * 
     * @var BOL_MailService
     */
    private $maliService;
    
	/**
     * Constructor.
     *
     */
    private function __construct()
    {
        $this->maliService = BOL_MailService::getInstance();
    }
    
    /**
     * Singleton instance.
     *
     * @var PEEP_Mailer
     */
    private static $classInstance;
    
    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_Mailer
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();
        
        return self::$classInstance;
    }
    
    /**
     * 
     * @param $state
     * @return BASE_CLASS_Mail
     */
    public function createMail()
    {
        return $this->maliService->createMail();
    }
    
    public function addToQueue( BASE_CLASS_Mail $mail )
    {
        $this->maliService->addToQueue($mail);
    }
    
    public function addListToQueue( array $list )
    {
        $this->maliService->addListToQueue($list);
    }
    
    public function send( BASE_CLASS_Mail $mail )
    {
        if ( $this->maliService->getTransfer() == BOL_MailService::TRANSFER_SMTP )
        {
            $this->maliService->addToQueue($mail);
        }
        else
        {
            $this->maliService->send($mail);    
        }
    }
    
    public function getEmailDomain()
    {
        return $this->maliService->getEmailDomain();
    }
}