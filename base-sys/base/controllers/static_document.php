<?php

class BASE_CTRL_StaticDocument extends PEEP_ActionController
{
    /**
     * @var BOL_NavigationService
     */
    private $navService;

    public function __construct()
    {
        parent::__construct();
        $this->navService = BOL_NavigationService::getInstance();
    }

    public function index( $params )
    {
        if ( empty($params['documentKey']) )
        {
            throw new Redirect404Exception();
        }

        $language = PEEP::getLanguage();
        $documentKey = $params['documentKey'];

        $document = $this->navService->findDocumentByKey($documentKey);

        if ( $document === null )
        {
            throw new Redirect404Exception();
        }

        $menuItem = $this->navService->findMenuItemByDocumentKey($document->getKey());

        if ( $menuItem !== null )
        {
            if ( !$menuItem->getVisibleFor() || ( $menuItem->getVisibleFor() == BOL_NavigationService::VISIBLE_FOR_GUEST && PEEP::getUser()->isAuthenticated() ) )
            {
                throw new Redirect403Exception();
            }

            if ( $menuItem->getVisibleFor() == BOL_NavigationService::VISIBLE_FOR_MEMBER && !PEEP::getUser()->isAuthenticated() )
            {
                throw new AuthenticateException();
            }
        }

        $this->assign('content', $language->text('base', "local_page_content_{$document->getKey()}"));
        $this->setPageHeading($language->text('base', "local_page_title_{$document->getKey()}"));
        $this->setPageTitle($language->text('base', "local_page_title_{$document->getKey()}"));
        $this->documentKey = $document->getKey();

        $this->setDocumentKey($document->getKey());

        PEEP::getEventManager()->bind(PEEP_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'setCustomMetaInfo'));
    }

    public function setCustomMetaInfo()
    {
        PEEP::getDocument()->setDescription(null);

        if ( PEEP::getLanguage()->valueExist('base', "local_page_meta_tags_{$this->getDocumentKey()}") )
        {
            PEEP::getDocument()->addCustomHeadInfo(PEEP::getLanguage()->text('base', "local_page_meta_tags_{$this->getDocumentKey()}"));
        }
    }
}
