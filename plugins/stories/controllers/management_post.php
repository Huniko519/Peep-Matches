<?php

class STORIES_CTRL_ManagementPost extends PEEP_ActionController
{
    private
    $language,
    $service;

    public function __construct()
    {

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $this->setPageHeading(PEEP::getLanguage()->text('stories', 'management_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_write');

        $this->language = PEEP::getLanguage();
        $this->service = PostService::getInstance();

        $this->addComponent('menu', new STORIES_CMP_ManagementMenu());
    }

    public function index()
    {
        $userId = PEEP::getUser()->getId();

        $page = empty($_GET['page']) ? 1 : $_GET['page'];

        $rpp = 5;


        $first = ($page - 1) * $rpp;

        $count = $rpp;

        $data = $this->getData($userId, $first, $count);

        $list = $data['list'];

        $itemCount = $data['count'];

        $pageCount = ceil($itemCount / $rpp);

        $this->assign('list', $list);
        $this->assign('status', $data['status']);

        $this->assign('thisUrl', PEEP_URL_HOME . PEEP::getRequest()->getRequestUri());

        $this->addComponent('paging', new BASE_CMP_Paging($page, $pageCount, 5));
    }

    private function getData( $userId, $first, $count )
    {
        switch ( $this->getCase() )
        {
            case 'posts':
                return array(
                    'status' => $this->language->text('stories', 'status_published'),
                    'list' => $this->service->findUserPostList($userId, $first, $count),
                    'count' => $this->service->countUserPost($userId),
                );

            case 'drafts':
                return array(
                    'status' => $this->language->text('stories', 'status_draft'),
                    'list' => $this->service->findUserDraftList($userId, $first, $count),
                    'count' => $this->service->countUserDraft($userId),
                );
        }

        return array();
    }

    private function getCase()
    {
        switch ( true )
        {
            case ( PEEP::getRouter()->getUri() == PEEP::getRouter()->uriForRoute('story-manage-posts') ):
                return 'posts';

            case ( PEEP::getRouter()->getUri() == PEEP::getRouter()->uriForRoute('story-manage-drafts') ):
                return 'drafts';
        }
    }
}