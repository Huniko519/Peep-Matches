<?php

class BOL_QuestionConfigDao extends PEEP_BaseDao
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
     * @var BOL_QuestionDataDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionDataDao
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
        return 'BOL_QuestionConfig';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_question_config';
    }

    /**
     * Returns configs list
     *
     * @return array
     */
    public function getConfigListByPresentation( $presentation )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('questionPresentation', trim($presentation));

        return $this->findListByExample($example);
    }

    /**
     * Returns configs list
     *
     * @return array
     */
    public function getAllConfigs()
    {
        $example = new PEEP_Example();
        return $this->findListByExample($example);
    }
}

?>