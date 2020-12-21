<?php

class PRIVACY_BOL_ActionDataDao extends PEEP_BaseDao
{
    const ACTION = 'key';
    const USER_ID = 'userId';
    const PLUGIN_KEY = 'plugin_key';
    const VALUE = 'value';

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var PRIVACY_BOL_ActionDataDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PRIVACY_BOL_ActionDataDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @see PEEP_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'PRIVACY_BOL_ActionData';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'privacy_action_data';
    }

    public function findByActionNameList( array $actionNameList, $userId )
    {
        if ( $actionNameList === null || count($actionNameList) === 0 || empty($userId) )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, $userId);
        $example->andFieldInArray(self::ACTION, $actionNameList);
        
        return $this->findListByExample($example);
    }

    public function deleteByActionNamesList( array $actionNameList )
    {
        if ( $actionNameList === null || count($actionNameList) === 0 )
        {
            return;
        }

        $example = new PEEP_Example();
        $example->andFieldInArray(self::ACTION, $actionNameList);
        $this->deleteByExample($example);
        return $this->dbo->getAffectedRows();
    }

    /**
     * Returns action values
     *
     * @return array
     */
    public function findByActionListForUserList( array $actionNameList, $userIdList )
    {
        if ( $actionNameList === null || count($actionNameList) === 0 )
        {
            return array();
        }

        if ( $userIdList === null || count($userIdList) === 0 )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldInArray(self::USER_ID, $userIdList);
        $example->andFieldInArray(self::ACTION, $actionNameList);

        $data = $this->findListByExample($example);

        $result = array();
        foreach ( $data as $object )
        {
            $result[$object->userId][$object->key] = $object;
        }

        return $result;
    }

    public function batchReplace( array $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);
        return $this->dbo->getAffectedRows();
    }

    public function deleteByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        $this->deleteByExample($example);

        return $this->dbo->getAffectedRows();
    }

    public function deleteByPluginKey( $pluginKey )
    {
        if ( empty( $pluginKey ) )
        {
            return false;
        }

        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, $pluginKey);

        $this->deleteByExample($example);

        return $this->dbo->getAffectedRows();
    }
}