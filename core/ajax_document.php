<?php

class PEEP_AjaxDocument extends PEEP_HtmlDocument
{

    public function __construct()
    {
        $this->setType(PEEP_Document::AJAX);
    }

    public function getOnloadScript()
    {
        $onloadJS = '';

        ksort($this->onloadJavaScript['items']);

        foreach ( $this->onloadJavaScript['items'] as $priority => $scripts )
        {
            foreach ( $scripts as $script )
            {
                $onloadJS .= $script;
            }
        }

        return $onloadJS;
    }
    
    public function getScriptBeforeIncludes()
    {
        $onloadJS = '';
        
        ksort($this->preIncludeJavaScriptDeclarations);

        foreach ( $this->preIncludeJavaScriptDeclarations as $priority => $types )
        {
            foreach ( $types as $type => $declarations )
            {
                foreach ( $declarations as $declaration )
                {
                    $onloadJS .= $declaration . PHP_EOL;
                }
            }
        }
        
        return $onloadJS;
    }

    public function getScripts()
    {
        $jsUrlList = array();

        ksort($this->javaScripts['items']);

        foreach ( $this->javaScripts['items'] as $priority => $types )
        {
            foreach ( $types as $type => $urls )
            {
                foreach ( $urls as $url )
                {
                    $jsUrlList[] = $url;
                }
            }
        }

        return $jsUrlList;
    }

    public function getStyleSheets()
    {
        $cssFiles = array();

        ksort($this->styleSheets['items']);

        foreach ( $this->styleSheets['items'] as $priority => $scipts )
        {
            foreach ( $scipts as $media => $urls )
            {
                foreach ( $urls as $url )
                {
                    $cssFiles[] = $url;
                }
            }
        }

        return $cssFiles;
    }

    public function getStyleDeclarations()
    {
        $cssCode = '';

        ksort($this->styleDeclarations['items']);

        foreach ( $this->styleDeclarations['items'] as $priority => $mediaTypes )
        {
            foreach ( $mediaTypes as $media => $declarations )
            {
                foreach ( $declarations as $declaration )
                {
                    $cssCode .= $declaration;
                }
            }
        }

        return $cssCode;
    }

    public function render()
    {
        //TODO compile all scripts, styles, assigned vars and send as JSON array
        return '';
    }
}