<?php

class BASE_CMP_AvatarUserList extends PEEP_Component
{
    const CSS_CLASS_MINI_AVATAR = 'peep_mini_avatar';

    /**
     * @var array
     */
    protected $idList;
    /**
     * @var string
     */
    protected $viewMoreUrl;
    /**
     * @var boolean
     */
    protected $emptyListNoRender = false;
    /**
     * @var atring
     */
    protected $customCssClass = '';

    public function setViewMoreUrl( $viewMoreUrl )
    {
        $this->viewMoreUrl = trim($viewMoreUrl);
    }

    public function setEmptyListNoRender( $emptyListNoRender )
    {
        $this->emptyListNoRender = (bool) $emptyListNoRender;
    }

    public function setIdList( array $idList )
    {
        $this->idList = $idList;
    }

    public function setCustomCssClass( $customCssClass )
    {
        $this->customCssClass = (string) $customCssClass;
    }

    /**
     * Constructor.
     *
     * @param array $idList
     */
    public function __construct( array $idList = array() )
    {
        parent::__construct();
        $this->idList = $idList;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        if ( empty($this->idList) )
        {
            if ( $this->emptyListNoRender )
            {
                $this->setVisible(false);
            }

            return;
        }

        $avatars = $this->getAvatarInfo($this->idList);
        
        $event = new PEEP_Event('bookmarks.is_mark', array(), $avatars);
        PEEP::getEventManager()->trigger($event);
        
        if ( $event->getData() )
        {
            $avatars = $event->getData();
        }

        if ( $this->viewMoreUrl !== null )
        {
            $this->assign('view_more_array', array('url' => $this->viewMoreUrl, 'title' => PEEP::getLanguage()->text('base', 'view_more_label')));
        }

        $this->assign('users', $avatars);
        $this->assign('css_class', $this->customCssClass);
    }

    public function getAvatarInfo( $idList )
    {
        return BOL_AvatarService::getInstance()->getDataForUserAvatars($idList);
    }
}