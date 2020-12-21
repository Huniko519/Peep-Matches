<?php

class GOOGLEAUTH_CLASS_AuthAdapter extends PEEP_RemoteAuthAdapter
{

    public function __construct( $remoteId )
    {
        parent::__construct($remoteId, 'google');
    }
}

?>