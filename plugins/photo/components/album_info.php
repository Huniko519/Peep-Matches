<?php

class PHOTO_CMP_AlbumInfo extends PEEP_Component
{
    public function __construct( $params )
    {
        parent::__construct();

        $album = $params['album'];
        $coverEvent = PEEP::getEventManager()->trigger(
            new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_GET_ALBUM_COVER_URL, array('albumId' => $album->id))
        );
        $coverData = $coverEvent->getData();

        $this->assign('album', $album);
        $this->assign('coverUrl', $coverData['coverUrl']);
        $this->assign('coverUrlOrig', $coverData['coverUrlOrig']);
    }
}
