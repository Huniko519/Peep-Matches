<?php

class BOL_MediaPanelService
{
    /*
     * @var BOL_MediaPanelFileDao
     */
    private $dao;

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        $this->dao = BOL_MediaPanelFileDao::getInstance();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_MediaPanelService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_MediaPanelService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function add( $plugin, $type, $userId, $data, $stamp=null )
    {
        $o = new BOL_MediaPanelFile();

        $this->dao->save(
                $o->setPlugin($plugin)
                ->setType($type)
                ->setUserId($userId)
                ->setData($data)
                ->setStamp(empty($stamp) ? time() : $stamp)
        );

        return $o->getId();
    }

    public function findGalleryImages( $plugin, $userId=null, $first, $count )
    {
        return $this->dao->findImages($plugin, $userId, $first, $count);
    }

    public function findImage( $imageId )
    {
        return $this->dao->findImage($imageId);
    }

    public function countGalleryImages( $plugin, $userId=null )
    {
        return $this->dao->countGalleryImages($plugin, $userId);
    }

    public function deleteImages( $plugin, $count )
    {
        $this->dao->deleteImages($plugin, $count);
    }
    
    public function deleteById($id)
    {
    	$this->dao->deleteImageById($id);
    }
    
    public function findAll()
    {
         return $this->dao->findAll();       
    }

    public function deleteImagesByUserId($userId)
    {
        return $this->dao->deleteImagesByUserId($userId);
    }
}