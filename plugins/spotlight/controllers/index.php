<?php

class SPOTLIGHT_CTRL_Index extends PEEP_ActionController
{

    public function ajax( $params )
    {
        SPOTLIGHT_CMP_Floatbox::process($_POST);
    }
}

?>
