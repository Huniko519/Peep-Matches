<?php

class BOL_LogDao extends PEEP_BaseDao
{
    const TYPE = 'type';
    const KEY = 'key';
    const TIME_STAMP = 'timeStamp';
    const MESSAGE = 'message';

    /**
     * Singleton instance.
     *
     * @var BOL_LogDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_LogDao
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
        return 'BOL_Log';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_log';
    }

    /**
     * @param array $entries<BOL_Log>
     */
    public function addEntries( array $entries )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $entries);
    }

    /**
     * @param string $type
     */
    public function deleteByType( $type )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::TYPE, trim($type));

        $this->deleteByExample($example);
    }

    /**
     * @param string $key
     */
    public function deleteByKey( $key )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::KEY, trim($key));

        $this->deleteByExample($example);
    }

    public function findByTypeAndKey( $type, $key )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::TYPE, trim($type));
        $example->andFieldEqual(self::KEY, trim($key));

        return $this->findObjectByExample($example);
    }

    public function findByType( $type )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::TYPE, trim($type));

        return $this->findListByExample($example);
    }
}