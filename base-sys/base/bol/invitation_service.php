<?php

class BOL_InvitationService
{
    /**
     * Class instance
     *
     * @var BOL_InvitationService
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_InvitationService
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var BOL_InvitationDao
     */
    private $invitationDao;

    private function __construct()
    {
        $this->invitationDao = BOL_InvitationDao::getInstance();
    }

    public function findInvitationList( $userId, $beforeStamp, $ignoreIds, $count )
    {
        return $this->invitationDao->findInvitationList($userId, $beforeStamp, $ignoreIds, $count);
    }

    public function findNewInvitationList( $userId, $afterStamp )
    {
        return $this->invitationDao->findNewInvitationList($userId, $afterStamp);
    }

    public function findInvitationListForSend( $userIdList )
    {
        return $this->invitationDao->findInvitationListForSend($userIdList);
    }

    public function findInvitationCount( $userId, $viewed = null, $exclude = null )
    {
        return $this->invitationDao->findInvitationCount($userId, $viewed, $exclude);
    }

    public function findEntityInvitationList( $entityType, $entityId, $offset = 0, $count = null)
    {
        return $this->invitationDao->findEntityInvitationList($entityType, $entityId, $offset, $count);
    }

    public function findEntityInvitationCount( $entityType, $entityId )
    {
        return $this->invitationDao->findEntityInvitationCount($entityType, $entityId);
    }

    public function saveInvitation( BOL_Invitation $invitation )
    {
        $this->invitationDao->saveInvitation($invitation);
    }

    /**
     *
     * @param string $entityType
     * @param int $entityId
     * @param int $userId
     * @return BOL_Invitation
     */
    public function findInvitation( $entityType, $entityId, $userId )
    {
        return $this->invitationDao->findInvitation($entityType, $entityId, $userId);
    }

    public function markViewedByIds( $idList, $viewed = true )
    {
        $this->invitationDao->markViewedByIds($idList, $viewed);
    }

    public function markViewedByUserId( $userId, $viewed = true )
    {
        $this->invitationDao->markViewedByUserId($userId, $viewed);
    }

    public function markSentByIds( $idList, $sent = true )
    {
        $this->invitationDao->markSentByIds($idList, $sent);
    }

    public function deleteInvitation( $entityType, $entityId, $userId )
    {
        $this->invitationDao->deleteInvitation($entityType, $entityId, $userId);
    }

    public function deleteInvitationByEntity( $entityType, $entityId )
    {
        $this->invitationDao->deleteInvitationByEntity($entityType, $entityId);
    }

    public function deleteInvitationByPluginKey( $pluginKey )
    {
        $this->invitationDao->deleteInvitationByPluginKey($pluginKey);
    }

    public function setInvitationStatusByPluginKey( $pluginKey, $status )
    {
        $this->invitationDao->setInvitationStatusByPluginKey($pluginKey, $status);
    }
}