<?php

final class SEARCHSYS_BOL_Service
{
    /**
     * Constructor.
     */
    private function __construct() { }
    
    /**
     * Singleton instance.
     *
     * @var SEARCHSYS_BOL_Service
     */
    private static $classInstance;

    const LIST_LIMIT = 10;

    /**
     * Returns an instance of class
     *
     * @return SEARCHSYS_BOL_Service
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
     * @param $accountType
     * @param $types
     * @return array
     */
    public function getConfiguredQuestionsForAccountType( $accountType, $types )
    {
        $types = array_keys($types);
        $conf = PEEP::getConfig()->getValue('searchsys', 'questions');
        $confArr = json_decode($conf, true);

        if ( $accountType == BOL_QuestionService::ALL_ACCOUNT_TYPES )
        {
            $questionList = array();
            foreach ( $confArr as $type => $questions )
            {
                if ( in_array($type, $types) )
                {
                    $questionList = array_merge($questionList, $questions);
                }
            }
            
            return $questionList;
        }
        else
        {
            return !empty($confArr[$accountType]) ? $confArr[$accountType] : array();
        }
    }

    /**
     * @param bool $labels
     * @return array
     */
    public function getConfiguredGroupsForSiteSearch( $labels = false )
    {
        $conf = PEEP::getConfig()->getValue('searchsys', 'site_search_groups');
        $confArr = json_decode($conf, true);

        $result = array();
        if ( !$confArr )
        {
            return $result;
        }

        $groups = array();
        foreach ( $confArr as $group => $checked )
        {
            if ( $checked )
            {
                $groups[] = $group;
            }
        }

        $groupsInfo = $this->getSiteSearchGroups();

        $pm = PEEP::getPluginManager();
        foreach ( $groupsInfo as $group => $info )
        {
            if ( !in_array($group, $groups) || $group != 'users' && !$pm->isPluginActive($group) )
            {
                continue;
            }

            if ( $labels )
            {
                $result[$group] = $groupsInfo[$group]['label'];
            }
            else
            {
                $result[] = $group;
            }
        }

        return $result;
    }

    /**
     * @param bool $active
     * @return array|null
     */
    public function getSiteSearchGroups( $active = false )
    {
        $event = new BASE_CLASS_EventCollector('searchsys.collect_group');
        $res = PEEP::getEventManager()->trigger($event);

        $groups = $res->getData();
        $list = array();
        if ( $groups )
        {
            usort($groups, array($this, 'sortGroups'));

            foreach ( $groups as $group )
            {
                if ( $active && !$this->groupIsActive($group['key']) )
                {
                    continue;
                }
                $list[$group['key']] = $group;
            }

            return $list;
        }

        return null;
    }

    /**
     * @param $key
     * @return bool
     */
    public function groupIsActive( $key )
    {
        $configured = $this->getConfiguredGroupsForSiteSearch();

        if ( !$configured )
        {
            return false;
        }

        return in_array($key, $configured);
    }

    /**
     * @param $g1
     * @param $g2
     * @return int
     */
    public function sortGroups( $g1, $g2 )
    {
        if ( empty($g1['priority']) )
        {
            $g1['priority'] = 0;
        }

        if ( empty($g2['priority']) )
        {
            $g2['priority'] = 0;
        }

        if ( $g1['priority'] === $g2['priority'] )
        {
            return 0;
        }

        return $g1['priority'] < $g2['priority'] ? 1 : -1;
    }

    /**
     * @param $key
     * @return null
     */
    public function getGroup( $key )
    {
        $groups = $this->getSiteSearchGroups();

        if ( $groups && array_key_exists($key, $groups) )
        {
            return $groups[$key];
        }

        return null;
    }

    /**
     * @return mixed|null
     */
    public function getDefaultGroup()
    {
        $conf = $this->getConfiguredGroupsForSiteSearch();

        if ( $conf )
        {
            return array_shift($conf);
        }

        return null;
    }

    /**
     * @param $term
     * @param $group
     * @param $offset
     * @param $limit
     * @return null
     */
    public function searchEntriesInGroup( $term, $group, $offset, $limit )
    {
        if ( mb_strlen($term) < 2 )
        {
            return null;
        }

        $params = array('key' => $group, 'query' => $term, 'offset' => $offset, 'limit' => $limit);

        $event = new BASE_CLASS_EventCollector('searchsys.search_in_groups', $params);
        $res = PEEP::getEventManager()->trigger($event);

        $data = $res->getData();

        $items = $data ? array_shift($data) : null;

        return count($items[$group]) ? $items[$group] : null;
    }


    /**
     * @param $term
     * @param $limit
     * @return array
     */
    public function searchEntries( $term, $limit )
    {
        $groups = $this->getSiteSearchGroups(true);
        $result = array();
        foreach ( $groups as $g )
        {
            try
            {
                $items = $this->searchEntriesInGroup($term, $g['key'], 0, $limit);
            }
            catch ( Exception $e )
            {
                continue; // do not let the external code break everything
            }

            if ( count($items) )
            {
                foreach ( $items as $item )
                {
                    $item['group'] = $g['label'];
                    $result[$item['id']] = $item;
                }
            }
        }

        return $result;
    }

    /**
     * @param $groupName
     * @param $query
     * @return mixed|null
     */
    public function countEntriesInGroup( $groupName, $query )
    {
        if ( mb_strlen($query) < 2 )
        {
            return null;
        }

        $params = array('key' => $groupName, 'query' => $query);

        try
        {
            $event = new BASE_CLASS_EventCollector('searchsys.count_search_result', $params);
            $res = PEEP::getEventManager()->trigger($event);

            $data = $res->getData();
        }
        catch ( Exception $e )
        {
            return null;
        }

        return $data ? array_shift($data) : null;
    }

    /**
     * @param $keyword
     * @return array
     */
    public function countEntries( $keyword )
    {
        $groups = $this->getSiteSearchGroups();
        $result = array();
        foreach ( $groups as $g )
        {
            $items = $this->countEntriesInGroup($g['key'], $keyword);
            $result[$g['key']] = !empty($items[$g['key']]) ? (int) $items[$g['key']] : 0;
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isPeepsys()
    {
        return PEEP::getPluginManager()->isPluginActive('peepsys');
    }
}