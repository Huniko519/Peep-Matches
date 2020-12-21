<?php

class PHOTO_Import extends DATAIMPORTER_CLASS_Import
{
    public function import( $params )
    {
        $importDir = $params['importDir'];

        $txtFile = $importDir . 'configs.txt';
        
        // import configs
        if ( file_exists($txtFile) )
        {
            $string = file_get_contents($txtFile);
            $configs = json_decode($string, true);    
        }
        
        if ( !$configs )
        {
            return;
        }
        
        $photoService = PHOTO_BOL_PhotoService::getInstance();
        
        $types = array('main', 'preview', 'original');
        $photoDir = $photoService->getPhotoUploadDir();
        
        $page = 1;
        while ( true )
        {
            $photos = $photoService->findPhotoList('latest', $page, 10);
            $page++;
            
            if ( empty($photos) )
            {
                break;
            }
            
            foreach ( $photos as $photo )
            {
                foreach ( $types as $type )
                {
                    $path = $photoService->getPhotoPath($photo['id'], $photo['hash'], $type);
                    $photoName = str_replace($photoDir, '', $path);
                    $content = file_get_contents($configs['url'] . '/' . $photoName);
                    if ( mb_strlen($content) )
                    {
                        PEEP::getStorage()->fileSetContent($path, $content);
                    }
                }
            }
        }
    }
}
