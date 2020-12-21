<?php

class PHOTO_Export extends DATAEXPORTER_CLASS_Export
{
    public function excludeTableList()
    {
        return array();
    }

    public function includeTableList()
    {
        return array(PEEP_DB_PREFIX . 'photo');
    }

    public function export( $params )
    {
        $photoService = PHOTO_BOL_PhotoService::getInstance();
        
        $url = PEEP::getStorage()->getFileUrl($photoService->getPhotoUploadDir());
        
        /* @var $za ZipArchives */
        $za = $params['zipArchive'];
        $archiveDir = $params['archiveDir'];

        $string = json_encode(array('url' => $url));
        $za->addFromString($archiveDir . '/' . 'configs.txt', $string);
    }
}