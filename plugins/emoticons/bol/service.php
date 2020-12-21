<?php

class EMOTICONS_BOL_Service
{
    CONST EMOTICONS_DIR_NAME = 'emoticons';
    CONST PROHIBIT_CHAR_REPLACER = '_';
    
    private static $classInstance;
    
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private $plugin;
    private $smilyesDto;
    
    private $prohibitedChars;

    private function __construct()
    {
        $this->smilyesDto = EMOTICONS_BOL_EmoticonsDao::getInstance();
        $this->plugin = PEEP::getPluginManager()->getPlugin( 'emoticons' );
        
        $this->prohibitedChars = array('"', "'", '<', '>');
    }
    
    public function findSmileById( $id )
    {
        return $this->smilyesDto->findById($id);
    }

    public function getEmoticonsDir()
    {
        return $this->plugin->getUserFilesDir() . DS . self::EMOTICONS_DIR_NAME . DS;
    }
    
    public function getEmoticonsUrl()
    {
        return $this->plugin->getUserFilesUrl() . self::EMOTICONS_DIR_NAME . '/';
    }

    public function getAllEmoticons()
    {
        return $this->smilyesDto->getAllEmoticons();
    }

    public function updateEmoticonsOrder( $order )
    {
        return $this->smilyesDto->updateEmoticonsOrder($order);
    }
    
    public function isSmileCodeBusy( $code )
    {
        return $this->smilyesDto->findSmileByCode($code) !== NULL;
    }
    
    public function sanitizeCode( $code )
    {
        return trim(str_replace($this->prohibitedChars, self::PROHIBIT_CHAR_REPLACER, $code));
    }

    public function getProhibitedChars()
    {
        return $this->prohibitedChars;
    }
    
    public function getFreeOrder()
    {
        $maxOrder = $this->smilyesDto->getMaxOrder();
        
        return ++$maxOrder;
    }
    
    public function getEmoticonsKeyPair()
    {
        static $keyPair = array();
        
        if ( empty($keyPair) )
        {
            foreach ( $this->getAllEmoticons() as $smile )
            {
                $keyPair[$smile->code] = $smile->name;
            }
        }
        
        return $keyPair;
    }
    
    public function getEmoticonsKeyPairWrapInTag()
    {
        static $keyPair = array();
        
        if ( empty($keyPair) )
        {
            $url = $this->getEmoticonsUrl();
            
            foreach ( $this->getEmoticonsKeyPair() as $code => $name )
            {
                $keyPair[$code] = '<img src="' . $url . $name . '" title="' . $code . '" />';
            }
        }
        
        return $keyPair;
    }

    public function replace( $text )
    {
        $json = $this->getEmoticonsKeyPairWrapInTag();
        
        return str_ireplace(array_keys($json), array_values($json), $text);
    }
    
    public function getFreeSmileCategory()
    {
        $maxId = $this->smilyesDto->getMaxId();
        
        return ++$maxId;
    }
    
    public function findEmoticonsByCategory( $categoryId )
    {
        return $this->smilyesDto->findEmoticonsByCategory($categoryId);
    }
    
    public function deleteEmoticonsByCategory( $categoryId )
    {
        return $this->smilyesDto->deleteEmoticonsByCategory($categoryId);
    }
    
    public function setSmileCaption( $smileId, $categoryId )
    {
        return $this->smilyesDto->setSmileCaption($smileId, $categoryId);
    }

    // ************************* Begin: Deprecated ************************** \\
    
    public function getSmilesCategories()
    {
        $categories = scandir( $this->plugin->getUserFilesDir() . 'images' . DS );
        unset( $categories[0] );
        unset( $categories[1] );

        return array_unique( array_map('strtolower', $categories) );
    }
    
    public function getEmoticonsByCategory( $category )
    {
        $dir = $this->plugin->getUserFilesDir() . 'images' . DS . strtolower( $category );
        
        if ( !empty($category) && file_exists($dir) )
        {
            $emoticons = scandir( $dir );
            unset( $emoticons[0] );
            unset( $emoticons[1] );
            
            return array_unique( array_map('strtolower', $emoticons) );
        }
        else
        {
            return array();
        }
    }
    
    public function getThemeList()
    {
        $themes = scandir( $this->plugin->getStaticDir() . 'css' . DS . 'ui' );
        unset( $themes[0] );
        unset( $themes[1] );
        
        return $themes;
    }
    
    // *********************** End: Deprecated ****************************** \\
}
