<?php

class SEARCHSYS_CMP_ConsoleResultItem extends PEEP_Component
{
    public function __construct( $data )
    {
        parent::__construct();

        if ( $data['avatar'] === null )
        {
            $data['avatar'] = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
        }

        if ( !empty($data['info']) )
        {
            $data['info'] = mb_substr($data['info'], 0, 70);
        }

        $this->assign("data", $data);
    }
}