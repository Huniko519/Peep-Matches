<?php

class GOOGLEAUTH_BOL_Config
{
   public $client_id;
   public $client_secret;
   public $redirect_uri;
   public $endpoint = 'https://accounts.google.com/o/oauth2/auth'; //main url auth
   public $tokenpoint = 'https://accounts.google.com/o/oauth2/token'; //url by get token
   public $userinfopoint = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token='; //url by get userinfo
}