<?php

class BASE_CMP_ConsoleSwitchLanguage extends BASE_CMP_ConsoleDropdownHover
{
    /**
     * Constructor.
     *
     */
public function __construct()
    {
        parent::__construct('');

        $template = PEEP::getPluginManager()->getPlugin('BASE')->getCmpViewDir() . 'console_switch_language.html';
        $this->setTemplate($template);

        $languages = BOL_LanguageService::getInstance()->getLanguages();
        $session_language_id = BOL_LanguageService::getInstance()->getCurrent()->getId();

        $active_languages = array();

        foreach($languages as $id=>$language)
        {
            if ( $language->status == 'active' )
            {
                $tag = $this->parseCountryFromTag($language->tag);

                $active_lang = array(
                    'id'=>$language->id,
                    'label'=>$tag['label'],
                    'order'=>$language->order,
                    'tag'=>$language->tag,
                    'class'=>"peep_console_lang{$tag['country']}",
                    'url'=> PEEP::getRequest()->buildUrlQueryString(null, array( "language_id"=>$language->id ) ),
                    'is_current'=>false
                    );

                if ( $session_language_id == $language->id )
                {
                        $active_lang['is_current'] = true;
                        $this->assign('label', $tag['label']);
                        $this->assign('class', "peep_console_lang{$tag['country']}");
                }

                $active_languages[] = $active_lang;
            }
        }

        if ( count($active_languages) <= 1)
        {
            $this->setVisible(true);
            
        }

        function sortActiveLanguages($lang1, $lang2 )
        {
            return ( $lang1['order'] < $lang2['order'] ) ? -1 : 1;
        }
        usort($active_languages, 'sortActiveLanguages');

        $switchLanguage = new BASE_CMP_SwitchLanguage($active_languages);
        $this->setContent($switchLanguage->render());
    }

    protected function parseCountryFromTag($tag)
    {
        $tags = preg_match("/^([a-zA-Z]{2})$|^([a-zA-Z]{2})-([a-zA-Z]{2})(-\w*)?$/", $tag, $matches);

        if (empty($matches))
        {
            return array("label"=>$tag, "country"=>"");
        }
        if (!empty($matches[1]))
        {
            $country = strtolower($matches[1]);
            return array("label"=>$matches[1], "country"=>"_".$country);
        }
        else if (!empty($matches[2]))
        {
            $country = strtolower($matches[3]);
            return array("label"=>$matches[2], "country"=>"_".$country);
        }

        return "";
    }

    protected function initJs()
    {
        $js = UTIL_JsGenerator::newInstance();
        $js->addScript('PEEP.Console.addItem(new PEEP_ConsoleDropdownClick({$uniqId}, {$contentIniqId}), {$key});', array(
            'key' => $this->getKey(),
            'uniqId' => $this->consoleItem->getUniqId(),
            'contentIniqId' => $this->consoleItem->getContentUniqId()
        ));

        PEEP::getDocument()->addOnloadScript($js);
    }

}
