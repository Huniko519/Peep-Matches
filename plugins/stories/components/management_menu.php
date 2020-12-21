<?php

class STORIES_CMP_ManagementMenu extends PEEP_Component
{

    public function __construct()
    {
        parent::__construct();

        $language = PEEP::getLanguage()->getInstance();

        $item[0] = new BASE_MenuItem();

        $item[0]->setLabel($language->text('stories', 'manage_page_menu_published'))
            ->setOrder(0)
            ->setKey(0)
            ->setUrl(PEEP::getRouter()->urlForRoute('story-manage-posts'))
            ->setActive(PEEP::getRequest()->getRequestUri() == PEEP::getRouter()->uriForRoute('story-manage-posts'))
            ->setIconClass('peep_ic_clock');

        $item[1] = new BASE_MenuItem();

        $item[1]->setLabel($language->text('stories', 'manage_page_menu_drafts'))
            ->setOrder(1)
            ->setKey(1)
            ->setUrl(PEEP::getRouter()->urlForRoute('story-manage-drafts'))
            ->setActive(PEEP::getRequest()->getRequestUri() == PEEP::getRouter()->uriForRoute('story-manage-drafts'))
            ->setIconClass('peep_ic_geer_wheel');

        $item[2] = new BASE_MenuItem();

        $item[2]->setLabel($language->text('stories', 'manage_page_menu_comments'))
            ->setOrder(2)
            ->setKey(2)
            ->setUrl(PEEP::getRouter()->urlForRoute('story-manage-comments'))
            ->setActive(PEEP::getRequest()->getRequestUri() == PEEP::getRouter()->uriForRoute('story-manage-comments'))
            ->setIconClass('peep_ic_comment');

        $menu = new BASE_CMP_ContentMenu($item);

        $this->addComponent('menu', $menu);
    }
}