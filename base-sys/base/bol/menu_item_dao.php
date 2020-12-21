<?php

class BOL_MenuItemDao extends PEEP_BaseDao
{
    const PREFIX = 'prefix';
    const KEY = 'key';
    const DOCUMENT_KEY = 'documentKey';
    const TYPE = 'type';
    const ORDER = 'order';
    const ROUTE_PATH = 'routePath';
    const EXTERNAL_URL = 'externalUrl';
    const NEW_WINDOW = 'newWindow';
    const VISIBLE_FOR = 'visibleFor';
    const VALUE_TYPE_MAIN = 'main';
    const VALUE_TYPE_BOTTOM = 'bottom';
    const VALUE_TYPE_HIDDEN = 'hidden';
    const VALUE_TYPE_ADMIN = 'admin';
    const VALUE_TYPE_SETTINGS = 'admin_settings';
    const VALUE_TYPE_PAGES = 'admin_pages';
    const VALUE_TYPE_APPEARANCE = 'admin_appearance';
    const VALUE_TYPE_USERS = 'admin_users';
    const VALUE_TYPE_PLUGINS = 'admin_plugins';
    const VALUE_TYPE_PRIVACY = 'admin_privacy';
    
    
    const VALUE_VISIBLE_FOR_NOBODY = 0;
    const VALUE_VISIBLE_FOR_GUEST = 1;
    const VALUE_VISIBLE_FOR_MEMBER = 2;
    const VALUE_VISIBLE_FOR_ALL = 3;
    const CACHE_TAG_MENU_TYPE_LIST = 'base.menu.menu_type_list';

    /**
     * @var BOL_DocumentDao
     */
    private $documentDao;

    /**
     * Singleton instance.
     *
     * @var BOL_MenuItemDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_MenuItemDao
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
    protected function __construct()
    {
        parent::__construct();
        $this->documentDao = BOL_DocumentDao::getInstance();
    }

    /**
     * @see PEEP_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_MenuItem';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_menu_item';
    }

    /**
     * Returns all active items for provided menu type.
     *
     * @param string $menuType
     * @return array
     */
    public function findMenuItems( $menuType )
    {
        return $this->dbo->queryForList("
			SELECT `mi`.*, `mi`.`key` AS `menu_key`, `d`.`class`, `d`.`action`, `d`.`uri`, `d`.`isStatic`
			FROM `" . $this->getTableName() . "` AS `mi`
			LEFT JOIN `" . $this->documentDao->getTableName() . "` AS `d` ON ( `mi`.`" . self::DOCUMENT_KEY . "` = `d`.`" . BOL_DocumentDao::KEY . "`)
			WHERE `mi`.`" . self::TYPE . "` = :menuType ORDER BY `mi`.`order` ASC", array('menuType' => $menuType), 24 * 3600, array(self::CACHE_TAG_MENU_TYPE_LIST, PEEP_CacheManager::TAG_OPTION_INSTANT_LOAD));
    }

    /**
     * Returns all active items for provided menu types.
     *
     * @param string $menuType
     * @return array
     */
    public function findMenuItemsForMenuTypes( $menuTypes )
    {
        return $this->dbo->queryForList("
			SELECT `mi`.*, `mi`.`key` AS `menu_key`, `d`.`class`, `d`.`action`, `d`.`uri`, `d`.`isStatic`
			FROM `" . $this->getTableName() . "` AS `mi`
			LEFT JOIN `" . $this->documentDao->getTableName() . "` AS `d` ON ( `mi`.`" . self::DOCUMENT_KEY . "` = `d`.`" . BOL_DocumentDao::KEY . "`)
			WHERE `mi`.`" . self::TYPE . "` IN (" . $this->dbo->mergeInClause($menuTypes) . ") ORDER BY `mi`.`order` ASC");
    }

    /**
     * Returns max sort order for menu type.
     * 
     * @param string $menuType
     * @return integer
     */
    public function findMaxOrderForMenuType( $menuType )
    {
        return (int) $this->dbo->queryForColumn("SELECT MAX(`" . self::ORDER . "`) FROM `" . $this->getTableName() . "` WHERE `" . self::TYPE . "` = :menuType", array('menuType' => $menuType));
    }

    /**
     * @param string $menuType
     * @param string $prefix
     * @param string $key
     * @return BOL_MenuItem
     */
    public function findMenuItem( $prefix, $key )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::PREFIX, $prefix);
        $example->andFieldEqual(self::KEY, $key);

        return $this->findObjectByExample($example);
    }

    public function findFirstLocal( $visibleFor, $menuType )
    {

        return $this->dbo->queryForObject("
			SELECT *
			FROM `" . $this->getTableName() . "`
			WHERE `visibleFor` & ? AND `externalUrl` IS NULL AND `type` = ?
			ORDER BY `order` ASC
			LIMIT 1", $this->getDtoClassName(), array($visibleFor, $menuType));
    }

    public function findByDocumentKey( $docKey )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::DOCUMENT_KEY, $docKey);

        return $this->findObjectByExample($example);
    }

    protected function clearCache()
    {
        PEEP::getCacheManager()->clean(array(BOL_MenuItemDao::CACHE_TAG_MENU_TYPE_LIST));
    }
}
