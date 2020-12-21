<?php

class BOL_MassMailingIgnoreUserService
{
    /**
     * @var BOL_MassMailingIgnoreUserDao
     */
    private $massMailingDao;

    /**
     * @var BOL_MassMailingIgnoreUserService
     */
    private static $classInstance;

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->massMailingDao = BOL_MassMailingIgnoreUserDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return BOL_MassMailingIgnoreUserService
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
     * @param int $userId
     * @return BOL_MassMailingIgnoreUser
     */
    public function findByUserId( $userId )
    {
        return $this->massMailingDao->findByUserId($userId);
    }

    /**
     * @param BOL_MassMailingIgnoreUser $object
     */
    public function save( BOL_MassMailingIgnoreUser $object )
    {
        $this->massMailingDao->save($object);
    }
}
?>
