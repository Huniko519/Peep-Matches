<?php

class BOL_EmailVerifyDao extends PEEP_BaseDao
{

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
     * @var BOL_EmailVerifiedDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_EmailVerifiedDao
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
        return 'BOL_EmailVerify';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_email_verify';
    }

    /**
     * @param string $email
     * @return BOL_EmailVerified
     */
    public function findByEmail( $email, $type )
    {
        if ( $email === null || $type === null )
        {
            return null;
        }

        $example = new PEEP_Example();
        $example->andFieldEqual('email', trim($email));
        $example->andFieldEqual('type', trim($type));

        return $this->findObjectByExample($example);
    }

    public function findByEmailAndUserId( $email, $userId, $type )
    {
        if ( $email === null || $type === null || $userId === null )
        {
            return null;
        }

        $example = new PEEP_Example();
        $example->andFieldEqual('email', trim($email));
        $example->andFieldEqual('userId', (int) $userId);
        $example->andFieldEqual('type', trim($type));

        return $this->findObjectByExample($example);
    }

    /**
     * @param string $hash
     * @return BOL_EmailVerified
     */
    public function findByHash( $hash )
    {
        if ( $hash === null )
        {
            return null;
        }

        $hashlVal = trim($hash);

        $example = new PEEP_Example();
        $example->andFieldEqual('hash', $hashlVal);
        return $this->findObjectByExample($example);
    }

    /**
     * @param array $objects
     */
    public function batchReplace( $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);
    }

    public function deleteByCreatedStamp( $stamp )
    {
        $timeStamp = (int) $stamp;

        $example = new PEEP_Example();
        $example->andFieldLessOrEqual('createStamp', $timeStamp);
        $this->deleteByExample($example);
    }

    public function deleteByUserId( $userId )
    {
//        $timeStamp = (int) $stamp;

        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        $this->deleteByExample($example);
    }
}
?>
