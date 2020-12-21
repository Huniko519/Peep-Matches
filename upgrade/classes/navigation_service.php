<?php

class UPDATE_NavigationService
{
    const MAIN = PEEP_Navigation::MAIN;
    const BOTTOM = PEEP_Navigation::BOTTOM;

    const VISIBLE_FOR_GUEST = PEEP_Navigation::VISIBLE_FOR_GUEST;
    const VISIBLE_FOR_MEMBER = PEEP_Navigation::VISIBLE_FOR_MEMBER;
    const VISIBLE_FOR_ALL = PEEP_Navigation::VISIBLE_FOR_ALL;

    /**
     * @var PEEP_Navigation
     */
    private $navigation;
    /**
     * @var UPDATE_NavigationService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return UPDATE_NavigationService
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
        $this->navigation = PEEP_Navigation::getInstance();
    }

    /**
     * Adds menu items to global menu system.
     *
     * @param string $menuType
     * @param string $routeName
     * @param string $prefix
     * @param string $key
     * @param string $visibleFor
     */
    public function addMenuItem( $menuType, $routeName, $prefix, $key, $visibleFor = self::VISIBLE_FOR_ALL )
    {
        $this->navigation->addMenuItem($menuType, $routeName, $prefix, $key, $visibleFor);
    }

    /**
     * Deletes menu item.
     *
     * @param string $prefix
     * @param string $key
     */
    public function deleteMenuItem( $prefix, $key )
    {
        $this->navigation->deleteMenuItem($prefix, $key);
    }
}