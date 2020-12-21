<?php
class BOL_AttachmentDao extends PEEP_BaseDao
{
    const USER_ID = 'userId';
    const ADD_STAMP = 'addStamp';
    const STATUS = 'status';
    const FILE_NAME = 'fileName';
    const ORIG_FILE_NAME = 'origFileName';
    const SIZE = 'size';
    const BUNDLE = 'bundle';
    const PLUGIN_KEY = 'pluginKey';

    /**
     * Singleton instance.
     *
     * @var BOL_AttachmentDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AttachmentDao
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
        return 'BOL_Attachment';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_attachment';
    }

    /**
     * @param string $timeStamp
     * @return array<BOL_Attachment>
     */
    public function findExpiredInactiveItems( $timeStamp )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::STATUS, 0);
        $example->andFieldLessThan(self::ADD_STAMP, $timeStamp);

        return $this->findListByExample($example);
    }

    /**
     * @param integer $userId
     * @return array<BOL_Attachment>
     */
    public function findByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, $userId);
        $example->andFieldEqual(self::STATUS, 1);

        return $this->findListByExample($example);
    }

    /**
     * @param string $fileName
     * @return BOL_Attachment
     */
    public function findAttachmentByFileName( $fileName )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::FILE_NAME, $fileName);

        return $this->findObjectByExample($example);
    }

    /**
     * @param string $pluginKey
     * @param string $bundle
     * @return array<BOL_Attachment>
     */
    public function findAttahcmentByBundle( $pluginKey, $bundle )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::BUNDLE, $bundle);
        $example->andFieldEqual(self::PLUGIN_KEY, $pluginKey);
        return $this->findListByExample($example);
    }

    /**
     * @param string $pluginKey
     * @return array<BOL_Attachment>
     */
    public function findAttahcmentByPluginKey( $pluginKey )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::PLUGIN_KEY, $pluginKey);
        return $this->findListByExample($example);
    }

    /**
     * @param string $bundle
     * @param int $status
     */
    public function updateStatusByBundle( $pluginKey, $bundle, $status )
    {
        $query = "UPDATE `" . $this->getTableName() . "` SET `" . self::STATUS . "` = :status WHERE `".self::PLUGIN_KEY."` = :pk AND `" . self::BUNDLE . "` = :bundle";
        $this->dbo->query($query, array('status' => $status, 'bundle' => $bundle, 'pk' => $pluginKey));
    }
}
