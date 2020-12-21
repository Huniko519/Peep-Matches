<?php

class PHOTO_CMP_PhotoFloatbox extends PEEP_Component
{
    public function __construct( $layout, $params )
    {
        parent::__construct();

        if ( empty($params['available']) )
        {
            if ( !empty($params['msg']) )
            {
                $msg = $params['msg'];
            }
            else
            {
                $msg = PEEP::getLanguage()->text('base', 'authorization_failed_feedback');
            }

            $this->assign('authError', $msg);

            return;
        }
        
        switch ( $layout )
        {
            case 'page':
                $class = ' peep_photoview_info_onpage';
                break;
            default:
                if ( (bool)PEEP::getConfig()->getValue('photo', 'photo_view_classic') )
                {
                    $class = ' peep_photoview_pint_mode';
                }
                else
                {
                    $class = '';
                }
                break;
        }
        
        $this->assign('class', $class);
        $this->assign('layout', $layout);
    }
}
