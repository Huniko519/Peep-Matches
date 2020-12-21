<?php

class BASE_CMP_MiniAvatarUserList extends BASE_CMP_AvatarUserList
{

    /**
     * Constructor.
     *
     * @param array $idList
     */
    public function __construct( array $idList )
    {
        parent::__construct( $idList );
        $this->setTemplate(PEEP::getPluginManager()->getPlugin('base')->getCmpViewDir().'avatar_user_list.html');
        $this->setCustomCssClass(BASE_CMP_AvatarUserList::CSS_CLASS_MINI_AVATAR);
    }
    
    public function getAvatarInfo( $idList )
    {
        return BOL_AvatarService::getInstance()->getDataForUserAvatars($idList, true, true, true, false);
    }
}