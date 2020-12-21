<?php

class USERCREDITS_BOL_ActionPriceDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var USERCREDITS_BOL_ActionPriceDao
     */
    private static $classInstance;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class.
     *
     * @return USERCREDITS_BOL_ActionPriceDao
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
     * @see PEEP_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'USERCREDITS_BOL_ActionPrice';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'usercredits_action_price';
    }

    /**
     * @param $actionId
     * @param $accTypeId
     * @return mixed
     */
    public function findActionPrice( $actionId, $accTypeId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('actionId', $actionId);
        $example->andFieldEqual('accountTypeId', $accTypeId);

        return $this->findObjectByExample($example);
    }

    /**
     * @param $actionId
     * @param $accTypeIdList
     * @return array
     */
    public function findActionPriceForAccountTypeList( $actionId, $accTypeIdList )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('actionId', $actionId);
        $example->andFieldInArray('accountTypeId', $accTypeIdList);

        return $this->findListByExample($example);
    }

    /**
     * @param $accountTypeId
     */
    public function deleteByAccountType( $accountTypeId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('accountTypeId', $accountTypeId);

        $this->deleteByExample($example);
    }

    /**
     * @param $actionId
     */
    public function deleteByActionId( $actionId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('actionId', $actionId);

        $this->deleteByExample($example);
    }
}