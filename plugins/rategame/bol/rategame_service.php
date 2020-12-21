<?php

final class RATEGAME_BOL_RategameService
{
    /**
     * @var PHOTO_BOL_PhotoDao
     */
    private $photoDao;
    /**
     * Class instance
     *
     * @var RATEGAME_BOL_RategameService
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->photoDao = PHOTO_BOL_PhotoDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return PHOTO_BOL_PhotoService
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getNotRatedPhotoByUserId($userId, $sex)
    {
        $sexCond = "";
        if ($sex != 0)
        {
            $sexCond = " AND `question_data`.`questionName`='sex' AND `question_data`.`intValue`={$sex} ";
        }
        
        $sql = "SELECT * FROM `".PEEP_DB_PREFIX . 'photo'."` AS `photo`
            LEFT JOIN `".PEEP_DB_PREFIX . 'photo_album'."` AS `album`  ON ( `photo`.`albumId`=`album`.`id`)
            LEFT JOIN `".PEEP_DB_PREFIX . 'base_rate'."` AS `rate`  ON ( `photo`.`id`=`rate`.`entityId` AND `rate`.`entityType`='photo_rates')
            LEFT JOIN `".PEEP_DB_PREFIX . "base_question_data` AS `question_data` ON ( `question_data`.`userId` = `album`.`userId`  AND `question_data`.`questionName`='sex' )
            WHERE `album`.`userId`<>{$userId} {$sexCond} AND `photo`.`status`='approved' AND `photo`.`privacy`='everybody' AND ( SELECT COUNT(*) FROM `".PEEP_DB_PREFIX."base_rate` WHERE `userId`={$userId} AND `entityId`=`photo`.`id` AND `entityType`='photo_rates' ) = 0  ORDER BY RAND() LIMIT 1";

        $photoId = PEEP::getDbo()->queryForColumn($sql);
        return $this->photoDao->findById($photoId);
    }
}