<?php

class GOOGLELOCATION_CMP_MiniAvatarList extends BASE_CMP_AvatarUserList
{

    protected $avatarData = array();
    /**
     * Constructor.
     *
     * @param array $idList
     */
    public function __construct( $avatarData )
    {
        parent::__construct( array() );
        $this->idList = array( 1 );
        $this->setTemplate(PEEP::getPluginManager()->getPlugin('base')->getCmpViewDir().'avatar_user_list.html');
        $this->setCustomCssClass(BASE_CMP_AvatarUserList::CSS_CLASS_MINI_AVATAR);
        
        if ( !empty($avatarData) )
        {
            $this->avatarData = $avatarData;
        }
    }
    
    public function getAvatarInfo( $idList = null )
    {
        return $this->avatarData;
    }
}