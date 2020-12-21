<?php

class CONTACTIMPORTER_CTRL_Google extends PEEP_ActionController
{
    public function popup()
    {
	$document = PEEP::getDocument();
        $document->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate(PEEP_MasterPage::TEMPLATE_BLANK));

	if ( isset($_GET['error']) )
	{
		$document->addOnloadScript('window.close();');
		$this->assign('close', true);
		return;
	}

        //setting parameters
        $authcode= $_GET["code"];

        $clientId = PEEP::getConfig()->getValue('contactimporter', 'google_client_id');
        $clientSecret = PEEP::getConfig()->getValue('contactimporter', 'google_client_secret');

        $redirectUri = PEEP::getRouter()->urlForRoute('contact-importer-google-oauth');

        $fields = array(
            'code' => urlencode($authcode),
            'client_id'=>  urlencode($clientId),
            'client_secret'=>  urlencode($clientSecret),
            'redirect_uri'=>  urlencode($redirectUri),
            'grant_type'=>  urlencode('authorization_code')
        );

        //url-ify the data for the POST

        $fieldsString='';

        foreach( $fields as $key => $value )
        {
            $fieldsString .= $key . '=' . $value . '&';
        }

        $fieldsString = rtrim($fieldsString, '&');

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL,'https://accounts.google.com/o/oauth2/token');
        curl_setopt($ch,CURLOPT_POST,5);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fieldsString);

        // Set so curl_exec returns the result instead of outputting it.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //to trust any ssl certificates
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        //extracting access_token from response string
        $response=  json_decode($result);

	if ( empty($response->access_token) )
	{
            $authUrl = PEEP::getRequest()->buildUrlQueryString('https://accounts.google.com/o/oauth2/auth', array(
                'response_type' => 'code',
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'state' => 'contacts',
                'scope' => 'https://www.google.com/m8/feeds/'
            ));

            UTIL_Url::redirect($authUrl);
	}

        $accessToken= $response->access_token;
        //passing accesstoken to obtain contact details
        $resultCount = 100;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, 'https://www.google.com/m8/feeds/contacts/default/full?max-results=' . $resultCount . '&oauth_token=' . $accessToken . '&alt=json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('GData-Version: 2.0'));
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch,CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $jsonResponse = curl_exec($ch);
        
        curl_close($ch);
        
        //$jsonResponse =  file_get_contents('https://www.google.com/m8/feeds/contacts/default/full?max-results=' . $resultCount . '&oauth_token=' . $accessToken . '&alt=json');
	$response = json_decode($jsonResponse, true);

	if ( !empty($response["error"]["message"]) )
	{
		echo $response["error"]["message"];
		exit;
	}

	$out = array();
	$list = $response['feed']['entry'];

        $defaultImage = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();

        $contexId = uniqid('ci');
        $jsArray = array();

        foreach ( $list as $item )
	{
            if ( empty($item['gd$email'][0]['address']) )
            {
                continue;
            }

            $address = $item['gd$email'][0]['address'];
            $image = $item['link'][1]['type'] != 'image/*' ? $defaultImage : $item['link'][1]['href'] . '?oauth_token=' . $accessToken;
            $title = empty($item['title']['$t']) ? $address : $item['title']['$t'];
            $uniqId = uniqid('cii');

            $out[] = array(
                'title' => $title,
                'image' => $image,
                'address' => $address,
                'uniqId' => $uniqId,
                'fields' => empty($item['title']['$t']) ? '' : $address,
                'avatar' => array(
                    'title' => $title,
                    'src' => $image
                )
            );

            $jsArray[$address] = array(
                'linkId' => $uniqId,
                'userId' => $address
            );
        }

        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'avatar_user_select.js');
        PEEP::getDocument()->addOnloadScript("
            var cmp = new AvatarUserSelect(" . json_encode($jsArray) . ", '" . $contexId . "');
            cmp.init();
            PEEP.registerLanguageKey('base', 'avatar_user_select_empty_list_message', '" . PEEP::getLanguage()->text('base', 'avatar_user_select_empty_list_message') . "');
         ");

        $this->assign('users', $out);
        $this->assign('contexId', $contexId);

        $countLabel = PEEP::getLanguage()->text('base', 'avatar_user_list_select_count_label');
        $buttonLabel = PEEP::getLanguage()->text('base', 'avatar_user_list_select_button_label');

        $langs = array(
            'countLabel' => $countLabel,
            'startCountLabel' => (!empty($countLabel) ? str_replace('#count#', '0', $countLabel) : null ),
            'buttonLabel' => $buttonLabel,
            'startButtonLabel' => str_replace('#count#', '0', $buttonLabel)
        );

        $this->assign('langs', $langs);

        $rsp = json_encode(PEEP::getRouter()->urlFor('CONTACTIMPORTER_CTRL_Google', 'send'));
        PEEP::getDocument()->addOnloadScript('PEEP.bind("base.avatar_user_list_select", function( data ){
            var msg = $("#ci-message").val();
	    var inv = $("#ci-message").attr("inv");

	    msg = inv == msg ? "" : msg;
            window.opener.CONTACTIMPORTER_Google.send(' . $rsp . ', data, msg);
            window.close();
        });');
    }

    public function oauth2callback()
    {
        $redirectUrl = PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlFor('CONTACTIMPORTER_CTRL_Google', 'popup'), $_GET);

        $this->redirect($redirectUrl);
    }

    public function send()
    {
        $request = json_decode($_POST['request'], true);
        $userId = PEEP::getUser()->getId();
        $displayName = BOL_UserService::getInstance()->getDisplayName($userId);

        foreach ( $request['contacts'] as $email )
        {
            $code = UTIL_String::getRandomString(20);
            BOL_UserService::getInstance()->saveUserInvitation($userId, $code);


            $inviteUrl = PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlForRoute('base_join'), array('code' => $code));

            $assigns = array(
                'url' => $inviteUrl,
                'message' => empty($request['message']) ? '' : $request['message'],
                'user' => $displayName
            );

            $tpl = empty($request['message']) ? 'mail_google_invite' : 'mail_google_invite_msg';

            $mail = PEEP::getMailer()->createMail();
            $mail->setSubject(PEEP::getLanguage()->text('contactimporter', 'mail_google_invite_subject', $assigns));
            $mail->setHtmlContent(PEEP::getLanguage()->text('contactimporter', $tpl . '_html', $assigns));
            $mail->setTextContent(PEEP::getLanguage()->text('contactimporter', $tpl . '_txt', $assigns));
            $mail->addRecipientEmail($email);

            PEEP::getMailer()->addToQueue($mail);
        }

        $message = PEEP::getLanguage()->text('contactimporter', 'google_send_success', array(
           'count' => count($request['contacts'])
        ));

        exit($message);
    }
}
