<?php

function smarty_function_online_now( $params, $smarty )
{
    $chatNowMarkup = '';
    if ( PEEP::getUser()->isAuthenticated() && isset($params['userId']) && PEEP::getUser()->getId() != $params['userId'])
    {
        $allowChat = PEEP::getEventManager()->call('base.online_now_click', array('userId'=>PEEP::getUser()->getId(), 'onlineUserId'=>$params['userId']));

        if ($allowChat)
        {
            $chatNowMarkup = '<span id="peep_chat_now_'.$params['userId'].'" class="peep_lbutton peep_green" onclick="PEEP.trigger(\'base.online_now_click\', [ \'' . $params['userId'] . '\' ] );" >' . PEEP::getLanguage()->text('base', 'user_list_chat_now') . '</span><span id="peep_preloader_content_'.$params['userId'].'" class="peep_preloader_content peep_hidden"></span>';
        }
    }

    $buttonMarkup = '<div class="peep_miniic_live"><span class="peep_live_on"></span>'.$chatNowMarkup.'</div>';

    return $buttonMarkup;
}
?>
