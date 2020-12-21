<?php

class GOOGLEAUTH_CTRL_Connect extends PEEP_ActionController
{
    /**
     *
     * @var GOOGLEAUTH_BOL_Service
     */
    private $service;

    public function init()
    {
        $this->service = GOOGLEAUTH_BOL_Service::getInstance();
    }

    public function oauth()
    {
     $language = PEEP::getLanguage();
     if (!empty ($_GET['code']))
     {
       $data = array (
         'code'=>$_GET['code'],
         'client_id'=>$this->service->props->client_id,
         'client_secret'=>$this->service->props->client_secret,
         'redirect_uri'=>$this->service->props->redirect_uri,
         'grant_type'=>'authorization_code'
        );
       $userinfo = $this->service->getUserInfo ($data);
     }
     else
     {
        PEEP::getFeedback()->error($language->text('googleauth', 'login_failure_msg'));
        $this->redirect(PEEP::getRouter()->urlForRoute('static_sign_in'));
     }
     $result = $this->login ($userinfo);
     if ($result) $this->redirect(PEEP::getRouter()->getBaseUrl());
     else $this->redirect(PEEP::getRouter()->urlForRoute('static_sign_in'));
    }


  public function login( $params )
    {
      $language = PEEP::getLanguage();
      // Register or login
      $user = BOL_UserService::getInstance()->findByEmail($params['email']);
      if (!empty($user))
      {
        // LOGIN
        PEEP::getUser()->login($user->id);
        PEEP::getFeedback()->info($language->text('googleauth', 'login_success_msg'));
        return true;
      }
      else
      {
        //REGISTER
        $authAdapter = new GOOGLEAUTH_CLASS_AuthAdapter($params['email']);
        $username = 'glc'.$params ['id'];
        $password = uniqid();
        try
        {
          $user = BOL_UserService::getInstance()->createUser($username, $password, $params['email'], null, $params['verified_email']);
        }
        catch ( Exception $e )
        {
          switch ( $e->getCode() )
          {
           case BOL_UserService::CREATE_USER_DUPLICATE_EMAIL:
             PEEP::getFeedback()->error($language->text('googleauth', 'join_dublicate_email_msg'));
             return false;
             break;
          case BOL_UserService::CREATE_USER_INVALID_USERNAME:
             PEEP::getFeedback()->error($language->text('googleauth', 'join_incorrect_username'));
             return false;
             break;
          default:
             PEEP::getFeedback()->error($language->text('googleauth', 'join_incomplete'));
             return false;
             break;
         }
      } //END TRY-CATCH
      $user->username = "google_user_" . $user->id;
      BOL_UserService::getInstance()->saveOrUpdate($user);
      BOL_QuestionService::getInstance()->saveQuestionsData(array('realname' => $params['name']), $user->id);
      BOL_AvatarService::getInstance()->setUserAvatar ($user->id, $params['picture']);

      switch ($params['gender'])
      {
        case 'male'   :  BOL_QuestionService::getInstance()->saveQuestionsData(array('sex' => 1), $user->id);break;
        case 'female' :  BOL_QuestionService::getInstance()->saveQuestionsData(array('sex' => 2), $user->id);break;
      }
      $authAdapter->register($user->id);
      $authResult = PEEP_Auth::getInstance()->authenticate($authAdapter);
      if ( $authResult->isValid() )
      {
        $event = new PEEP_Event(PEEP_EventManager::ON_USER_REGISTER, array('method' => 'google', 'userId' => $user->id));
        PEEP::getEventManager()->trigger($event);
        PEEP::getFeedback()->info($language->text('googleauth', 'join_success_msg'));
      }
      else
      {
        PEEP::getFeedback()->error($language->text('googleauth', 'join_failure_msg'));
      }
      return $authResult->isValid();
    }
   }
}
