<?php

class BASE_CTRL_AjaxLoader extends PEEP_ActionController
{
    public function init()
    {
        if( !PEEP::getRequest()->isAjax() )
        {
           throw new Redirect404Exception();
        }
    }

    public function component()
    {
        if ( empty($_GET['cmpClass']) )
        {
            exit;
        }

        $cmpClass = trim($_GET['cmpClass']);
        $params = empty($_POST['params']) ? array() : json_decode($_POST['params'], true);
        
        $cmp = PEEP::getClassInstanceArray($cmpClass, $params);
        $responce = $this->getComponentMarkup($cmp);

        exit(json_encode($responce));
    }

    protected function getComponentMarkup( PEEP_Component $cmp )
    {

        /* @var $document PEEP_AjaxDocument */
        $document = PEEP::getDocument();

        $responce = array();

        $responce['content'] = trim($cmp->render());

        $beforeIncludes = $document->getScriptBeforeIncludes();
        if ( !empty($beforeIncludes) )
        {
            $responce['beforeIncludes'] = $beforeIncludes;
        }

        foreach ( $document->getScripts() as $script )
        {
            $responce['scriptFiles'][] = $script;
        }

        $onloadScript = $document->getOnloadScript();
        if ( !empty($onloadScript) )
        {
            $responce['onloadScript'] = $onloadScript;
        }

        $styleDeclarations = $document->getStyleDeclarations();
        if ( !empty($styleDeclarations) )
        {
            $responce['styleDeclarations'] = $styleDeclarations;
        }

        $styleSheets = $document->getStyleSheets();
        if ( !empty($styleSheets) )
        {
            $responce['styleSheets'] = $styleSheets;
        }

        return $responce;
    }
}