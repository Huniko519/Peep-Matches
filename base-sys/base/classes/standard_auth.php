<?php

class BASE_CLASS_StandardAuth extends PEEP_AuthAdapter
{
    /**
     * @var string
     */
    private $identity;
    /**
     * @var string
     */
    private $password;
    /**
     * @var BOL_UserService
     */
    private $userService;

    /**
     * Constructor.
     *
     * @param string $identity
     * @param string $password
     */
    public function __construct( $identity, $password )
    {
        $this->identity = trim($identity);
        $this->password = trim($password);

        $this->userService = BOL_UserService::getInstance();
    }

    /**
     * @see PEEP_AuthAdapter::authenticate()
     *
     * @return PEEP_AuthResult
     */
    function authenticate()
    {
        $user = $this->userService->findUserForStandardAuth($this->identity);

        $language = PEEP::getLanguage();

        if ( $user === null )
        {
            return new PEEP_AuthResult(PEEP_AuthResult::FAILURE_IDENTITY_NOT_FOUND, null, array($language->text('base', 'auth_identity_not_found_error_message')));
        }
        
        if ( $user->getPassword() !== BOL_UserService::getInstance()->hashPassword($this->password) )
        {
            return new PEEP_AuthResult(PEEP_AuthResult::FAILURE_PASSWORD_INVALID, null, array($language->text('base', 'auth_invlid_password_error_message')));
        }

        return new PEEP_AuthResult(PEEP_AuthResult::SUCCESS, $user->getId(), array($language->text('base', 'auth_success_message')));
    }
}