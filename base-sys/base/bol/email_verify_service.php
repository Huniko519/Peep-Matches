<?php

class BOL_EmailVerifyService
{
    const TYPE_USER_EMAIL = 'user';
    const TYPE_SITE_EMAIL = 'site';


    /**
     * @var BOL_QuestionDao
     */
    private $emailVerifiedDao;

    /**
     * Constructor.
     *
     */
    private function __construct()
    {
        $this->emailVerifiedDao = BOL_EmailVerifyDao::getInstance();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_EmailVerifyService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_EmailVerifyService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @param BOL_EmailVerified $object
     */
    public function saveOrUpdate( BOL_EmailVerified $object )
    {
        $this->emailVerifiedDao->save($object);
    }

    /**
     * @param string $email
     * @param string $type
     * @return BOL_EmailVerified
     */
    public function findByEmail( $email, $type )
    {
        return $this->emailVerifiedDao->findByEmail($email, $type);
    }

    /**
     * @param string $email
     * @param int $userId
     * @param string $type
     * @return BOL_EmailVerified
     */
    public function findByEmailAndUserId( $email, $userId, $type )
    {
        return $this->emailVerifiedDao->findByEmailAndUserId($email, $userId, $type);
    }

    /**
     * @param string $hash
     * @return BOL_EmailVerified
     */
    public function findByHash( $hash )
    {
        return $this->emailVerifiedDao->findByHash($hash);
    }

    /**
     * @return string
     */
    public function generateHash()
    {
        return md5(uniqid());
    }

    /**
     * @param array $objects
     */
    public function batchReplace( $objects )
    {
        $this->emailVerifiedDao->batchReplace($objects);
    }

    /**
     * @param int $id
     */
    public function deleteById( $id )
    {
        $this->emailVerifiedDao->deleteById($id);
    }

    /**
     * @param int $userId
     */
    public function deleteByUserId( $userId )
    {
        $this->emailVerifiedDao->deleteByUserId($userId);
    }

    /**
     * @param int $stamp
     */
    public function deleteByCreatedStamp( $stamp )
    {
        $this->emailVerifiedDao->deleteByCreatedStamp($stamp);
    }

    public function sendVerificationMail( $type, $params )
    {
        $subject = $params['subject'];
        $template_html = $params['body_html'];
        $template_text = $params['body_text'];

        switch ( $type )
        {
            case self::TYPE_USER_EMAIL:
                $user = $params['user'];
                $email = $user->email;
                $userId = $user->id;

                break;

            case self::TYPE_SITE_EMAIL:
                $email = PEEP::getConfig()->getValue('base', 'unverify_site_email');
                $userId = 0;

                break;

            default :
                PEEP::getFeedback()->error($language->text('base', 'email_verify_verify_mail_was_not_sent'));
                return;
        }

        $emailVerifiedData = BOL_EmailVerifyService::getInstance()->findByEmailAndUserId($email, $userId, $type);

        if ( $emailVerifiedData !== null )
        {
            $timeLimit = 60 * 60 * 24 * 3; // 3 days

            if ( time() - (int) $emailVerifiedData->createStamp >= $timeLimit )
            {
                $emailVerifiedData = null;
            }
        }

        if ( $emailVerifiedData === null )
        {
            $emailVerifiedData = new BOL_EmailVerify();
            $emailVerifiedData->userId = $userId;
            $emailVerifiedData->email = trim($email);
            $emailVerifiedData->hash = BOL_EmailVerifyService::getInstance()->generateHash();
            $emailVerifiedData->createStamp = time();
            $emailVerifiedData->type = $type;

            BOL_EmailVerifyService::getInstance()->batchReplace(array($emailVerifiedData));
        }

        $vars = array(
            'code' => $emailVerifiedData->hash,
            'url' => PEEP::getRouter()->urlForRoute('base_email_verify_code_check', array('code' => $emailVerifiedData->hash)),
            'verification_page_url' => PEEP::getRouter()->urlForRoute('base_email_verify_code_form')
        );

        $language = PEEP::getLanguage();

        $subject = UTIL_String::replaceVars($subject, $vars);
        $template_html = UTIL_String::replaceVars($template_html, $vars);
        $template_text = UTIL_String::replaceVars($template_text, $vars);

        $mail = PEEP::getMailer()->createMail();
        $mail->addRecipientEmail($emailVerifiedData->email);
        $mail->setSubject($subject);
        $mail->setHtmlContent($template_html);
        $mail->setTextContent($template_text);

        PEEP::getMailer()->send($mail);

        if ( !isset($params['feedback']) || $params['feedback'] !== false )
        {
            PEEP::getFeedback()->info($language->text('base', 'email_verify_verify_mail_was_sent'));
        }
    }

    public function sendUserVerificationMail( BOL_User $user, $feedback = true )
    {
        $vars = array(
            'username' => BOL_UserService::getInstance()->getDisplayName($user->id),
        );

        $language = PEEP::getLanguage();

        $subject = $language->text('base', 'email_verify_subject', $vars);
        $template_html = $language->text('base', 'email_verify_template_html', $vars);
        $template_text = $language->text('base', 'email_verify_template_text', $vars);

        $params = array(
            'user' => $user,
            'subject' => $subject,
            'body_html' => $template_html,
            'body_text' => $template_text
        );

        $this->sendVerificationMail(self::TYPE_USER_EMAIL, $params);
    }

    public function sendSiteVerificationMail( $feedback = true )
    {
        $language = PEEP::getLanguage();

        $subject = $language->text('base', 'site_email_verify_subject');
        $template_html = $language->text('base', 'site_email_verify_template_html');
        $template_text = $language->text('base', 'site_email_verify_template_text');

        $params = array(
            'subject' => $subject,
            'body_html' => $template_html,
            'body_text' => $template_text
        );

        $this->sendVerificationMail(self::TYPE_SITE_EMAIL, $params);
    }

    /**
     * @param string $code
     */
    public function verifyEmail( $code )
    {
        $language = PEEP::getLanguage();

        /* @var BOL_EmailVerified */
        $emailVerifyData = $this->findByHash($code);
        if ( $emailVerifyData !== null )
        {
            switch ( $emailVerifyData->type )
            {
                case self::TYPE_USER_EMAIL:

                    $user = BOL_UserService::getInstance()->findUserById($emailVerifyData->userId);
                    if ( $user !== null )
                    {
                        if ( PEEP::getUser()->isAuthenticated() )
                        {
                            if ( PEEP::getUser()->getId() !== $user->getId() )
                            {
                                PEEP::getUser()->logout();
                            }
                        }

                        PEEP::getUser()->login($user->getId());

                        $this->deleteById($emailVerifyData->id);

                        $user->emailVerify = true;
                        BOL_UserService::getInstance()->saveOrUpdate($user);

                        PEEP::getFeedback()->info($language->text('base', 'email_verify_email_verify_success'));
                        
                        PEEP::getApplication()->redirect(PEEP::getRouter()->urlForRoute('base_default_index'));
                    }
                    break;

                case self::TYPE_SITE_EMAIL:

                    PEEP::getConfig()->saveConfig('base', 'site_email', $emailVerifyData->email);
                    BOL_LanguageService::getInstance()->generateCacheForAllActiveLanguages();
                    PEEP::getFeedback()->info($language->text('base', 'email_verify_email_verify_success'));
                    PEEP::getApplication()->redirect(PEEP::getRouter()->urlForRoute('admin_settings_main'));

                    break;
            }
        }
    }
}
?>
