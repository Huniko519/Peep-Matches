<?php


require_once PEEP_DIR_PLUGIN.'googleauth'.DS.'lib'.DS.'httpcurl.php';

class GOOGLEAUTH_BOL_Service
{
    private static $classInstance;

    /*
     *
     * Returns class instance
     *
     * @return GOOGLEAUTH_BOL_Service
     *
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    private $httpcurl;
    public $props;

    protected function __construct ()
    {
     $this->httpcurl = new HTTPCurl();
     $this->props = $this->getProperties ();
     $this->httpcurl->setUserAgent ('(Google auth/peepdev)');
     $this->httpcurl->setSSLVerify (false);
     $this->httpcurl->setCache (false);
     $this->httpcurl->setHeaderBody (false);
    }

    public function findValue ($scan_array, $find_key)
    {
        $result = null;
        foreach ( $scan_array as $key => $val )
        {
            if (!strcasecmp($find_key,$key))
            {
              $result = $val;
              break;
            }
            else
            {
              if (is_array($val)) $result = $this->findValue ($val,$find_key);
            }
        }
        return $result;
    }



   public function getProperties ()
    {
     $peepconfig = PEEP::getConfig();
     $props = new GOOGLEAUTH_BOL_Config ();
     $props->client_id = $peepconfig->getValue ('googleauth','client_id');
     $props->client_secret = $peepconfig->getValue ('googleauth','client_secret');
     $props->redirect_uri = PEEP::getRouter()->urlForRoute('googleauth_oauth');
     return $props;
    }

   public function saveProperties (GOOGLEAUTH_BOL_Config $props)
    {
     $peepconfig = PEEP::getConfig();
     $peepconfig->saveConfig ('googleauth','client_id',$props->client_id);
     $peepconfig->saveConfig ('googleauth','client_secret',$props->client_secret);
     return true;
    }

    public function generateOAuthUri ()
    {
     $data = array (
       'scope'=>$this->getScope(),
       'redirect_uri'=>$this->props->redirect_uri,
       'response_type'=>'code',
       'client_id'=>$this->props->client_id
       );
     return $this->props->endpoint.'?'.http_build_query ($data);
    }

    private function getToken ($data)
    {
     $this->httpcurl->setUrl ($this->props->tokenpoint);
     $this->httpcurl->setPostData ($data);
     $this->httpcurl->execute();
     return json_decode ($this->httpcurl->content,true);
    }

    public function getUserInfo ($data)
    {
     $token = $this->findValue($this->getToken($data),'access_token');
     $this->httpcurl->setUrl ($this->props->userinfopoint.$token);
     $this->httpcurl->setPostMethod (false);
     $this->httpcurl->execute();
     return json_decode ($this->httpcurl->content,true);
    }

    public function getScope()
    {
     $email = 'https://www.googleapis.com/auth/userinfo.email';
     $profile= 'https://www.googleapis.com/auth/userinfo.profile';
     return $email.' '.$profile;
    }


}
