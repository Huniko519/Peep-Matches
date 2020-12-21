<?php

class BASE_CTRL_ApiServer extends PEEP_ActionController
{
    public function request()
    {
        $trustedIp = $_SERVER["SERVER_ADDR"]; 
        
        if ( defined('PEEP_API_TRUSTED_IP')  )
        {
            $trustedIp = PEEP_API_TRUSTED_IP;
        }
        
        if ( $trustedIp != $_SERVER["REMOTE_ADDR"] )
        {
            $this->error('Request from untrusted IP address', 0);
        }
        
        if ( empty($_GET['controller']) || empty($_GET['action']) )
        {
            $this->error('Incorrect request: controller or action is empty', 1);
        }
        
        $controllerClass = trim($_GET['controller']);
        $action = trim($_GET['action']);
        
        if ( !class_exists( $controllerClass, true ) )
        {
            $this->error('Controller class is not exists', 2);
        }
        
        $controller = new $controllerClass();
        
        // check if controller exists and is instance of base action controller class
        if( $controller === null || !$controller instanceof PEEP_ActionController )
        {
            $this->error("Can't dispatch request! Please provide valid controller class!", 3);
        }
        /* @var $controller PEEP_ActionController */
        $controller->init();
        
        $data = empty($_GET['data']) ? null : @json_decode(urldecode($_GET['data']), true);
        $data = empty($data) ? array() : $data;
        
        try 
        {        
            $responce = $controller->$action($data);
        }
        catch ( Exception $e )
        {
            $this->error($e->getMessage(), 4, 'actionException');
        }
        
        $this->success($responce);
    }
    
    private function error($message, $code, $errorType = 'requestError')
    {
        echo json_encode(array(
                'responseType' => 'error',
                'error' => $code,
                'errorMessage' => $message,
                'errorType' => $errorType
            ));
        
        exit;
    }
    
    public function success( $data )
    {
        echo json_encode(array(
                'responseType' => 'success',
                'data' => $data
            ));
        
        exit;
    }
}