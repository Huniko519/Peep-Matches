<?php

class BASE_CMP_ConsoleList extends PEEP_Component
{
    protected $viewAll = null, $itemKey, $listRsp;


    public function __construct( $consoleItemKey )
    {
        parent::__construct();

        $this->itemKey = $consoleItemKey;
        $this->listRsp = PEEP::getRouter()->urlFor('BASE_CTRL_Console', 'listRsp');
    }

    public function initJs()
    {
        $js = UTIL_JsGenerator::composeJsString('$.extend(PEEP.Console.getItem({$key}), PEEP_ConsoleList).construct({$params});', array(
            'key' => $this->itemKey,
            'params' => array(
                'rsp' => $this->listRsp,
                'key' => $this->itemKey
            )
        ));

        PEEP::getDocument()->addOnloadScript($js);
    }

    public function setViewAll( $label, $url )
    {
        $this->viewAll = array(
            'label' => $label,
            'url' => $url
        );
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign('viewAll', $this->viewAll);
    }
}