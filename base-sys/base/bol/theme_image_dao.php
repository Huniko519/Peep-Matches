<?php

class BOL_ThemeImageDao extends PEEP_BaseDao
{
    const FILENAME = 'filename';

    /**
     * Singleton instance.
     *
     * @var BOL_ThemeImageDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ThemeImageDao
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
    }

    /**
     * @see PEEP_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_ThemeImage';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_theme_image';
    }

    /**
     * @return array<BOL_ThemeImage>
     */
    public function findGraphics()
    {
        $example = new PEEP_Example();
        $example->setOrder('`id` DESC');

        return $this->findListByExample($example);
    }
}
