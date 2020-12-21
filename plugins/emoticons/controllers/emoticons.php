<?php


class EMOTICONS_CTRL_Emoticons extends PEEP_ActionController
{
    public function getEmoticonsByCategory( array $param = array() )
    {
        exit(json_encode(EMOTICONS_BOL_Service::getInstance()->getEmoticonsByCategory($_POST['category'])));
    }
}
