<?php


class EMOTICONS_CMP_Panel extends PEEP_Component
{
    public function __construct()
    {
        parent::__construct();
        
        $service = EMOTICONS_BOL_Service::getInstance();
        $plugin = PEEP::getPluginManager()->getPlugin('emoticons');
        $document = PEEP::getDocument();
        
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'emoticons.css');

        $document->addScriptDeclarationBeforeIncludes(
            UTIL_JsGenerator::composeJsString(
                ';window.EMOTICONSPARAMS = Object.defineProperties({}, {
                    emoticonsUrl: {value: {$emoticonsUrl}},
                    emoticons: {value: {$emoticons}},
                    btnBackground: {value: {$backgroundUrl}}
                });Object.freeze(window.EMOTICONSPARAMS);', array(
                    'emoticonsUrl' => $service->getEmoticonsUrl(),
                    'emoticons' => $service->getEmoticonsKeyPair(),
                    'backgroundUrl' => $plugin->getStaticUrl() . 'images/emoj_panel.png'
                )
            )
        );

        $document->addScript($plugin->getStaticJsUrl() . 'emoticons.js', 'text/javascript', 9999);

        $this->assign('width', (int)PEEP::getConfig()->getValue('emoticons', 'width'));
        $this->assign('url', $service->getEmoticonsUrl());
        
        $emoticons = array();
        $captions = array();
        
        foreach ( $service->getAllEmoticons() as $smile )
        {
            if ( !isset($emoticons[$smile->category]) )
            {
                $emoticons[$smile->category] = array();
            }
            
            $emoticons[$smile->category][] = $smile;
            
            if ( !empty($smile->isCaption) && !isset($captions[$smile->category]) )
            {
                $captions[$smile->category] = $smile->name;
            }
        }
        
        $this->assign('captions', $captions, $smile->name);
        
        if ( count($emoticons) === 1 )
        {
            $keys = array_keys($emoticons);
            $this->assign('emoticons', $emoticons[$keys[0]]);
            $this->assign('isSingle', TRUE);
        }
        else
        {
            $this->assign('emoticons', $emoticons);
            $this->assign('isSingle', FALSE);
        }
    }
}
