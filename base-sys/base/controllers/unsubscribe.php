<?php


class BASE_CTRL_Unsubscribe extends PEEP_ActionController
{
    private $unsubscribeServise;
    private $userServise;

    public function __construct()
    {
        $this->unsubscribeServise = BOL_MassMailingIgnoreUserService::getInstance();
        $this->userServise = BOL_UserService::getInstance();
    }

    public function index( $params )
    {
        if( PEEP::getRequest()->isAjax() )
        {
            exit;
        }
        
        $language = PEEP::getLanguage();

        $this->setPageHeading( $language->text( 'base', 'massmailing_unsubscribe' ) );

        $code = null;
        $userId = null;

        $result = false;

        if( isset($params['code']) && isset($params['id']) )
        {
            $result = 'confirm';
            
            if ( !empty($_POST['cancel']) )
            {
                $this->redirect(PEEP_URL_HOME);
            }


            $code = trim($params['code']);
            $userId = $params['id'];
            $user = $this->userServise->findUserById($userId);
            if ( $user !== null )
            {
                if( md5( $user->username . $user->password ) ===  $code )
                {
                    $result = 'confirm';
                    if (!empty( $_POST['confirm'] ) )
                    {   
                        BOL_PreferenceService::getInstance()->savePreferenceValue('mass_mailing_subscribe', false, $user->id);
                        $result = true;
                        PEEP::getFeedback()->info($language->text('base', 'massmailing_unsubscribe_successful'));
                        $this->redirect(PEEP_URL_HOME);
                    }
                }
            }
        }

        $this->assign('result', $result);
    }
    
    public function apiUnsubscribe($params)
    {
        if ( empty($params['emails']) || !is_array($params['emails']) )
        {
            throw new InvalidArgumentException('Invalid email list');
        }
        
        foreach ( $params['emails'] as $email )
        {
            $user = BOL_UserService::getInstance()->findByEmail($email);
            
            if ( $user === null )
            {
                throw new LogicException('User with email ' . $email . ' not found');
            }
            
            BOL_PreferenceService::getInstance()->savePreferenceValue('mass_mailing_subscribe', false, $user->id);
        }
    }
}

?>
