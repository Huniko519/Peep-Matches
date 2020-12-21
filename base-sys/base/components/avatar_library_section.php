<?php

class BASE_CMP_AvatarLibrarySection extends PEEP_Component
{
    public function __construct( $list, $offset, $count )
    {
        parent::__construct();

        $this->assign('list', $list);
        $this->assign('count', $count);
        $this->assign('loadMore', $count - $offset > BOL_AvatarService::AVATAR_CHANGE_GALLERY_LIMIT);
    }
}