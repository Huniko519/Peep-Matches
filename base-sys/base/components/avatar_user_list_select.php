<?php

class BASE_CMP_AvatarUserListSelect extends PEEP_Component
{
    /**
     * @var string
     */
    private $buttonLabel;
    /**
     * @var string
     */
    private $countLabel;
    /**
     * @var string
     */
    private $headingLabel;
    /**
     * @var array
     */
    private $idList;

    /**
     * Constructor.
     *
     * @param array $idList
     */
    public function __construct( array $idList, $langs = array() )
    {
        parent::__construct();

        $this->countLabel = PEEP::getLanguage()->text('base', 'avatar_user_list_select_count_label');
        $this->buttonLabel = PEEP::getLanguage()->text('base', 'avatar_user_list_select_button_label');
        $this->idList = $idList;

        if ( !empty($langs['buttonLabel']) )
        {
            $this->buttonLabel = $langs['buttonLabel'];
        }

        if ( array_key_exists('countLabel', $langs) )
        {
            $this->countLabel = $langs['countLabel'];
        }

        if ( !empty($langs['headingLabel']) )
        {
            $this->headingLabel = $langs['headingLabel'];
        }
    }

    /**
     * @param string $buttonLabel
     */
    public function setButtonLabel( $buttonLabel )
    {
        $this->buttonLabel = $buttonLabel;
    }

    /**
     * @param string $countLabel
     */
    public function setCountLabel( $countLabel )
    {
        $this->countLabel = $countLabel;
    }

    /**
     * @param string $generalLabel
     */
    public function setGeneralLabel( $generalLabel )
    {
        $this->headingLabel = $generalLabel;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $contexId = UTIL_HtmlTag::generateAutoId('cmp');
        $this->assign('contexId', $contexId);
        
        if ( empty($this->idList) )
        {
            return;
        }

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($this->idList, true, false, false);
        $this->assign('avatars', $avatars);

        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($this->idList);
        $usernames = BOL_UserService::getInstance()->getUserNamesForList($this->idList);
        $orderdList = BOL_UserService::getInstance()->getRecentlyActiveOrderedIdList($this->idList);

        $this->idList = array();

        foreach( $orderdList as $list )
        {
           $this->idList[] =  $list['id'];
        }

        $arrayToAssign = array();
        $jsArray = array();

        foreach ( $this->idList as $id )
        {
            $linkId = UTIL_HtmlTag::generateAutoId('user-select');

            if ( !empty($avatars[$id]) )
            {
                $avatars[$id]['url'] = 'javascript://';
            }

            $arrayToAssign[$id] = array(
                'id' => $id,
                'title' => empty($displayNames[$id]) ? '_DISPLAY_NAME_' : $displayNames[$id],
                'linkId' => $linkId,
                'username' => $usernames[$id]
            );

            $jsArray[$id] = array(
                'linkId' => $linkId,
                'userId' => $id
            );
        }

        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'avatar_user_select.js');
        PEEP::getDocument()->addOnloadScript("
            var cmp = new AvatarUserSelect(" . json_encode($jsArray) . ", '" . $contexId . "');
            cmp.init();  ");

        PEEP::getLanguage()->text('base', 'avatar_user_select_empty_list_message');

        $this->assign('users', $arrayToAssign);

        $langs = array(
            'countLabel' => $this->countLabel,
            'startCountLabel' => (!empty($this->countLabel) ? str_replace('#count#', '0', $this->countLabel) : null ),
            'buttonLabel' => $this->buttonLabel,
            'startButtonLabel' => str_replace('#count#', '0', $this->buttonLabel)
        );
        $this->assign('langs', $langs);
    }
}