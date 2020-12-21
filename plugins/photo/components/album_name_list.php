<?php

class PHOTO_CMP_AlbumNameList extends PEEP_Component
{
    /**
     * @param int $userId
     */
    public function __construct( $userId, $exclude )
    {
        parent::__construct();

        if ( empty($userId) )
        {
            $this->setVisible(false);

            return;
        }

        $this->assign('albumNameList', PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumNameListByUserId($userId, $exclude));
    }
}