<?php

class BASE_CMP_ConsoleDropdownClick extends BASE_CMP_ConsoleDropdown
{
    public function __construct($label, $key = null)
    {
        parent::__construct($label, $key);

        $template = PEEP::getPluginManager()->getPlugin('BASE')->getCmpViewDir() . 'console_dropdown_click.html';
        $this->setTemplate($template);

        $this->addClass('peep_console_dropdown_click');
    }

    protected function initJs()
    {
        $js = UTIL_JsGenerator::newInstance();
        $js->addScript('PEEP.Console.addItem(new PEEP_ConsoleDropdownClick({$uniqId}, {$contentIniqId}), {$key});', array(
            'uniqId' => $this->consoleItem->getUniqId(),
            'key' => $this->getKey(),
            'contentIniqId' => $this->consoleItem->getContentUniqId()
        ));

        PEEP::getDocument()->addOnloadScript($js);

        return $this->consoleItem->getUniqId();
    }
}