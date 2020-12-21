<?php

class USERCREDITS_BOL_ActionDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var USERCREDITS_BOL_ActionDao
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
     * @return USERCREDITS_BOL_ActionDao
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
        return 'USERCREDITS_BOL_Action';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'usercredits_action';
    }

    /**
     * Finds action by plugin key and action name
     * 
     * @param string $pluginKey
     * @param string $actionKey
     * @return USERCREDITS_BOL_Action
     */
    public function findAction( $pluginKey, $actionKey )
    {
    	$example = new PEEP_Example();
    	$example->andFieldEqual('pluginKey', $pluginKey);
    	$example->andFieldEqual('actionKey', $actionKey);
    	
    	return $this->findObjectByExample($example);
    }
    
    /**
     * Finds action list by type
     * 
     * @param string $type
     * @param $accTypeId
     * @return array
     */
    public function findList( $type, $accTypeId )
    {
        switch ( $type )
        {
            case 'earn':
                $amountCond = ' AND `ap`.`amount` > 0 ';
                break;

            case 'lose':
                $amountCond = ' AND `ap`.`amount` < 0 ';
                break;
        
            default:
                $amountCond = ' AND `ap`.`amount` = 0 ';
                break;
        }

        $actionPriceDao = USERCREDITS_BOL_ActionPriceDao::getInstance();
        $sql = "SELECT `a`.*, `ap`.`amount`, `ap`.`disabled` FROM `".$this->getTableName()."` AS `a`
            LEFT JOIN `".$actionPriceDao->getTableName()."` AS `ap`
                ON (`a`.`id`=`ap`.`actionId` AND `ap`.`accountTypeId` = :id)
            WHERE `a`.`isHidden` = 0 AND `a`.`active` = 1 " . $amountCond;
        
        return $this->dbo->queryForList($sql, array('id' => $accTypeId));
    }
    
    /**
     * Finds actions by plugin key
     * 
     * @param string $pluginKey
     * @return array
     */
    public function findActionsByPluginKey( $pluginKey )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('pluginKey', $pluginKey);
        
        return $this->findListByExample($example);
    }

    /**
     * @param $keyList
     * @return array
     */
    public function findActionList( $keyList )
    {
        $sql = 'SELECT * FROM `'.$this->getTableName().'` WHERE ';
        
        foreach ( $keyList as $pluginKey => $actionKeys )
        {
            foreach ( $actionKeys as $actionKey )
            {
                $sql .= "`pluginKey`='".$pluginKey."' AND `actionKey`='".$actionKey."' OR ";
            }
        }
        
        $sql = substr($sql, 0, strlen($sql)-3);
        
        return $this->dbo->queryForObjectList($sql, 'USERCREDITS_BOL_Action');
    }
}