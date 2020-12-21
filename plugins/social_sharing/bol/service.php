<?php

final class SOCIALSHARING_BOL_Service
{
   
    private static $classInstance;

    private function __construct()
    {
        
    }

    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDefaultImagePath()
    {
        return PEEP::getPluginManager()->getPlugin('socialsharing')->getUserFilesDir().'logo.jpg';
    }

    public function getDefaultImageUrl()
    {
        return PEEP::getPluginManager()->getPlugin('socialsharing')->getUserFilesUrl().'logo.jpg';
    }

    public function uploadImage( $uploadedFileName )
    {
        $image = new UTIL_Image($uploadedFileName);
        $imagePath = $this->getDefaultImagePath();

        $width = $image->getWidth();
        $height = $image->getHeight();

        $side = $width >= $height ? $height : $width;
        $side = $side > 200 ? 200 : $side;

        $image->resizeImage($side, $side, true)->saveImage($imagePath);
    }
}