<?php

class PEEP_ApiDefaultRoute extends OW_DefaultRoute
{

    /**
     * Generates URI using provided params.
     *
     * @throws InvalidArgumentException
     * @param string $controller
     * @param string $action
     * @param array $params
     * @return string
     */
    public function generateUri( $controller, $action = null, array $params = array() )
    {
        throw new LogicException("Cant generate URI in API context");
    }

    /**
     * Returns dispatch params (controller, action, vars) for provided URI.
     * 
     * @throws Redirect404Exception
     * @param string $uri
     * @return array
     */
    public function getDispatchAttrs( $uri )
    {
        throw new Redirect404Exception();
    }
}
