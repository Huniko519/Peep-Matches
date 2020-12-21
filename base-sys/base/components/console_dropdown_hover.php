<?php

class BASE_CMP_ConsoleDropdownHover extends BASE_CMP_ConsoleDropdown
{
    protected $url = 'javascript://';

    public function __construct($label, $key = null)
    {
        parent::__construct($label, $key);

        $template = PEEP::getPluginManager()->getPlugin('BASE')->getCmpViewDir() . 'console_dropdown_hover.html';
        $this->setTemplate($template);

        $this->addClass('peep_console_dropdown_hover');
$avatarService = BOL_AvatarService::getInstance();
        $userId = PEEP::getUser()->getId();
$avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $this->assign('avatar', $avatars[$userId]);

    }

    protected function initJs()
    {
        $js = UTIL_JsGenerator::newInstance();
        $js->addScript('PEEP.Console.addItem(new PEEP_ConsoleDropdownHover({$uniqId}, {$contentIniqId}), {$key});', array(
            'key' => $this->getKey(),
            'uniqId' => $this->consoleItem->getUniqId(),
            'contentIniqId' => $this->consoleItem->getContentUniqId()
        ));

        PEEP::getDocument()->addOnloadScript($js);
    }

    public function setUrl( $url )
    {
        $this->url = $url;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign('url', $this->url);
    }
}