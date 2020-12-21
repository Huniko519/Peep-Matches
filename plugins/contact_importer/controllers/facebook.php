<?php

require_once PEEP_DIR_LIB . 'facebook' . DS . 'facebook.php';

class CONTACTIMPORTER_CTRL_Facebook extends PEEP_ActionController
{
    public function canvas()
    {
        PEEP::getDocument()->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate('blank'));
        $requestIds = empty($_GET['request_ids']) ? array() : explode(',', $_GET['request_ids']);

        $appId = PEEP::getConfig()->getValue('contactimporter', 'facebook_app_id');
        $appSecret = PEEP::getConfig()->getValue('contactimporter', 'facebook_app_secret');

        if ( empty($appId) || empty($appSecret) )
        {
            $this->assign('content', 'App Secret and App Id are required');
            return;
        }

        $facebook = new Facebook(array(
	    'appId' => $appId,
	    'secret' => $appSecret
	));

	$from = array();
	$inviters = array();
	foreach ( $requestIds as $rid )
	{
	    $request = $facebook->api('/' . $rid);

	    if ($request)
	    {
		$from[$request['from']['id']] = $request['from'];
	    }

	    $data = empty($request['data']) ? array() : json_decode($request['data'], true);
	    if ( !empty($data['userId']) )
	    {
		$inviters[] = $data['userId'];
	    }
	}

	$from = array_reverse($from);

	$inviters = array_unique($inviters);
	$joinData = json_encode(array(
            'inviters' => $inviters,
            'requestIds' => $requestIds
        ));

	$code = base64_encode($joinData);
	$url = PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlForRoute('base_join'), array('code' => $code));

        $buttonEmbed = PEEP::getThemeManager()->processDecorator('button', array(
            'langLabel' => 'contactimporter+facebook_canvas_page_visit_btn',
            'onclick' => "window.open('" . $url . "'); return false;"
        ));

	switch ( count($from) )
	{
	    case 1:
		$user = reset($from);
		$content = PEEP::getLanguage()->text('contactimporter', 'facebook_canvas_page_1', array(
		    'user' => $user['name'],
		    'siteUrl' => $url,
                    'button' => $buttonEmbed
		));
		break;

	    case 2:
		$user1 = reset($from);
		$user2 = next($from);
		$content = PEEP::getLanguage()->text('contactimporter', 'facebook_canvas_page_2', array(
		    'user1' => $user1['name'],
		    'user2' => $user2['name'],
		    'siteUrl' => $url,
                    'button' => $buttonEmbed
		));
		break;

	    default:
		$user = reset($from);
		$content = PEEP::getLanguage()->text('contactimporter', 'facebook_canvas_page_x', array(
		    'user' => $user['name'],
		    'count' => count($from) - 1,
		    'siteUrl' => $url,
                    'button' => $buttonEmbed
		));
	}

	$this->assign('content', $content);
    }
}