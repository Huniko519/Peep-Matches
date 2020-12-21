<?php

class HTTPCurl
{

 public $content;
 public $headers;

 private $curlint;

 public function __construct ()
 {
    $this->curlint = curl_init();                   // curl init
    curl_setopt($this->curlint,CURLOPT_RETURNTRANSFER,1); // get content
 }

 public function setPostData ($postdata)
 {
    curl_setopt($this->curlint,CURLOPT_POST,1);
    curl_setopt($this->curlint,CURLOPT_POSTFIELDS,$postdata);
 }

 public function setPostMethod ($post)
 {
    curl_setopt($this->curlint,CURLOPT_POST,$post);
 }

 public function setUserAgent ($useragent)
 {
    curl_setopt($this->curlint,CURLOPT_USERAGENT,$useragent);
 }

 public function setHeaderBody ($header)
 {
    curl_setopt($this->curlint,CURLOPT_HEADER,$header);
 }

 public function setTimeout ($timeout)
 {
    curl_setopt($this->curlint,CURLOPT_TIMEOUT,$timeout);
 }

 public function setSSLVerify ($verify)
 {
    curl_setopt($this->curlint,CURLOPT_SSL_VERIFYPEER,$verify);
 }

 public function setCache ($cache)
 {
    curl_setopt($this->curlint,CURLOPT_FRESH_CONNECT,!$cache);  //cache
 }

 public function setUrl ($url)
 {
    curl_setopt($this->curlint,CURLOPT_URL,$url);       // url
 }


 public function execute ()
    {
        $this->content = curl_exec ($this->curlint);
        $this->headers = curl_getinfo ($this->curlint);
        return curl_errno ($this->curlint);
    }
 public function __destruct ()
 {
  if (isset($this->curlint)) curl_close ($this->curlint);
 }


}

?>