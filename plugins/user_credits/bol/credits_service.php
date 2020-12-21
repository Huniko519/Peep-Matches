<?php

final class USERCREDITS_BOL_CreditsService
{
    /**
     * @var USERCREDITS_BOL_BalanceDao
     */
    private $balanceDao;

    /**
     * @var USERCREDITS_BOL_ActionDao
     */
    private $actionDao;

    /**
     * @var USERCREDITS_BOL_ActionPriceDao
     */
    private $actionPriceDao;

    /**
     * @var USERCREDITS_BOL_PackDao
     */
    private $packDao;

    /**
     * @var USERCREDITS_BOL_LogDao
     */
    private $logDao;

    /**
     * Class instance
     *
     * @var USERCREDITS_BOL_CreditsService
     */
    private static $classInstance;

    const ACTION_INTERVAL = 30;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->balanceDao = USERCREDITS_BOL_BalanceDao::getInstance();
        $this->actionDao = USERCREDITS_BOL_ActionDao::getInstance();
        $this->actionPriceDao = USERCREDITS_BOL_ActionPriceDao::getInstance();
        $this->packDao = USERCREDITS_BOL_PackDao::getInstance();
        $this->logDao = USERCREDITS_BOL_LogDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return USERCREDITS_BOL_CreditsService
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Returns user credits balance
     *
     * @param int $userId
     * @return int
     */
    public function getCreditsBalance( $userId )
    {
        if ( !$userId )
        {
            return 0;
        }

        $balance = $this->balanceDao->findByUserId($userId);

        return $balance ? $balance->balance : 0;
    }

    /**
     * Increases user balance
     *
     * @param int $userId
     * @param float $amount
     * @return bool
     */
    public function increaseBalance( $userId, $amount )
    {
        if ( !$userId || !$amount )
        {
            return false;
        }

        $balance = $this->balanceDao->findByUserId($userId);

        if ( $balance )
        {
            $balance->balance += (int) $amount;
        }
        else
        {
            $balance = new USERCREDITS_BOL_Balance();
            $balance->userId = $userId;
            $balance->balance = (int) $amount;
        }

        $this->balanceDao->save($balance);

        return true;
    }

    /**
     * Decreases user balance
     *
     * @param int $userId
     * @param float $amount
     * @return bool
     */
    public function decreaseBalance( $userId, $amount )
    {
        if ( !$userId || !$amount )
        {
            return false;
        }

        $amount = (int) $amount;
        $balance = $this->balanceDao->findByUserId($userId);

        if ( $balance && $balance->balance >= $amount )
        {
            $balance->balance -= (int) $amount;
            $this->balanceDao->save($balance);

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Updates user credits balance
     *
     * @param $userId
     * @param $amount
     * @return bool
     */
    public function setBalance( $userId, $amount )
    {
        if ( !$userId || !$amount )
        {
            return false;
        }

        $balance = $this->balanceDao->findByUserId($userId);

        if ( !$balance )
        {
            $balance = new USERCREDITS_BOL_Balance();
        }

        $balance->userId = $userId;
        $balance->balance = (int) $amount;

        $this->balanceDao->save($balance);

        return true;
    }

    /**
     * Grant credits by one user to another
     *
     * @param $grantorId
     * @param $userId
     * @param $amount
     * @return bool
     */
    public function grantCredits( $grantorId, $userId, $amount )
    {
        if ( !$grantorId || !$userId || !$amount )
        {
            return false;
        }

        $grantorBalance = $this->balanceDao->findByUserId($grantorId);

        if ( !$grantorBalance || $grantorBalance->balance < $amount )
        {
            return false;
        }

        $balance = $this->balanceDao->findByUserId($userId);

        if ( !$balance )
        {
            $balance = new USERCREDITS_BOL_Balance();
            $balance->userId = $userId;
            $balance->balance = 0;
        }

        // increase balance
        $balance->balance = $balance->balance + $amount;
        $this->balanceDao->save($balance);

        //decrease grantor balance
        $grantorBalance->balance = $grantorBalance->balance - $amount;
        $this->balanceDao->save($grantorBalance);

        return true;
    }

    /**
     * Return amount a user can grant to another
     *
     * @param $userId
     * @return int
     */
    public function getGrantableAmountForUser( $userId )
    {
        if ( !$userId )
        {
            return 0;
        }

        $amounts = array(10, 50, 100);
        $balance = $this->getCreditsBalance($userId);
        $portion = $balance * 0.1;

        if ( $portion < $amounts[0] )
        {
            return $amounts[0];
        }

        $closest = null;
        foreach ( $amounts as $item )
        {
            if ( $closest == null || abs($portion - $closest) > abs($item - $portion) )
            {
                $closest = $item;
            }
        }

        return $closest;
    }

    /**
     * @param array $userIdList
     * @return array
     */
    public function getBalanceForUserList( array $userIdList )
    {
        if ( !$userIdList )
        {
            return null;
        }

        $balance = $this->balanceDao->getBalanceForUserList($userIdList);

        $balanceList = array();
        if ( $balance )
        {
            foreach ( $balance as $userBalance )
            {
                $balanceList[$userBalance->userId] = $userBalance->balance;
            }
        }

        return $balanceList;
    }

    /**
     * Checks user balance for sufficient credits to perform action
     *
     * @param string $pluginKey
     * @param string $action
     * @param int $userId
     * @param null $extra
     * @return bool
     */
    public function checkBalance( $pluginKey, $action, $userId, $extra = null )
    {
        if ( !mb_strlen($pluginKey) || !mb_strlen($action) || !$userId )
        {
            return false;
        }

        if ( !$actionDto = $this->findAction($pluginKey, $action) )
        {
            return true;
        }

        $actionPrice = $this->findActionPriceForUser($actionDto->id, $userId);

        if ( !$actionPrice )
        {
            return true;
        }

        if ( $actionPrice->disabled ) // disabled action
        {
            return -1;
        }

        if ( $actionPrice->amount >= 0 ) // free or earning
        {
            return true;
        }

        // layer check
        $params = array('userId' => $userId, 'pluginKey' => $pluginKey, 'action' => $action, 'extra' => $extra);
        $event = new PEEP_Event('usercredits.layer_check', $params);
        PEEP::getEventManager()->trigger($event);
        $layerCheck = $event->getData();

        if ( $layerCheck )
        {
            return true;
        }

        $balance = $this->balanceDao->findByUserId($userId);

        if ( $balance && $balance->balance >= abs($actionPrice->amount) )
        {
            return true;
        }

        return false;
    }

    /**
     * Checks balance of a list of users for sufficient credits to perform action
     *
     * @param string $pluginKey
     * @param string $action
     * @param array $userIdList
     * @return array
     */
    public function checkBalanceForUserList( $pluginKey, $action, array $userIdList )
    {
        if ( !mb_strlen($pluginKey) || !mb_strlen($action) || !$userIdList )
        {
            return array();
        }

        $def = array_fill_keys($userIdList, true);

        if ( !$actionDto = $this->findAction($pluginKey, $action) )
        {
            return $def;
        }

        $price = $this->findActionPriceForAllAccountTypes($actionDto->id);
        $balance = $this->getBalanceForUserList($userIdList);
        $accTypeIds = $this->findAccountTypeIdForUserIdList($userIdList);

        $result = array();
        foreach ( $userIdList as $userId )
        {
            $accTypeId = isset($accTypeIds[$userId]) ? $accTypeIds[$userId] : null;

            /** @var $actionPrice USERCREDITS_BOL_ActionPrice */
            $actionPrice = isset($price[$accTypeId]) ? $price[$accTypeId] : null;

            // layer check
            $params = array('userId' => $userId, 'pluginKey' => $pluginKey, 'action' => $action);
            $event = new PEEP_Event('usercredits.layer_check', $params);
            PEEP::getEventManager()->trigger($event);
            $layerCheck = $event->getData();

            if ( $actionPrice === null )
            {
                $result[$userId] = true;
            }
            else if ( $actionPrice->disabled )
            {
                $result[$userId] = -1;
            }
            else if ( $actionPrice->amount >= 0 )
            {
                $result[$userId] = true;
            }
            else if ( $layerCheck )
            {
                $result[$userId] = true;
            }
            else
            {
                $result[$userId] = !empty($balance[$userId]) && $balance[$userId] >= abs($actionPrice->amount);
            }
        }

        return $result;
    }

    /**
     * @param array $keyList
     * @param $userId
     * @return array
     */
    public function checkBalanceForActionList( array $keyList, $userId )
    {
        if ( !$keyList || !$userId )
        {
            return array();
        }

        $actions = $this->findActionList($keyList);

        $actionList = array();
        if ( $actions )
        {
            foreach ( $actions as $action )
            {
                $actionPrice = $this->findActionPriceForUser($action->id, $userId);
                $actionList[$action->pluginKey][$action->actionKey] = $actionPrice ? $actionPrice->amount : null;
            }
        }

        $balance = $this->balanceDao->findByUserId($userId);

        $result = array();
        foreach ( $keyList as $pluginKey => $actionKeys )
        {
            foreach ( $actionKeys as $actionKey )
            {
                $result[$pluginKey][$actionKey] = !empty($actionList[$pluginKey][$actionKey]) && $balance >= $actionList[$pluginKey][$actionKey];
            }
        }

        return $result;
    }

    /**
     * Tracks action use by a user
     *
     * @param string $pluginKey
     * @param string $action
     * @param int $userId
     * @param bool $checkInterval
     * @param array $extra
     * @return array
     */
    public function trackAction( $pluginKey, $action, $userId, $checkInterval = true, $extra = null )
    {
        $defaults = array('status' => false, 'amount' => null);

        if ( !mb_strlen($pluginKey) || !mb_strlen($action) || !$userId )
        {
            return $defaults;
        }

        if ( !$actionDto = $this->findAction($pluginKey, $action) )
        {
            return $defaults;
        }

        $actionPrice = $this->findActionPriceForUser($actionDto->id, $userId);

        if ( !$actionPrice )
        {
            return $defaults;
        }

        $defaults['amount'] = $actionPrice->amount;

        // layer check
        $params = array('userId' => $userId, 'pluginKey' => $pluginKey, 'action' => $action, 'extra' => $extra);
        $event = new PEEP_Event('usercredits.layer_check', $params);
        PEEP::getEventManager()->trigger($event);
        $layerCheck = $event->getData();

        if ( $layerCheck )
        {
            return $defaults;
        }

        if ( $actionPrice->amount > 0 )
        {
            $lastAction = $this->findLog($userId, $actionDto->id);

            if ( $checkInterval && $lastAction && (time() - $lastAction->logTimestamp < self::ACTION_INTERVAL) )
            {
                return $defaults;
            }

            $defaults['status'] = $this->increaseBalance($userId, abs($actionPrice->amount));
        }
        elseif ( $actionPrice->amount < 0 )
        {
            $defaults['status'] = $this->decreaseBalance($userId, abs($actionPrice->amount));
        }

        if ( $defaults['status'] )
        {
            $this->logAction($actionDto->id, $userId, $actionPrice->amount);
        }

        return $defaults;
    }

    /**
     * Adds new credits action
     *
     * @param USERCREDITS_BOL_Action $action
     * @return bool|int
     */
    public function addCreditsAction( USERCREDITS_BOL_Action $action )
    {
        // check if action already exists
        if ( $actionDto = $this->findAction($action->pluginKey, $action->actionKey) )
        {
            return $actionDto->id;
        }

        $this->actionDao->save($action);

        return $action->id;
    }

    /**
     * Updates credits action
     *
     * @param USERCREDITS_BOL_Action $action
     * @return int
     */
    public function updateCreditsAction( USERCREDITS_BOL_Action $action )
    {
        $this->actionDao->save($action);

        return $action->id;
    }

    /**
     * Collects and stores actions generated by plugins
     *
     * @param array $actions
     * @return bool
     */
    public function collectActions( array $actions )
    {
        $accTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();

        foreach ( $actions as $a )
        {
            $action = $this->findAction($a['pluginKey'], $a['action']);
            if ( $action )
            {
                if ( $action->active == 0 )
                {
                    $action->active = 1;
                    $this->updateCreditsAction($action);
                }

                continue;
            }

            $action = new USERCREDITS_BOL_Action();

            $action->pluginKey = $a['pluginKey'];
            $action->actionKey = $a['action'];
            $action->isHidden = isset($a['hidden']) ? (int) $a['hidden'] : 0;
            $action->settingsRoute = isset($a['settingsRoute']) ? $a['settingsRoute'] : null;
            $action->active = isset($a['active']) ? (int) $a['active'] : 1;

            $actionId = $this->addCreditsAction($action);

            if ( $actionId )
            {
                foreach ( $accTypes as $type )
                {
                    $this->addActionPrice($actionId, $type->id, (int) $a['amount']);
                }
            }
        }

        return true;
    }

    /**
     * @param array $actions
     * @return bool
     */
    public function updateActions( array $actions )
    {
        $accTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();

        foreach ( $actions as $a )
        {
            if ( !$action = $this->findAction($a['pluginKey'], $a['action']) )
            {
                $action = new USERCREDITS_BOL_Action();
            }

            $action->pluginKey = $a['pluginKey'];
            $action->actionKey = $a['action'];
            $action->isHidden = isset($a['hidden']) ? (int) $a['hidden'] : $action->isHidden;
            $action->settingsRoute = isset($a['settingsRoute']) ? $a['settingsRoute'] : $action->settingsRoute;

            $this->actionDao->save($action);

            foreach ( $accTypes as $type )
            {
                $this->addActionPrice($action->id, $type->id, (int) $a['amount'], $a['disabled']);
            }
        }

        return true;
    }

    /**
     * Deletes array of actions
     *
     * @param array $actions
     * @return bool
     */
    public function deleteActions( array $actions )
    {
        foreach ( $actions as $a )
        {
            $action = $this->findAction($a['pluginKey'], $a['action']);

            if ( $action )
            {
                $this->actionDao->deleteById($action->id);
                $this->deleteActionPricesByActionId($action->id);
            }
        }

        return true;
    }

    /**
     * Deletes plugin all actions
     *
     * @param string $pluginKey
     * @return bool
     */
    public function deleteActionsByPluginKey( $pluginKey = null )
    {
        if ( $pluginKey == null )
        {
            $actions = $this->actionDao->findAll();
        }
        else
        {
            $actions = $this->actionDao->findActionsByPluginKey($pluginKey);
        }

        foreach ( $actions as $a )
        {
            $this->actionDao->deleteById($a->id);
            $this->deleteActionPricesByActionId($a->id);
        }

        return true;
    }

    /**
     * @param $pluginKey
     * @return bool
     */
    public function activateActionsByPluginKey( $pluginKey )
    {
        $actions = $this->actionDao->findActionsByPluginKey($pluginKey);

        foreach ( $actions as $a )
        {
            $a->active = 1;
            $this->actionDao->save($a);
        }

        return true;
    }

    /**
     * @param $pluginKey
     * @return bool
     */
    public function deactivateActionsByPluginKey( $pluginKey )
    {
        $actions = $this->actionDao->findActionsByPluginKey($pluginKey);

        foreach ( $actions as $a )
        {
            $a->active = 0;
            $this->actionDao->save($a);
        }

        return true;
    }

    /**
     * Finds credits actions by type
     *
     * @param string $type
     * @param $accTypeId
     * @param bool $forAdmin
     * @return array
     */
    public function findCreditsActions( $type, $accTypeId, $forAdmin = true )
    {
        $questionService = BOL_QuestionService::getInstance();
        if ( !$accTypeId )
        {
            /* @var $def BOL_QuestionAccountType */
            $def = $questionService->getDefaultAccountType();
            $accTypeId = $def->id;
        }

        $list = $this->actionDao->findList($type, $accTypeId);

        $actions = array();
        foreach ( $list as $action )
        {
            if ( !$forAdmin && ($action['disabled'] || !empty($action['settingsRoute'])) )
            {
                continue;
            }

            $action['title'] = $this->getActionTitle($action['pluginKey'], $action['actionKey']);
            $actions[] = $action;
        }

        return $actions;
    }

    public function findAllAddedActions()
    {
        return $this->actionDao->findAll();
    }

    /**
     * Returns action title for multi-language support
     *
     * @param string $pluginKey
     * @param string $actionKey
     * @return string
     */
    public function getActionTitle( $pluginKey, $actionKey, $additionalParams = array() )
    {
        $event = new PEEP_Event("usercredits.get_action_label", array("pluginKey" => $pluginKey, "actionKey" => $actionKey, "additionalParams" => $additionalParams));
        PEEP::getEventManager()->trigger($event);
        $label = $event->getData();

        if ( $label === null )
        {
            $label = PEEP::getLanguage()->text($pluginKey, 'usercredits_action_' . $actionKey);
        }

        return $label;
    }

    /**
     * Finds action by plugin key & action name
     *
     * @param string $pluginKey
     * @param string $actionKey
     * @return USERCREDITS_BOL_Action
     */
    public function findAction( $pluginKey, $actionKey )
    {
        return $this->actionDao->findAction($pluginKey, $actionKey);
    }

    /**
     * @param array $keyList
     * @return array
     */
    public function findActionList( array $keyList )
    {
        return $this->actionDao->findActionList($keyList);
    }

    /**
     * Finds action by Id
     *
     * @param int $actionId
     * @return USERCREDITS_BOL_Action
     */
    public function findActionById( $actionId )
    {
        return $this->actionDao->findById($actionId);
    }

    /**
     * Adds user credits pack
     *
     * @param USERCREDITS_BOL_Pack $pack
     * @return int
     */
    public function addPack( USERCREDITS_BOL_Pack $pack )
    {
        if ( !$pack->accountTypeId )
        {
            /* @var $def BOL_QuestionAccountType */
            $def = BOL_QuestionService::getInstance()->getDefaultAccountType();
            $pack->accountTypeId = $def->id;
        }

        $this->packDao->save($pack);

        return $pack->id;
    }

    /**
     * Get list of packs
     *
     * @param $accountTypeId
     * @return array
     */
    public function getPackList( $accountTypeId )
    {
        if ( !$accountTypeId )
        {
            /* @var $def BOL_QuestionAccountType */
            $def = BOL_QuestionService::getInstance()->getDefaultAccountType();
            $accountTypeId = $def->id;
        }

        $packs = $this->packDao->getAllPacks($accountTypeId);
        $em = PEEP::getEventManager();

        $packList = array();

        foreach ( $packs as $packDto )
        {
            // collect product ID
            $event = new PEEP_Event('usercredits.get_product_id', array('id' => $packDto->id));
            $em->trigger($event);
            $productId = $event->getData();

            $price = floatval($packDto->price);
            $packList[] = array(
                'id' => $packDto->id,
                'credits' => $packDto->credits,
                'price' => $price,
                'title' => $this->getPackTitle($price, $packDto->credits),
                'productId' => $productId
            );
        }

        return $packList;
    }

    public function getAllPackList()
    {
        $packs = $this->packDao->getAllPacks();

        $packList = array();

        foreach ( $packs as $packDto )
        {
            $price = floatval($packDto->price);
            $packList[] = array(
                'id' => $packDto->id,
                'credits' => $packDto->credits,
                'price' => $price,
                'title' => $this->getPackTitle($price, $packDto->credits)
            );
        }

        return $packList;
    }

    /**
     * Returns pack title for multi-language support
     *
     * @param $price
     * @param $credits
     * @return string
     */
    public function getPackTitle( $price, $credits )
    {
        $currency = BOL_BillingService::getInstance()->getActiveCurrency();
        $params = array('price' => floatval($price), 'curr' => $currency, 'credits' => $credits);

        return PEEP::getLanguage()->text('usercredits', 'pack_title', $params);
    }

    /**
     * Deletes pack by Id
     *
     * @param int $id
     * @return bool
     */
    public function deletePackById( $id )
    {
        $this->packDao->deleteById($id);

        return true;
    }

    /**
     * Finds pack by Id
     *
     * @param int $id
     * @return USERCREDITS_BOL_Pack
     */
    public function findPackById( $id )
    {
        return $this->packDao->findById($id);
    }

    /**
     * Checks if packs added
     *
     * @return bool
     */
    public function packSetup()
    {
        return (bool) $this->packDao->countAll();
    }

    /**
     * Logs action use
     *
     * @param int $actionId
     * @param int $userId
     * @param float $amount
     * @return bool
     */
    public function logAction( $actionId, $userId, $amount, $additionalParams = null )
    {
        if ( !$userId )
        {
            return false;
        }

        $log = new USERCREDITS_BOL_Log();
        $log->actionId = $actionId;
        $log->userId = $userId;
        $log->amount = (int) $amount;
        $log->logTimestamp = time();
        $log->additionalParams = $additionalParams;

        $this->logDao->save($log);

        return true;
    }

    /**
     * Finds action log record
     *
     * @param int $userId
     * @param int $actionId
     * @return USERCREDITS_BOL_Log
     */
    public function findLog( $userId, $actionId )
    {
        return $this->logDao->findLast($userId, $actionId);
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function getUserLogHistory( $userId, $page, $limit )
    {
        if ( !$userId )
        {
            return array();
        }

        $log = $this->logDao->findListForUser($userId, $page, $limit);

        $result = array();
        if ( $log )
        {
            foreach ( $log as $entry )
            {
                $additionalParams = !empty($entry['additionalParams']) ? json_decode($entry['additionalParams'], true) : null;
                $entry['action'] = $this->getActionTitle($entry['pluginKey'], $entry['actionKey'], $additionalParams);
                $result[$entry['id']] = $entry;
            }
        }

        return $result;
    }

    /**
     * @param $userId
     * @return int
     */
    public function countUserLogEntries( $userId )
    {
        if ( !$userId )
        {
            return 0;
        }

        return $this->logDao->countEntriesForUser($userId);
    }

    /**
     * @param $actionId
     * @param $accTypeId
     * @param $price
     * @param int $disabled
     */
    public function addActionPrice( $actionId, $accTypeId, $price, $disabled = 0 )
    {
        $actionPrice = $this->actionPriceDao->findActionPrice($actionId, $accTypeId);

        if ( !$actionPrice )
        {
            $actionPrice = new USERCREDITS_BOL_ActionPrice();
            $actionPrice->accountTypeId = $accTypeId;
            $actionPrice->actionId = $actionId;
        }

        $actionPrice->amount = $price;
        $actionPrice->disabled = $disabled;

        $this->actionPriceDao->save($actionPrice);
    }

    /**
     * @param $actionId
     * @param $accTypeId
     * @return USERCREDITS_BOL_ActionPrice
     */
    public function findActionPrice( $actionId, $accTypeId )
    {
        return $this->actionPriceDao->findActionPrice($actionId, $accTypeId);
    }

    /**
     * @param $actionId
     * @param $userId
     * @return \USERCREDITS_BOL_ActionPrice
     */
    public function findActionPriceForUser( $actionId, $userId )
    {
        $accountTypeId = $this->getUserAccountTypeId($userId);

        if ( $accountTypeId )
        {
            return $this->actionPriceDao->findActionPrice($actionId, $accountTypeId);
        }

        return null;
    }

    /**
     * @param $actionId
     * @param $userIdList
     * @return array
     */
    public function findActionPriceForUserList( $actionId, $userIdList )
    {
        if ( !$userIdList )
        {
            return null;
        }

        $users = BOL_UserService::getInstance()->findUserListByIdList($userIdList);
        if ( !$users )
        {
            return null;
        }

        $actionPriceList = $this->findActionPriceForAllAccountTypes($actionId);
        $accTypesList = $this->findAccountTypeIdForUserIdList($userIdList);

        $result = array();
        foreach ( $users as $user )
        {
            $type = isset($accTypesList[$user->id]) ? $accTypesList[$user->id] : null;
            $result[$user->id] = isset($actionPriceList[$type]) ? $actionPriceList[$type] : null;
        }

        return $result;
    }

    /**
     * @param $actionId
     * @return array
     */
    public function findActionPriceForAllAccountTypes( $actionId )
    {
        if ( !$actionId )
        {
            return null;
        }

        $accountTypeIdList = array();
        $types = BOL_QuestionService::getInstance()->findAllAccountTypes();

        foreach ( $types as $accType )
        {
            if ( !in_array($accType->id, $accountTypeIdList) )
            {
                $accountTypeIdList[] = $accType->id;
            }
        }

        $def = array_fill_keys($accountTypeIdList, null);

        $actionPriceList = $this->actionPriceDao->findActionPriceForAccountTypeList($actionId, $accountTypeIdList);
        if ( !$actionPriceList )
        {
            return $def;
        }

        $result = array();
        foreach ( $actionPriceList as $actionPrice )
        {
            $result[$actionPrice->accountTypeId] = $actionPrice;
        }

        return $result;
    }

    /**
     * @param array $userIdList
     * @return array
     */
    public function findAccountTypeIdForUserIdList( array $userIdList )
    {
        if ( !$userIdList )
        {
            return null;
        }

        $users = BOL_UserService::getInstance()->findUserListByIdList($userIdList);

        if ( !$users )
        {
            return null;
        }

        $types = BOL_QuestionService::getInstance()->findAllAccountTypes();

        $result = array();

        /** @var $user BOL_User */
        foreach ( $users as $user )
        {
            foreach ( $types as $type )
            {
                if ( $user->getAccountType() == $type->name )
                {
                    $result[$user->id] = $type->id;
                    break;
                }
            }
        }

        return $result;
    }

    public function findAccountTypes()
    {
        $accTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();
        $lang = PEEP::getLanguage();

        $types = array();
        if ( $accTypes )
        {
            /* @var $type BOL_QuestionAccountType */
            foreach ( $accTypes as $type )
            {
                $types[$type->id] = $lang->text('base', 'questions_account_type_' . $type->name);
            }
        }

        return $types;
    }

    /**
     * @param $userId
     * @return int
     */
    public function getUserAccountTypeId( $userId )
    {
        if ( !$userId )
        {
            return null;
        }

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( !$user )
        {
            return null;
        }

        $accTypeName = $user->getAccountType();
        $accTypeList = BOL_QuestionAccountTypeDao::getInstance()->findAccountTypeByNameList(array($accTypeName));
        $accountTypeId = isset($accTypeList[0]) ? $accTypeList[0]->id : null;

        return $accountTypeId;
    }

    /**
     * @param $accountTypeId
     */
    public function deleteActionPricesByAccountType( $accountTypeId )
    {
        $this->actionPriceDao->deleteByAccountType($accountTypeId);
    }

    /**
     * @param $actionId
     */
    public function deleteActionPricesByActionId( $actionId )
    {
        $this->actionPriceDao->deleteByActionId($actionId);
    }

    /**
     * @param USERCREDITS_BOL_ActionPrice $ap
     */
    public function updateCreditsActionPrice( USERCREDITS_BOL_ActionPrice $ap )
    {
        $this->actionPriceDao->save($ap);
    }

    /**
     * @param $userId
     * @param $amount
     * @param $price
     * @return bool
     */
    public function sendPackPurchasedNotification( $userId, $amount, $price )
    {
        if ( !$userId || !$amount )
        {
            return false;
        }

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( !$user )
        {
            return false;
        }

        $lang = PEEP::getLanguage();

        $email = $user->email;

        $subject = $lang->text('usercredits', 'pack_purchase_notification_subject', array('credits' => $amount));

        $assigns = array(
            'credits' => $amount,
            'price' => floatval($price),
            'currency' => BOL_BillingService::getInstance()->getActiveCurrency()
        );
        $text = $lang->text('usercredits', 'pack_purchase_notification_text', $assigns);
        $html = $lang->text('usercredits', 'pack_purchase_notification_html', $assigns);

        try
        {
            $mail = PEEP::getMailer()->createMail()
                ->addRecipientEmail($email)
                ->setTextContent($text)
                ->setHtmlContent($html)
                ->setSubject($subject);

            PEEP::getMailer()->send($mail);
        }
        catch ( Exception $e )
        {
            return false;
        }

        return true;
    }
    
    public function deleteUserCreditBalanceByUserId( $userId )
    {
        return $this->balanceDao->deleteUserCreditBalanceByUserId($userId);
    }
    
    public function deleteUserCreditLogByUserId($userId)
    {
        return $this->logDao->deleteUserCreditLogByUserId($userId);
    }
}
