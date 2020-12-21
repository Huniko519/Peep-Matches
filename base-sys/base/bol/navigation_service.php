<?php

class BOL_NavigationService
{
    const MENU_TYPE_MAIN = BOL_MenuItemDao::VALUE_TYPE_MAIN;
    const MENU_TYPE_BOTTOM = BOL_MenuItemDao::VALUE_TYPE_BOTTOM;
    const MENU_TYPE_HIDDEN = BOL_MenuItemDao::VALUE_TYPE_HIDDEN;
    const MENU_TYPE_ADMIN = BOL_MenuItemDao::VALUE_TYPE_ADMIN;
    const MENU_TYPE_SETTINGS = BOL_MenuItemDao::VALUE_TYPE_SETTINGS;
    const MENU_TYPE_PAGES = BOL_MenuItemDao::VALUE_TYPE_PAGES;
    const MENU_TYPE_APPEARANCE = BOL_MenuItemDao::VALUE_TYPE_APPEARANCE;
    const MENU_TYPE_USERS = BOL_MenuItemDao::VALUE_TYPE_USERS;
    const MENU_TYPE_PLUGINS = BOL_MenuItemDao::VALUE_TYPE_PLUGINS;
    const MENU_TYPE_PRIVACY = BOL_MenuItemDao::VALUE_TYPE_PRIVACY;
    

    const VISIBLE_FOR_GUEST = BOL_MenuItemDao::VALUE_VISIBLE_FOR_GUEST;
    const VISIBLE_FOR_MEMBER = BOL_MenuItemDao::VALUE_VISIBLE_FOR_MEMBER;
    const VISIBLE_FOR_ALL = BOL_MenuItemDao::VALUE_VISIBLE_FOR_ALL;

    /**
     * @var BOL_DocumentDao
     */
    private $documentDao;
    /**
     * @var BOL_MenuItemDao
     */
    private $menuItemDao;
    /**
     * @var BOL_NavigationService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_NavigationService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->documentDao = BOL_DocumentDao::getInstance();
        $this->menuItemDao = BOL_MenuItemDao::getInstance();
    }

    /**
     * Saves and updates document items.
     * 
     * @param BOL_Document $document
     */
    public function saveDocument( BOL_Document $document )
    {
        $this->documentDao->save($document);
    }

    /**
     * Saves and updates menu items.
     * 
     * @param BOL_MenuItem $menuItem
     */
    public function saveMenuItem( BOL_MenuItem $menuItem )
    {
        $this->menuItemDao->save($menuItem);
    }

    /**
     * Returns list of all static documents.
     * 
     * @return array<BOL_Document>
     */
    public function findAllStaticDocuments()
    {
        return $this->documentDao->findAllStaticDocuments();
    }

    /**
     * Returns list of all static documents.
     *
     * @return array<BOL_Document>
     */


    /**
     * Checks if static document with provided uri (assigned) exists.
     *
     * @param string $uri
     * @return boolean
     */
    public function staticDocumentExists( $uri )
    {
        return!( $this->findStaticDocument($uri) === null );
    }

    /**
     * Returns static document object for provided uri.
     * 
     * @param string $uri
     * @return BOL_Document
     */
    public function findStaticDocument( $uri )
    {
        return $this->documentDao->findStaticDocument($uri);
    }

    /**
     * Returns list of menu items for provided menu type.
     * 
     * @param $menuType
     * @return array<BOL_MenuItem>
     */
    public function findMenuItems( $menuType )
    {
        return $this->menuItemDao->findMenuItems($menuType);
    }

    /**
     * Returns list of menu items for provided list of menu types.
     *
     * @param array $menuTypes
     * @return array
     */
    public function findMenuItemsForMenuList( $menuTypes )
    {
        $items = $this->menuItemDao->findMenuItemsForMenuTypes($menuTypes);

        $resultArray = array();

        foreach ( $menuTypes as $type )
        {
            $resultArray[$type] = array();
        }

        /* @var $item BOL_MenuItem */
        foreach ( $items as $item )
        {
            $resultArray[$item['type']][] = $item;
        }

        return $resultArray;
    }

    /**
     * Returns static document object (dto) for provided controller class and method.
     *
     * @param string $controller
     * @param string $action
     * @return BOL_Document
     */
    public function findDocumentByDispatchAttrs( $controller, $action )
    {
        return $this->documentDao->findDocumentByDispatchAttrs($controller, $action);
    }

    /**
     * Returns menu item dto for provided id.
     * 
     * @param int $id
     * @return BOL_MenuItem
     */
    public function findMenuItemById( $id )
    {
        return $this->menuItemDao->findById($id);
    }

    public function findDocumentById( $id )
    {
        return $this->documentDao->findById($id);
    }

    public function deleteDocument( $dto )
    {
        return $this->documentDao->delete($dto);
    }

    /**
     * Returns max sort order for menu type.
     * 
     * @param strign $menuType
     * @return integer
     */
    public function findMaxSortOrderForMenuType( $menuType )
    {
        return $this->menuItemDao->findMaxOrderForMenuType($menuType);
    }

    /**
     *
     * @return BOL_Document
     */
    public function findDocumentByKey( $key )
    {
        return $this->documentDao->findDocumentByKey($key);
    }

    public function deleteMenuItem( $dto )
    {
        $this->menuItemDao->delete($dto);
    }

    public function findMenuItem( $prefix, $key )
    {
        return $this->menuItemDao->findMenuItem($prefix, $key);
    }

    /**
     *
     * @param <type> $visibleFor
     * @return BOL_MenuItem
     */
    public function findFirstLocal( $visibleFor, $menuType )
    {
        return $this->menuItemDao->findFirstLocal($visibleFor, $menuType);
    }

    public function isDocumentUriUnique( $uri )
    {
        return $this->documentDao->isDocumentUriUnique($uri);
    }

    /**
     * Converts query result array into BASE_MenuItem items array.
     *
     * @param array $items
     */
    public function getMenuItems( array $menuItems )
    {
        $resultArray = array();

        foreach ( $menuItems as $value )
        {
            $visible = (int) $value['visibleFor'];
            $auth = PEEP::getUser()->isAuthenticated();

            if ( $visible === 0 || ( $visible === 1 && $auth ) || ( $visible === 2 && !$auth ) )
            {
                continue;
            }

            if ( !empty($value['externalUrl']) )
            {
                $url = $value['externalUrl'];
            }
            else if ( !empty($value['uri']) )
            {
                $url = PEEP::getRouter()->getBaseUrl() . $value['uri'];
            }
            else if ( !empty($value['routePath']) )
            {
                $url = PEEP::getRouter()->urlForRoute($value['routePath']);
            }
            else if ( !empty($value['class']) && !empty($value['action']) )
            {
                $url = PEEP::getRouter()->urlFor($value['class'], $value['action']);
            }
            else
            {
                $url = '_INVALID_URL_';
            }

            $menuItem = new BASE_MenuItem();
            $menuItem->setKey($value['menu_key']);
            $menuItem->setLabel(PEEP::getLanguage()->text($value['prefix'], $value['menu_key']));
            $menuItem->setOrder($value['order']);
            $menuItem->setUrl($url);
            $menuItem->setNewWindow($value['newWindow']);
            $menuItem->setPrefix($value['prefix']);

            $resultArray[] = $menuItem;
        }

        return $resultArray;
    }

    /**
     * System method. Don't call it.
     *
     * @param BOL_MenuItem $el1
     * @param BOL_MenuItem $el2
     */
    public function sortObjectListByAsc( BASE_MenuItem $el1, BASE_MenuItem $el2 )
    {
        if ( $el1->getOrder() === $el2->getOrder() )
        {
            return 0;
        }

        return $el1->getOrder() > $el2->getOrder() ? 1 : -1;
    }

    /**
     * Finds menu item by document key.
     *
     * @param string $docKey
     * @return BOL_MenuItem
     */
    public function findMenuItemByDocumentKey( $docKey )
    {
        return $this->menuItemDao->findByDocumentKey($docKey);
    }
}