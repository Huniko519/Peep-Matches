<?php

class BASE_CMP_DeleteUser extends PEEP_Component
{

    /**
     * Constructor.
     */
    public function __construct( $params = array() )
    {
        parent::__construct();

        $userId = (int) $params['userId'];
        $showMessage = (bool) $params['showMessage'];

        $rspUrl = PEEP::getRouter()->urlFor('BASE_CTRL_User', 'deleteUser', array(
            'user-id' => $userId
        ));

        $rspUrl = PEEP::getRequest()->buildUrlQueryString($rspUrl, array(
            'showMessage' => (int) $showMessage
        ));

        $js = UTIL_JsGenerator::composeJsString('$("#baseDCButton").click(function()
        {
            var button = this;

            PEEP.inProgressNode(button);

            $.getJSON({$rsp}, function(r)
            {
                PEEP.activateNode(button);

                if ( _scope.floatBox )
                {
                    _scope.floatBox.close();
                }

                if ( _scope.deleteCallback )
                {
                    _scope.deleteCallback(r);
                }
            });
        });', array(
            'rsp' => $rspUrl
        ));

        PEEP::getDocument()->addOnloadScript($js);
    }
}