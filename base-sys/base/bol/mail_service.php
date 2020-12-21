<?php

require_once PEEP_DIR_LIB . 'php_mailer' . DS . 'class.phpmailer.php';
require_once PEEP_DIR_LIB . 'php_mailer' . DS . 'class.smtp.php';

class BOL_MailService
{
    const MAIL_COUNT_PER_CRON_JOB = 50;

    const TRANSFER_SMTP = 'smtp';
    const TRANSFER_MAIL = 'mail';
    const TRANSFER_SENDMAIL = 'sendmail';

    /**
     *
     * @var BOL_MailDao
     */
    private $mailDao;
    private $defaultMailSettingList = array();

    private function __construct()
    {
        $this->mailDao = BOL_MailDao::getInstance();

        $siteName = PEEP::getConfig()->getValue('base', 'site_name');
        $siteEmail = PEEP::getConfig()->getValue('base', 'site_email');
        $senderSuffix = defined('PEEP_SENDER_MAIL_SUFFIX') ? PEEP_SENDER_MAIL_SUFFIX : null;

        $this->defaultMailSettingList = array(
            'sender' => array($siteEmail, $siteName),
            'senderSuffix' => intval($senderSuffix)
        );
    }
    /**
     * Class instance
     *
     * @var BOL_MailService
     */
    private static $classInstance;
    /**
     *
     * @var PHPMailer
     */
    private $phpMailer;

    /**
     * Returns class instance
     *
     * @return BOL_MailService
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @return PHPMailer
     */
    private function getMailer()
    {
        if ( !isset($this->phpMailer) )
        {
            $this->phpMailer = $this->initializeMailer($this->getTransfer());
        }

        return $this->phpMailer;
    }

    /**
     *
     * @return PHPMailer
     */
    private function initializeMailer( $transfer )
    {
        $mailer = new PHPMailer(true);

        switch ( $transfer )
        {
            case self::TRANSFER_SMTP :
                $this->smtpSetup($mailer);
                break;
            case self::TRANSFER_SENDMAIL :
                $mailer->IsSendmail();
                break;
            case self::TRANSFER_MAIL :
                $mailer->IsMail();
                break;
        }

        $mailer->CharSet = "utf-8";
        
        return $mailer;
    }

    public function getTransfer()
    {
        if ( PEEP::getConfig()->getValue('base', 'mail_smtp_enabled') )
        {
            return self::TRANSFER_SMTP;
        }

        return self::TRANSFER_MAIL;
    }

    private function getSMTPSettingList()
    {
        $configs = PEEP::getConfig()->getValues('base');

        return array(
            'connectionPrefix' => $configs['mail_smtp_connection_prefix'],
            'host' => $configs['mail_smtp_host'],
            'port' => $configs['mail_smtp_port'],
            'user' => $configs['mail_smtp_user'],
            'password' => $configs['mail_smtp_password']
        );
    }

    /**
     *
     * @param PHPMailer $mailer
     */
    private function smtpSetup( $mailer )
    {
        $settingList = $this->getSMTPSettingList();

        $mailer->SMTPSecure = $settingList['connectionPrefix'];
        $mailer->IsSMTP();
        $mailer->SMTPAuth = true;
        $mailer->SMTPKeepAlive = true;
        $mailer->Host = $settingList['host'];

        if ( !empty($settingList['port']) )
        {
            $mailer->Port = (int) $settingList['port'];
        }

        $mailer->Username = $settingList['user'];
        $mailer->Password = $settingList['password'];
    }

    public function smtpTestConnection()
    {
        if ( $this->getTransfer() !== self::TRANSFER_SMTP )
        {
            throw new LogicException('Mail transfer is not SMTP');
        }

        $mailer = $this->getMailer();

        try
        {
            return $mailer->SmtpConnect();
        }
        catch ( phpmailerException $e )
        {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    /**
     *
     * @return BASE_CLASS_Mail
     */
    public function createMail()
    {
        $mail = new BASE_CLASS_Mail($this->defaultMailSettingList);

        return $mail;
    }

    private function createMailFromDto( BOL_Mail $mailDto )
    {
        $mail = new BASE_CLASS_Mail();
        $mail->addRecipientEmail($mailDto->recipientEmail);
        $mail->setSender($mailDto->senderEmail, $mailDto->senderName);
        $mail->setSubject($mailDto->subject);
        $mail->setTextContent($mailDto->textContent);
        $mail->setHtmlContent($mailDto->htmlContent);
        $mail->setSentTime($mailDto->sentTime);
        $mail->setPriority($mailDto->priority);
        $mail->setSenderSuffix($mailDto->senderSuffix);

        return $mail;
    }

    private function prepareFromEmail( $email, $suffix )
    {
        if ( empty($email) )
        {
            return null;
        }

        $suffix = intval($suffix);

        if ( empty($suffix) )
        {
            return $email;
        }

        list($user, $provider) = explode('@', $email);

        return $user . '+' . $suffix . '@' . $provider;
    }

    public function send( BASE_CLASS_Mail $mail )
    {
        $mailer = $this->getMailer();
        $mailState = $mail->saveToArray();

        $event = new PEEP_Event('base.mail_service.send.check_mail_state', array(), $mailState);
        PEEP::getEventManager()->trigger($event);
        $mailState = $event->getData();

        if (empty($mailState['recipientEmailList']))
        {
            return false;
        }

        $fromEmail = $this->prepareFromEmail($mailState['sender'][0], $mailState['senderSuffix']);

        $mailer->SetFrom($fromEmail, $mailState['sender'][1]);
        $mailer->Sender = $mailState['sender'][0];

        if ( !empty($mailState['replyTo']) )
        {
            $mailer->AddReplyTo($mailState['replyTo'][0], $mailState['replyTo'][1]);
        }
        foreach ( $mailState['recipientEmailList'] as $item )
        {
            $mailer->AddAddress($item);
        }

        $isHtml = !empty($mailState['htmlContent']);

        $mailer->Subject = $mailState['subject'];
        $mailer->IsHTML($isHtml);
        $mailer->Body = $isHtml ? $mailState['htmlContent'] : $mailState['textContent'];
        $mailer->AltBody = $isHtml ? $mailState['textContent'] : '';

        $result = $mailer->Send();
        $mailer->ClearReplyTos();
        $mailer->ClearAllRecipients();

        return $result;
    }

    private function mailToDtoList( BASE_CLASS_Mail $mail )
    {
        $mailState = $mail->saveToArray();
        $resultList = array();

        foreach ( $mailState['recipientEmailList'] as $email )
        {
            $mailDto = new BOL_Mail();

            $mailDto->senderEmail = $mailState['sender'][0];
            $mailDto->senderName = $mailState['sender'][1];
            $mailDto->subject = $mailState['subject'];
            $mailDto->textContent = $mailState['textContent'];
            $mailDto->htmlContent = $mailState['htmlContent'];
            $mailDto->sentTime = empty($mailState['sentTime']) ? time() : $mailState['sentTime'];
            $mailDto->priority = $mailState['priority'];
            $mailDto->recipientEmail = $email;
            $mailDto->senderSuffix = intval($mailState['senderSuffix']);

            $resultList[] = $mailDto;
        }

        return $resultList;
    }

    public function addToQueue( BASE_CLASS_Mail $mail )
    {
        $dtoList = $this->mailToDtoList($mail);

        foreach ( $dtoList as $dtoMail )
        {
            $this->mailDao->save($dtoMail);
        }
    }

    public function addListToQueue( array $mailList )
    {
        $fullDtoList = array();

        foreach ( $mailList as $mail )
        {
            $dtoList = $this->mailToDtoList($mail);

            foreach ( $dtoList as $mailDto )
            {
                $fullDtoList[] = $mailDto;
            }
        }

        if ( !empty ($fullDtoList) )
        {
            $this->mailDao->saveList($fullDtoList);
        }
    }

    public function processQueue( $count = self::MAIL_COUNT_PER_CRON_JOB )
    {
        $list = $this->mailDao->findList($count);

        $processedIdList = array();

        foreach ( $list as $item )
        {
            try
            {
                $mail = $this->createMailFromDto($item);
                $this->send($mail);
            }
            catch ( Exception $e )
            {
                //Skip invalid email adresses
            }

            $this->mailDao->updateSentStatus($item->id);
        }

        $this->mailDao->deleteSentMails();
    }

    public function getEmailDomain()
    {
        switch ( $this->getTransfer() )
        {
            case self::TRANSFER_SMTP:
                $settings = $this->getSMTPSettingList();
                return $settings['host'];

            default:
                $urlInfo = parse_url(PEEP_URL_HOME);
                return $urlInfo['host'];
        }
    }
    
    public function deleteQueuedMailsByRecipientId( $userId )
    {
        $user = BOL_UserService::getInstance()->findUserById($userId);
        
        if ( $user === null )
        {
            return;
        }
        
        $this->mailDao->deleteByRecipientEmail($user->email);
    }

    public function __destruct()
    {
        $this->getMailer()->SmtpClose();
    }
}