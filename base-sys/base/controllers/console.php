<?php

class BASE_CTRL_Console extends PEEP_ActionController
{
    public function listRsp()
    {
        $request = json_decode($_POST['request'], true);

        $event = new BASE_CLASS_ConsoleListEvent('console.load_list', $request, $request['data']);
        PEEP::getEventManager()->trigger($event);

        $responce = array();
        $responce['items'] = $event->getList();

        $responce['data'] = $event->getData();
        $responce['markup'] = array();

        /* @var $document PEEP_AjaxDocument */
        $document = PEEP::getDocument();

        $responce['markup']['scriptFiles'] = $document->getScripts();
        $responce['markup']['onloadScript'] = $document->getOnloadScript();
        $responce['markup']['styleDeclarations'] = $document->getStyleDeclarations();
        $responce['markup']['styleSheets'] = $document->getStyleSheets();
        $responce['markup']['beforeIncludes'] = $document->getScriptBeforeIncludes();

        echo json_encode($responce);

        exit;
    }
}