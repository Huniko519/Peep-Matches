<?php

class SPOTLIGHT_BOL_Service {

    /**
     *
     * @var SPOTLIGHT_BOL_UserDao
     */
    private $userDao;
    /**
     * Class instance
     *
     * @var SPOTLIGHT_BOL_Service
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    protected function __construct() {
        $this->userDao = SPOTLIGHT_BOL_UserDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return SPOTLIGHT_BOL_Service
     */
    public static function getInstance() {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Add a message entries to a database.
     */
    public function addUser($userId)
    {
        $user = new SPOTLIGHT_BOL_User();
        $user->userId = $userId;
        $user->timestamp = time();
        $user->expiration_timestamp = time() + PEEP::getConfig()->getValue('spotlight', 'expiration_time');

        $this->userDao->save($user);
    }

    public function deleteUser($userId)
    {
        return $this->userDao->deleteByUserId($userId);
    }

    public function getUserCount()
    {
        return $this->userDao->countAll();
    }

    public function clearExpiredUsers()
    {
        $userList = $this->userDao->findExpiredUsers();
        
        if (empty($userList))
        {
            return;
        }
        
//        foreach($userList as $user)
//        {
//            //Cnews
//            PEEP::getEventManager()->trigger(new PEEP_Event('feed.delete_item', array(
//                'entityType' => 'add_to_spotlight',
//                'entityId' => $user->userId
//            )));
//        }
        $this->userDao->clearExpiredUsers();
    }

    public function getSpotLight( $start = 0, $count = null )
    {
        return $this->userDao->findSpotLight($start, $count);
    }

    public function findUserById($userId)
    {
        return $this->userDao->findUserById($userId);
    }

}
