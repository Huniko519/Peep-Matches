<?php

class BASE_CMP_BlockUser extends PEEP_Component
{

    /**
     * Constructor.
     */
    public function __construct( $params = array() )
    {
        parent::__construct();

        $userId = (int) $params['userId'];

        $js = UTIL_JsGenerator::composeJsString('$("#baseBlockButton").click(function(){
           _scope.confirmCallback();
        });');

        PEEP::getDocument()->addOnloadScript($js);
    }
}