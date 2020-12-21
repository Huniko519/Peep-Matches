<?php

class BIRTHDAYS_BOL_UserDao extends PEEP_BaseDao
{
    /**
     * @var BOL_UserDao
     */
    private $userDao;
    /**
     * Singleton instance.
     *
     * @var BOL_UserDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserDao
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
        $this->userDao = BOL_UserDao::getInstance();
    }

    /**
     * @see PEEP_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return $this->userDao->getDtoClassName();
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return $this->userDao->getTableName();
    }

    public function findListByBirthdayPeriod( $start, $end, $first, $count, $idList = null, $privacy = null )
    {
        if ( $idList === array() )
        {
            return array();
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "id", array(
            "method" => "BIRTHDAYS_BOL_UserDao::findListByBirthdayPeriod"
        ));

        $query = "SELECT `u`.* FROM `{$this->getTableName()}` AS `u`
			INNER JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` AS `qd` ON( `u`.`id` = `qd`.`userId` )
            " . $queryParts["join"] . "
            ".( !empty($privacy) ? "LEFT JOIN `" . BIRTHDAYS_BOL_PrivacyDao::getInstance()->getTableName() . "` AS `bp` ON( `u`.`id` = bp.userId AND bp.privacy NOT IN (". $this->dbo->mergeInClause($privacy) ." ) ) " : '' ). "
			WHERE " . $queryParts["where"] . " AND " . ( !empty($privacy) ?" `bp`.id IS NULL AND " : "" ). " `qd`.`questionName` = 'birthdate'
            AND ( DATE_FORMAT(`qd`.`dateValue`, '" . date('Y') . "-%m-%d') BETWEEN :start1 AND :end1 OR DATE_FORMAT(`qd`.`dateValue`, '" . ( intval(date('Y')) + 1 ) . "-%m-%d') BETWEEN :start2 AND :end2 )
            ".( !empty($idList) ? "AND `qd`.`userId` IN ( ".$this->dbo->mergeInClause($idList)." )" : '' )."
			ORDER BY MONTH(`qd`.`dateValue`) " . (date('m') == 12 ? 'DESC' : 'ASC') . " , DAY(`qd`.`dateValue`) ASC
			LIMIT :first, :count";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('start1' => $start, 'start2' => $start, 'end1' => $end, 'end2' => $end, 'first' => $first, 'count' => $count));
    }

    public function countByBirthdayPeriod( $start, $end, $idList = null, $privacy = null )
    {
        if ( $idList === array() )
        {
            return 0;
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("q", "userId", array(
            "method" => "BIRTHDAYS_BOL_UserDao::countByBirthdayPeriod"
        ));

        $query = "SELECT COUNT(*) FROM `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` q
            " . $queryParts["join"] . " 
            ".( !empty($privacy) ? "LEFT JOIN `" . BIRTHDAYS_BOL_PrivacyDao::getInstance()->getTableName() . "` AS `bp` ON( `q`.`userId` = bp.userId AND ( bp.privacy NOT IN (". $this->dbo->mergeInClause($privacy) .") ) ) " : '' ). "
			WHERE " . $queryParts["where"] . " AND ". ( !empty($privacy) ? " `bp`.id IS NULL AND " : "" ). " q.`questionName` = 'birthdate' AND
            ( DATE_FORMAT(q.`dateValue`, '" . date('Y') . "-%m-%d') BETWEEN :start1 AND :end1 OR DATE_FORMAT(q.`dateValue`, '" . ( intval(date('Y')) + 1 ) . "-%m-%d') BETWEEN :start2 AND :end2 )
            " . ( !empty($idList) ? "AND q.`userId` IN (".$this->dbo->mergeInClause($idList).")" : '');

        return $this->dbo->queryForColumn($query, array('start1' => $start, 'start2' => $start, 'end1' => $end, 'end2' => $end));
    }

//    public function findListByBirthdayPeriodAndUserIdList( $start, $end, $first, $count, $idList )
//    {
//        if ( empty($idList) )
//        {
//            return array();
//        }
//
//        $query = " SELECT `u`.* FROM `{$this->getTableName()}` AS `u`
//			INNER JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` AS `qd` ON(`u`.`id` = `qd`.`userId`)
//			WHERE `qd`.`questionName` = 'birthdate' AND DATE_FORMAT(`qd`.`dateValue`, '" . date('Y') . "-%m-%d') BETWEEN :start AND :end
//                AND `u`.`id` IN ({$this->dbo->mergeInClause($idList)})
//			ORDER BY DAY(`qd`.`dateValue`) ASC
//			LIMIT :first, :count";
//
//        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('start' => $start, 'end' => $end, 'first' => $first, 'count' => $count));
//    }

//    public function countByBirthdayPeriodAndUserIdList( $start, $end, $idList )
//    {
//        $query = "SELECT COUNT(*) FROM `" . BOL_QuestionDataDao::getInstance()->getTableName() . "`
//			WHERE `questionName` = 'birthdate' AND DATE_FORMAT(`dateValue`, '" . date('Y') . "-%m-%d') BETWEEN :start AND :end";
//
//        return $this->dbo->queryForColumn($query, array('start' => $start, 'end' => $end));
//    }

    public function findUserListByBirthday( $date )
    {
        $query = "SELECT `u`.`id` FROM `".$this->getTableName()."` AS `u`
            INNER JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` AS `qd` ON(`u`.`id` = `qd`.`userId`)
            WHERE `qd`.`questionName` = 'birthdate' AND DATE_FORMAT(`qd`.`dateValue`, '" . date('Y') . "-%m-%d') = :date";

        return $this->dbo->queryForColumnList($query, array('date' => $date));
    }
}