<?php

class SEARCHSYS_CLASS_EventHandler
{
    /**
     * Class instance
     *
     * @var SEARCHSYS_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return SEARCHSYS_CLASS_EventHandler
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
     * @param BASE_CLASS_ConsoleItemCollector $event
     */
    public function addConsoleSearchCmp( BASE_CLASS_ConsoleItemCollector $event )
    {
        if ( !PEEP::getUser()->isAuthorized('searchsys', 'site_search') )
        {
            return;
        }

        if ( !PEEP::getConfig()->getValue('searchsys', 'site_search_enabled') )
        {
            return;
        }

        $cmp = new SEARCHSYS_CMP_SiteSearch();

        $event->addItem($cmp, 1000);
    }


    /**
     * @param $found
     * @param $key
     * @return array|null
     */
    private function getAvatarsByFoundItems( $found, $key )
    {
        if ( !$found )
        {
            return null;
        }

        $userIdList = array();
        foreach ( $found as $item )
        {
            if ( !in_array($item[$key], $userIdList) )
            {
                array_push($userIdList, $item[$key]);
            }
        }

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList);

        return $avatars;
    }

    // Users
    public function addUsersSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        $group = array(
            'pluginKey' => 'base',
            'key' => 'users',
            'priority' => 1000,
            'label' => PEEP::getLanguage()->text('base', 'users'),
            'url' => PEEP::getRouter()->urlForRoute('users')
        );

        $ec->add($group);
    }

    public function searchUsersGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'users' )
        {
            $query = PEEP::getDbo()->escapeString('%' . $params['query'] . '%');
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "id", array(
                "method" => "SEARCHSYS_CLASS_EventHandler::searchUsersGroup"
            ));

            $questionName = PEEP::getConfig()->getValue('base', 'display_name_question');
            
            $realnameCond = $questionName == 'realname' ? "OR `name` = 'realname'" : '';
            $questionsSql = "SELECT `name` FROM `". BOL_QuestionDao::getInstance()->getTableName() ."`
                WHERE `type` = 'text' AND (`onSearch` = 1 $realnameCond )
                ORDER BY `sortOrder` ASC";

            $questions = PEEP::getDbo()->queryForColumnList($questionsSql);
            
            switch ( $questionName )
            {
                case 'username':
                    if ( $questions )
                    {
                        $sql = "SELECT `u`.*, `qd`.`textValue` FROM `" . BOL_UserDao::getInstance()->getTableName() . "` AS `u` " .
                            $queryParts["join"] .
                            "LEFT JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` AS `qd`
                            ON (`u`.`id` = `qd`.`userId` AND `qd`.`questionName` IN (" . PEEP::getDbo()->mergeInClause($questions) . ") ) " .
                            " WHERE " . $queryParts["where"] . 
                            " AND ( `u`.`username` LIKE '" . $query . "' collate utf8_general_ci " .
                            " OR `qd`.`textValue` LIKE '" . $query . "' collate utf8_general_ci ) " .
                            " GROUP BY `u`.`id` " .
                            " ORDER BY `u`.`activityStamp` DESC
                            LIMIT :offset, :limit";
                    }
                    else
                    {
                        $sql = "SELECT `u`.* FROM `" . BOL_UserDao::getInstance()->getTableName() . "` AS `u` " .
                            $queryParts["join"] .
                            "LEFT JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` AS `qd`" .
                            " WHERE " . $queryParts["where"] .
                            " AND ( `u`.`username` LIKE '" . $query . "' collate utf8_general_ci ) " .
                            "ORDER BY `u`.`activityStamp` DESC
                            LIMIT :offset, :limit";
                    }
                    
                    break;
                
                case 'realname':
                    $sql = "SELECT `u`.*, `qd`.`textValue` FROM `" . BOL_UserDao::getInstance()->getTableName() . "` AS `u` " .
                        $queryParts["join"] .
                        "LEFT JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` AS `qd`
                            ON (`u`.`id` = `qd`.`userId` AND `qd`.`questionName` IN (" . PEEP::getDbo()->mergeInClause($questions) . ") ) " .
                        " WHERE " . $queryParts["where"] .
                        " AND ( `qd`.`textValue` LIKE '" . $query . "' collate utf8_general_ci ) " .
                        " GROUP BY `u`.`id` " .
                        "ORDER BY `u`.`activityStamp` DESC
                        LIMIT :offset, :limit";
                    
                    break;
            }

            $found = PEEP::getDbo()->queryForList($sql, array('offset' => $offset, 'limit' => $limit));

            $list = array();
            if ( $found )
            {
                $defAvatar = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
                $avatars = $this->getAvatarsByFoundItems($found, 'id');

                foreach ( $found as $item )
                {
                    $data = array(
                        'id' => $key . '_' . $item['id'],
                        'url' => !empty($avatars[$item['id']]) ? $avatars[$item['id']]['url'] : null,
                        'avatar' => !empty($avatars[$item['id']]) ? $avatars[$item['id']] : $defAvatar,
                        'text' => !empty($avatars[$item['id']]) ? $avatars[$item['id']]['title'] : null,
                        'info' => !empty($item['textValue']) ? $item['textValue'] : ''
                    );

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countUsersGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'users' )
        {
            $query = PEEP::getDbo()->escapeString('%' . $params['query'] . '%');

            $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "id", array(
                "method" => "SEARCHSYS_CLASS_EventHandler::searchUsersGroup"
            ));

            $questionName = PEEP::getConfig()->getValue('base', 'display_name_question');

            $realnameCond = $questionName == 'realname' ? "OR `name` = 'realname'" : '';
            $questionsSql = "SELECT `name` FROM `". BOL_QuestionDao::getInstance()->getTableName() ."`
                WHERE `type` = 'text' AND (`onSearch` = 1 $realnameCond )
                ORDER BY `sortOrder` ASC";

            $questions = PEEP::getDbo()->queryForColumnList($questionsSql);

            switch ( $questionName )
            {
                case 'username':
                    if ( $questions )
                    {
                        $sql = "SELECT COUNT(DISTINCT(`u`.`id`)) FROM `" . BOL_UserDao::getInstance()->getTableName() . "` AS `u` " .
                            $queryParts["join"] .
                            "LEFT JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` AS `qd`
                            ON (`u`.`id` = `qd`.`userId` AND `qd`.`questionName` IN (" . PEEP::getDbo()->mergeInClause($questions) . ") ) " .
                            " WHERE " . $queryParts["where"] .
                            " AND ( `u`.`username` LIKE '" . $query . "' collate utf8_general_ci " .
                            " OR `qd`.`textValue` LIKE '" . $query . "' collate utf8_general_ci )";
                    }
                    else
                    {
                        $sql = "SELECT COUNT(DISTINCT(`u`.`id`)) FROM `" . BOL_UserDao::getInstance()->getTableName() . "` AS `u` " .
                            $queryParts["join"] .
                            "LEFT JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` AS `qd`" .
                            " WHERE " . $queryParts["where"] .
                            " AND ( `u`.`username` LIKE '" . $query . "' collate utf8_general_ci ) ";
                    }

                    break;

                case 'realname':
                    $sql = "SELECT COUNT(DISTINCT(`u`.`id`)) FROM `" . BOL_UserDao::getInstance()->getTableName() . "` AS `u` " .
                        $queryParts["join"] .
                        "LEFT JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` AS `qd`
                            ON (`u`.`id` = `qd`.`userId` AND `qd`.`questionName` IN (" . PEEP::getDbo()->mergeInClause($questions) . ") ) " .
                        " WHERE " . $queryParts["where"] .
                        " AND ( `qd`.`textValue` LIKE '" . $query . "' collate utf8_general_ci ) ";

                    break;
            }

            $count = PEEP::getDbo()->queryForColumn($sql, array('query' => $query));
            $result[$key] = $count;

            $ec->add($result);
        }
    }

    // Stories
    public function addStoriesSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        if ( PEEP::getPluginManager()->isPluginActive('stories') )
        {
            $group = array(
                'pluginKey' => 'stories',
                'key' => 'stories',
                'priority' => 90,
                'label' => PEEP::getLanguage()->text('stories', 'auth_group_label'),
                'url' => PEEP::getRouter()->urlForRoute('stories')
            );

            $ec->add($group);
        }
    }

    public function searchStoriesGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'stories' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $storyDao = PostDao::getInstance();
            $sql =
                "SELECT * FROM `" . $storyDao->getTableName() . "`
                WHERE `privacy` = 'everybody' AND `isDraft` = 0 AND (`title` LIKE :query collate utf8_general_ci OR `post` LIKE :query collate utf8_general_ci)
                ORDER BY `timestamp` DESC LIMIT :offset, :limit";
            $found = PEEP::getDbo()->queryForList($sql, array('query' => '%'.$query.'%', 'offset' => $offset, 'limit' => $limit));

            $router = PEEP::getRouter();
            $list = array();
            if ( $found )
            {
                $defAvatar = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
                $avatars = $this->getAvatarsByFoundItems($found, 'authorId');

                foreach ( $found as $item )
                {
                    $data = array(
                        'id' => $key . '_' . $item['id'],
                        'url' => $router->urlForRoute('post', array('id' => $item['id'])),
                        'avatar' => !empty($avatars[$item['authorId']]) ? $avatars[$item['authorId']] : $defAvatar,
                        'text' => $item['title'],
                        'info' => $item['post']
                    );

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countStoriesGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'stories' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];

            $storyDao = PostDao::getInstance();
            $sql =
                "SELECT COUNT(*) FROM `" . $storyDao->getTableName() . "`
                WHERE `privacy` = 'everybody' AND `isDraft` = 0 AND (`title` LIKE :query collate utf8_general_ci OR `post` LIKE :query collate utf8_general_ci)";

            $count = PEEP::getDbo()->queryForColumn($sql, array('query' => '%'.$query.'%'));

            $result[$key] = $count;

            $ec->add($result);
        }
    }

    // Forum
    public function addForumSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        if ( PEEP::getPluginManager()->isPluginActive('forum') )
        {
            $group = array(
                'pluginKey' => 'forum',
                'key' => 'forum',
                'priority' => 80,
                'label' => PEEP::getLanguage()->text('forum', 'auth_group_label'),
                'url' => PEEP::getRouter()->urlForRoute('forum-default')
            );

            $ec->add($group);
        }
    }

    public function searchForumGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'forum' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = PEEP::getDbo()->escapeString('%' . $params['query'] . '%');
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $forumService = FORUM_BOL_ForumService::getInstance();
            $router = PEEP::getRouter();

            if ( PEEP::getUser()->isAuthorized('forum') )
            {
                $excludeGroupIdList = array();
            }
            else
            {
                $excludeGroupIdList = $forumService->getPrivateUnavailableGroupIdList(PEEP::getUser()->getId());
            }
            
            $excludeCond = $excludeGroupIdList ? " AND `t`.`groupId` NOT IN (".PEEP::getDbo()->mergeInClause($excludeGroupIdList).") " : "";

            $sql = "SELECT `p`.*, `t`.`title`
                FROM `".FORUM_BOL_TopicDao::getInstance()->getTableName()."` AS `t`
                INNER JOIN `".FORUM_BOL_PostDao::getInstance()->getTableName()."` AS `p` ON(`p`.`topicId` = `t`.`id`)
                WHERE `t`.`status` = 'approved' $excludeCond
                AND (`t`.`title` LIKE '" . $query . "' collate utf8_general_ci OR `p`.`text` LIKE '" . $query . "' collate utf8_general_ci)
                ORDER BY `p`.`createStamp` DESC
                LIMIT :offset, :limit";

            $found = PEEP::getDbo()->queryForList($sql, array('offset' => $offset, 'limit' => $limit));
            $list = array();
            if ( $found )
            {
                $defAvatar = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
                $avatars = $this->getAvatarsByFoundItems($found, 'userId');

                foreach ( $found as $item )
                {
                    $data = array(
                        'id' => $key . '_' . $item['id'],
                        'url' => $router->urlForRoute('topic-default', array('topicId' => $item['topicId'])),
                        'avatar' => !empty($avatars[$item['userId']]) ? $avatars[$item['userId']] : $defAvatar,
                        'text' => $item['title'],
                        'info' => $item['text']
                    );

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countForumGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'forum' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = PEEP::getDbo()->escapeString('%' . $params['query'] . '%');

            $forumService = FORUM_BOL_ForumService::getInstance();

            if ( PEEP::getUser()->isAuthorized('forum') )
            {
                $excludeGroupIdList = array();
            }
            else
            {
                $excludeGroupIdList = $forumService->getPrivateUnavailableGroupIdList(PEEP::getUser()->getId());
            }
            
            $excludeCond = $excludeGroupIdList ? " AND `t`.`groupId` NOT IN (".PEEP::getDbo()->mergeInClause($excludeGroupIdList).") " : "";

            $sql = "SELECT COUNT(`p`.`id`)
                FROM `".FORUM_BOL_TopicDao::getInstance()->getTableName()."` AS `t`
                INNER JOIN `".FORUM_BOL_PostDao::getInstance()->getTableName()."` AS `p` ON(`p`.`topicId` = `t`.`id`)
                WHERE `t`.`status` = 'approved' $excludeCond
                AND (`t`.`title` LIKE '" . $query . "' collate utf8_general_ci OR `p`.`text` LIKE '" . $query . "' collate utf8_general_ci)";

            $count = PEEP::getDbo()->queryForColumn($sql);

            $result[$key] = $count;

            $ec->add($result);
        }
    }

    // Event
    public function addEventSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        if ( PEEP::getPluginManager()->isPluginActive('event') )
        {
            $group = array(
                'pluginKey' => 'event',
                'key' => 'event',
                'priority' => 60,
                'label' => PEEP::getLanguage()->text('event', 'auth_group_label'),
                'url' => PEEP::getRouter()->urlForRoute('event.main_menu_route')
            );

            $ec->add($group);
        }
    }

    public function searchEventGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'event' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $eventDao = EVENT_BOL_EventDao::getInstance();
            $sql =
                "SELECT * FROM `" . $eventDao->getTableName() . "`
                WHERE `whoCanView` = 1 AND (`title` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)
                ORDER BY `createTimeStamp` DESC LIMIT :offset, :limit";
            $found = PEEP::getDbo()->queryForList($sql, array('query' => '%'.$query.'%', 'offset' => $offset, 'limit' => $limit));

            $router = PEEP::getRouter();
            $eventService = EVENT_BOL_EventService::getInstance();
            $list = array();
            if ( $found )
            {
                foreach ( $found as $item )
                {
                    $avatar = $item['image'] ? $eventService->generateImageUrl($item['image'], true) : $eventService->generateDefaultImageUrl();
                    $data = array(
                        'id' => $key . '_' . $item['id'],
                        'url' => $router->urlForRoute('event.view', array('eventId' => $item['id'])),
                    );
                    $data['avatar'] = array('src' => $avatar, 'url' => $data['url']);
                    $data['text'] = $item['title'];
                    $data['info'] = $item['description'] ? $item['description'] : "";

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countEventGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'event' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];

            $eventDao = EVENT_BOL_EventDao::getInstance();
            $sql =
                "SELECT COUNT(*) FROM `" . $eventDao->getTableName() . "`
                WHERE `whoCanView` = 1 AND (`title` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)";

            $count = PEEP::getDbo()->queryForColumn($sql, array('query' => '%'.$query.'%'));

            $result[$key] = $count;

            $ec->add($result);
        }
    }

    // Groups
    public function addGroupsSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        if ( PEEP::getPluginManager()->isPluginActive('groups') )
        {
            $group = array(
                'pluginKey' => 'groups',
                'key' => 'groups',
                'priority' => 50,
                'label' => PEEP::getLanguage()->text('groups', 'auth_group_label'),
                'url' => PEEP::getRouter()->urlForRoute('groups-index')
            );

            $ec->add($group);
        }
    }

    public function searchGroupsGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'groups' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $groupsDao = GROUPS_BOL_GroupDao::getInstance();
            $sql =
                "SELECT * FROM `" . $groupsDao->getTableName() . "`
                WHERE `whoCanView` = 'anyone' AND (`title` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)
                ORDER BY `timeStamp` DESC LIMIT :offset, :limit";

            $found = PEEP::getDbo()->queryForObjectList(
                $sql,
                $groupsDao->getDtoClassName(),
                array('query' => '%'.$query.'%', 'offset' => $offset, 'limit' => $limit)
            );

            $router = PEEP::getRouter();
            $groupService = GROUPS_BOL_Service::getInstance();
            $list = array();
            if ( $found )
            {
                foreach ( $found as $item )
                {
                    $data = array(
                        'id' => $key . '_' . $item->id,
                        'url' => $router->urlForRoute('groups-view', array('groupId' => $item->id))
                    );
                    $data['avatar'] = array('src' => $groupService->getGroupImageUrl($item), 'url' => $data['url']);
                    $data['text'] = $item->title;
                    $data['info'] = $item->description ? $item->description : "";

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countGroupsGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'groups' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];

            $groupsDao = GROUPS_BOL_GroupDao::getInstance();
            $sql =
                "SELECT COUNT(*) FROM `" . $groupsDao->getTableName() . "`
                WHERE `whoCanView` = 'anyone' AND (`title` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)";

            $count = PEEP::getDbo()->queryForColumn($sql, array('query' => '%'.$query.'%'));

            $result[$key] = $count;

            $ec->add($result);
        }
    }

    // Video
    public function addVideoSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        if ( PEEP::getPluginManager()->isPluginActive('video') )
        {
            $group = array(
                'pluginKey' => 'video',
                'key' => 'video',
                'priority' => 80,
                'label' => PEEP::getLanguage()->text('video', 'auth_group_label'),
                'url' => PEEP::getRouter()->urlForRoute('video_list_index')
            );

            $ec->add($group);
        }
    }

    public function searchVideoGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'video' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $videoDao = VIDEO_BOL_ClipDao::getInstance();
            $sql =
                "SELECT * FROM `" . $videoDao->getTableName() . "`
                WHERE status = 'approved' AND `privacy` = 'everybody' AND (`title` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)
                ORDER BY `addDatetime` DESC LIMIT :offset, :limit";

            $found = PEEP::getDbo()->queryForObjectList(
                $sql,
                $videoDao->getDtoClassName(),
                array('query' => '%'.$query.'%', 'offset' => $offset, 'limit' => $limit)
            );

            $router = PEEP::getRouter();
            $videoService = VIDEO_BOL_ClipService::getInstance();
            $list = array();
            if ( $found )
            {
                foreach ( $found as $item )
                {
                    $data = array(
                        'id' => $key . '_' . $item->id,
                        'url' => $router->urlForRoute('view_clip', array('id' => $item->id))
                    );
                    $thumb = $videoService->getClipThumbUrl($item->id, $item->code, $item->thumbUrl);
                    if ( $thumb == 'undefined' )
                    {
                        $thumb = $videoService->getClipDefaultThumbUrl();
                    }
                    $data['avatar'] = array('src' => $thumb, 'url' => $data['url']);
                    $data['text'] = $item->title;
                    $data['info'] = $item->description ? $item->description : "";

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countVideoGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'video' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];

            $videoDao = VIDEO_BOL_ClipDao::getInstance();
            $sql =
                "SELECT COUNT(*) FROM `" . $videoDao->getTableName() . "`
                WHERE `status` = 'approved' AND `privacy` = 'everybody' AND (`title` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)";

            $count = PEEP::getDbo()->queryForColumn($sql, array('query' => '%'.$query.'%'));

            $result[$key] = $count;

            $ec->add($result);
        }
    }

    // Photo
    public function addPhotoSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        if ( PEEP::getPluginManager()->isPluginActive('photo') )
        {
            $group = array(
                'pluginKey' => 'photo',
                'key' => 'photo',
                'priority' => 70,
                'label' => PEEP::getLanguage()->text('photo', 'auth_group_label'),
                'url' => PEEP::getRouter()->urlForRoute('view_photo_list')
            );

            $ec->add($group);
        }
    }

    public function searchPhotoGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'photo' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $photoDao = PHOTO_BOL_PhotoDao::getInstance();
            $sql =
                "SELECT * FROM `" . $photoDao->getTableName() . "`
                WHERE status = 'approved' AND `privacy` = 'everybody' AND `description` LIKE :query collate utf8_general_ci
                ORDER BY `addDatetime` DESC LIMIT :offset, :limit";

            $found = PEEP::getDbo()->queryForObjectList(
                $sql,
                $photoDao->getDtoClassName(),
                array('query' => '%'.$query.'%', 'offset' => $offset, 'limit' => $limit)
            );

            $router = PEEP::getRouter();
            $photoService = PHOTO_BOL_PhotoService::getInstance();
            $list = array();
            if ( $found )
            {
                foreach ( $found as $item )
                {
                    $data = array();
                    $data['id'] = $key . '_' . $item->id;
                    $data['url'] = $router->urlForRoute('view_photo', array('id' => $item->id));
                    $data['avatar'] = array('src' => $photoService->getPhotoPreviewUrl($item->id, $item->hash), 'url' => $data['url']);
                    $data['text'] = UTIL_String::truncate($item->description, 30);
                    $data['info'] = '';

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countPhotoGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'photo' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];

            $photoDao = PHOTO_BOL_PhotoDao::getInstance();
            $sql =
                "SELECT COUNT(*) FROM `" . $photoDao->getTableName() . "`
                WHERE status = 'approved' AND `privacy` = 'everybody' AND `description` LIKE :query collate utf8_general_ci";

            $count = PEEP::getDbo()->queryForColumn($sql, array('query' => '%'.$query.'%'));

            $result[$key] = $count;

            $ec->add($result);
        }
    }

    // Links
    public function addLinksSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        if ( PEEP::getPluginManager()->isPluginActive('links') )
        {
            $group = array(
                'pluginKey' => 'links',
                'key' => 'links',
                'priority' => 30,
                'label' => PEEP::getLanguage()->text('links', 'auth_group_label'),
                'url' => PEEP::getRouter()->urlForRoute('links')
            );

            $ec->add($group);
        }
    }

    public function searchLinksGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'links' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $linksDao = LinkDao::getInstance();
            $sql =
                "SELECT * FROM `" . $linksDao->getTableName() . "`
                WHERE `privacy` = 'everybody' AND (`title` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)
                ORDER BY `timestamp` DESC LIMIT :offset, :limit";

            $found = PEEP::getDbo()->queryForList($sql, array('query' => '%'.$query.'%', 'offset' => $offset, 'limit' => $limit));

            $router = PEEP::getRouter();
            $list = array();
            if ( $found )
            {
                $defAvatar = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
                $avatars = $this->getAvatarsByFoundItems($found, 'userId');

                foreach ( $found as $item )
                {
                    $data = array();
                    $data['id'] = $key . '_' . $item['id'];
                    $data['url'] = $router->urlForRoute('link', array('id' => $item['id']));
                    $data['avatar'] = !empty($avatars[$item['userId']]) ? $avatars[$item['userId']] : $defAvatar;
                    $data['text'] = $item['title'];
                    $data['info'] = $item['description'] ? $item['description'] : "";

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countLinksGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'links' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];

            $linksDao = LinkDao::getInstance();
            $sql =
                "SELECT COUNT(*) FROM `" . $linksDao->getTableName() . "`
                WHERE `privacy` = 'everybody' AND (`title` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)";

            $count = PEEP::getDbo()->queryForColumn($sql, array('query' => '%'.$query.'%'));

            $result[$key] = $count;

            $ec->add($result);
        }
    }

    
    // ShopPro
    public function addShopProSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        if ( PEEP::getPluginManager()->isPluginActive('shoppro') )
        {
            $group = array(
                'pluginKey' => 'shoppro',
                'key' => 'shoppro',
                'priority' => 50,
                'label' => PEEP::getLanguage()->text('shoppro', 'main_menu_item'),
                'url' => PEEP::getRouter()->urlForRoute('shoppro.index')
            );

            $ec->add($group);
        }
    }

    public function searchShopProGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'shoppro' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $sql = "SELECT * FROM `" . PEEP_DB_PREFIX . "shoppro_products` WHERE `active` = '1'
                AND `name` LIKE :query collate utf8_general_ci
                ORDER BY `date_add` DESC LIMIT :offset, :limit";

            $found = PEEP::getDbo()->queryForList(
                $sql,
                array('query' => '%'.$query.'%', 'offset' => $offset, 'limit' => $limit)
            );

            $list = array();
            $url = PEEP::getPluginManager()->getPlugin('shoppro')->getUserFilesUrl();

            if ( $found )
            {
                foreach ( $found as $item )
                {
                    $data = array();
                    $data['id'] = $key . '_' . $item['id'];
                    $data['url'] = PEEP_URL_HOME . "product/".$item['id']."/zoom/index.html";
                    $data['avatar'] = array('src' => $url . "images/product_".$item['id'].".jpg", 'url' => $data['url']);
                    $data['text'] = $item['name'];
                    $data['info'] = $item['description'] ? UTIL_String::truncate($item['description'], 30) : "";

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countShopProGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'shoppro' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];

            $sql =
                "SELECT COUNT(*) FROM `" . PEEP_DB_PREFIX . "shoppro_products` WHERE `active` = '1'
                AND `name` LIKE :query collate utf8_general_ci";

            $count = PEEP::getDbo()->queryForColumn($sql, array('query' => '%'.$query.'%'));

            $result[$key] = $count;

            $ec->add($result);
        }
    }

    // Events Extended
    public function addEventXSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        if ( PEEP::getPluginManager()->isPluginActive('eventx') )
        {
            $group = array(
                'pluginKey' => 'eventx',
                'key' => 'eventx',
                'priority' => 60,
                'label' => PEEP::getLanguage()->text('eventx', 'auth_group_label'),
                'url' => PEEP::getRouter()->urlForRoute('eventx.main_menu_route')
            );

            $ec->add($group);
        }
    }

    public function searchEventXGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'eventx' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $eventXDao = EVENTX_BOL_EventDao::getInstance();
            $sql =
                "SELECT * FROM `" . $eventXDao->getTableName() . "`
                WHERE `whoCanView` = 1 AND (`title` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)
                ORDER BY `createTimeStamp` DESC LIMIT :offset, :limit";
            $found = PEEP::getDbo()->queryForList($sql, array('query' => '%'.$query.'%', 'offset' => $offset, 'limit' => $limit));

            $router = PEEP::getRouter();
            $eventXService = EVENTX_BOL_EventService::getInstance();
            $list = array();
            if ( $found )
            {
                foreach ( $found as $item )
                {
                    $avatar = $item['image'] ? $eventXService->generateImageUrl($item['image'], true) : $eventXService->generateDefaultImageUrl();
                    $data = array(
                        'id' => $key . '_' . $item['id'],
                        'url' => $router->urlForRoute('eventx.view', array('eventId' => $item['id'])),
                    );
                    $data['avatar'] = array('src' => $avatar, 'url' => $data['url']);
                    $data['text'] = $item['title'];
                    $data['info'] = $item['description'] ? $item['description'] : "";

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countEventXGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'eventx' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];

            $eventXDao = EVENTX_BOL_EventDao::getInstance();
            $sql =
                "SELECT COUNT(*) FROM `" . $eventXDao->getTableName() . "`
                WHERE `whoCanView` = 1 AND (`title` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)";

            $count = PEEP::getDbo()->queryForColumn($sql, array('query' => '%'.$query.'%'));

            $result[$key] = $count;

            $ec->add($result);
        }
    }

    // Games
    public function addGamesSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        if ( PEEP::getPluginManager()->isPluginActive('mochigames') )
        {
            $group = array(
                'pluginKey' => 'mochigames',
                'key' => 'mochigames',
                'priority' => 60,
                'label' => PEEP::getLanguage()->text('mochigames', 'auth_group_label'),
                'url' => PEEP::getRouter()->urlForRoute('mochigames')
            );

            $ec->add($group);
        }
    }

    public function searchGamesGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'mochigames' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $gamesDao = MOCHIGAMES_BOL_MochigamesDao::getInstance();
            $sql =
                "SELECT * FROM `" . $gamesDao->getTableName() . "`
                WHERE `is_enabled` = 1 AND (`name` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)
                ORDER BY `timestamp` DESC LIMIT :offset, :limit";
            $found = PEEP::getDbo()->queryForList($sql, array('query' => '%'.$query.'%', 'offset' => $offset, 'limit' => $limit));
            
            $router = PEEP::getRouter();
            $gamesService = MOCHIGAMES_BOL_Service::getInstance();
            $list = array();
            if ( $found )
            {
                foreach ( $found as $item )
                {
                    $avatar = !empty($item['thumbnail_url']) ? $item['thumbnail_url'] : $gamesService->getDefaultThumbnailUrl();
                    $data = array(
                        'id' => $key . '_' . $item['id'],
                        'url' => $router->urlForRoute('mochigames-game', array('slug' => $item['slug'])),
                    );
                    $data['avatar'] = array('src' => $avatar, 'url' => $data['url']);
                    $data['text'] = $item['name'];
                    $data['info'] = $item['description'] ? $item['description'] : "";

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countGamesGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'mochigames' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];
            
            $gamesDao = MOCHIGAMES_BOL_MochigamesDao::getInstance();
            $sql =
                "SELECT COUNT(*) FROM `" . $gamesDao->getTableName() . "`
                WHERE `is_enabled` = 1 AND (`name` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)";

            $count = PEEP::getDbo()->queryForColumn($sql, array('query' => '%'.$query.'%'));
            
            $result[$key] = $count;

            $ec->add($result);
        }
    }

    // Questions
    public function addQuestionsSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        if ( PEEP::getPluginManager()->isPluginActive('questions') )
        {
            $group = array(
                'pluginKey' => 'questions',
                'key' => 'questions',
                'priority' => 60,
                'label' => PEEP::getLanguage()->text('questions', 'auth_group_label'),
                'url' => PEEP::getRouter()->urlForRoute('questions-index')
            );

            $ec->add($group);
        }
    }

    public function searchQuestionsGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'questions' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $qDao = QUESTIONS_BOL_QuestionDao::getInstance();
            $sql =
                "SELECT * FROM `" . $qDao->getTableName() . "`
                WHERE `text` LIKE :query collate utf8_general_ci
                ORDER BY `timeStamp` DESC LIMIT :offset, :limit";
            $found = PEEP::getDbo()->queryForList($sql, array('query' => '%'.$query.'%', 'offset' => $offset, 'limit' => $limit));

            $router = PEEP::getRouter();
            $list = array();
            if ( $found )
            {
                $defAvatar = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
                $avatars = $this->getAvatarsByFoundItems($found, 'userId');
                
                foreach ( $found as $item )
                {
                    $avatar = !empty($avatars[$item['userId']]) ? $avatars[$item['userId']]['src'] : $defAvatar;
                    $data = array(
                        'id' => $key . '_' . $item['id'],
                        'url' => $router->urlForRoute('questions-question', array('qid' => $item['id'])),
                    );
                    $data['avatar'] = array('src' => $avatar, 'url' => $data['url']);
                    $data['text'] = $item['text'];
                    $data['info'] = '';

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countQuestionsGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'questions' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];

            $qDao = QUESTIONS_BOL_OptionDao::getInstance();
            $sql =
                "SELECT COUNT(*) FROM `" . $qDao->getTableName() . "`
                WHERE `text` LIKE :query collate utf8_general_ci";

            $count = PEEP::getDbo()->queryForColumn($sql, array('query' => '%'.$query.'%'));

            $result[$key] = $count;

            $ec->add($result);
        }
    }

    // Extended Questions
    public function addEQuestionsSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        if ( PEEP::getPluginManager()->isPluginActive('equestions') )
        {
            $group = array(
                'pluginKey' => 'equestions',
                'key' => 'equestions',
                'priority' => 60,
                'label' => PEEP::getLanguage()->text('equestions', 'auth_group_label'),
                'url' => PEEP::getRouter()->urlForRoute('equestions-index')
            );

            $ec->add($group);
        }
    }

    public function searchEQuestionsGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'equestions' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $qDao = EQUESTIONS_BOL_QuestionDao::getInstance();
            $sql =
                "SELECT * FROM `" . $qDao->getTableName() . "`
                WHERE `text` LIKE :query collate utf8_general_ci
                ORDER BY `timeStamp` DESC LIMIT :offset, :limit";
            $found = PEEP::getDbo()->queryForList($sql, array('query' => '%'.$query.'%', 'offset' => $offset, 'limit' => $limit));

            $router = PEEP::getRouter();
            $list = array();
            if ( $found )
            {
                $defAvatar = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
                $avatars = $this->getAvatarsByFoundItems($found, 'userId');

                foreach ( $found as $item )
                {
                    $avatar = !empty($avatars[$item['userId']]) ? $avatars[$item['userId']]['src'] : $defAvatar;
                    $data = array(
                        'id' => $key . '_' . $item['id'],
                        'url' => $router->urlForRoute('equestions-question', array('qid' => $item['id'])),
                    );
                    $data['avatar'] = array('src' => $avatar, 'url' => $data['url']);
                    $data['text'] = $item['text'];
                    $data['info'] = '';

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countEQuestionsGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'equestions' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];

            $qDao = EQUESTIONS_BOL_OptionDao::getInstance();
            $sql =
                "SELECT COUNT(*) FROM `" . $qDao->getTableName() . "`
                WHERE `text` LIKE :query collate utf8_general_ci";

            $count = PEEP::getDbo()->queryForColumn($sql, array('query' => '%'.$query.'%'));

            $result[$key] = $count;

            $ec->add($result);
        }
    }

    // IClassifieds
    public function addIClassifiedsSearchResultGroup( BASE_CLASS_EventCollector $ec )
    {
        if ( PEEP::getPluginManager()->isPluginActive('iclassifieds') )
        {
            $group = array(
                'pluginKey' => 'iclassifieds',
                'key' => 'iclassifieds',
                'priority' => 60,
                'label' => PEEP::getLanguage()->text('iclassifieds', 'auth_group_label'),
                'url' => PEEP::getRouter()->urlForRoute('iclassifieds_view_index')
            );

            $ec->add($group);
        }
    }

    public function searchIClassifiedsGroup( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'iclassifieds' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];
            $offset = (int) $params['offset'];
            $limit = (int) $params['limit'];

            $cDao = ICLASSIFIEDS_BOL_ItemDao::getInstance();
            $sql =
                "SELECT * FROM `" . $cDao->getTableName() . "`
                WHERE status = 'approved' AND privacy = 'everybody' AND (expiry >= NOW() OR expiry = '0000-00-00')
                    AND (`name` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)
                ORDER BY `timestamp` DESC LIMIT :offset, :limit";
            $found = PEEP::getDbo()->queryForList($sql, array('query' => '%'.$query.'%', 'offset' => $offset, 'limit' => $limit));

            $router = PEEP::getRouter();
            $list = array();
            if ( $found )
            {
                foreach ( $found as $item )
                {
                    $avatar = ICLASSIFIEDS_BOL_ItemImageService::getInstance()->getItemMainPreviewImage($item['id']);
                    $data = array(
                        'id' => $key . '_' . $item['id'],
                        'url' => $router->urlForRoute('iclassifieds_view_item', array('id' => $item['id'])),
                    );
                    $data['avatar'] = array('src' => $avatar, 'url' => $data['url']);
                    $data['text'] = $item['name'];
                    $data['info'] = $item['description'] ? UTIL_HtmlTag::stripTags($item['description']) : "";

                    $list[$data['id']] = $data;
                }
            }

            $result[$key] = $list;

            $ec->add($result);
        }
    }

    public function countIClassifiedsGroupResult( BASE_CLASS_EventCollector $ec )
    {
        $params = $ec->getParams();
        $key = $params['key'];

        if ( $key == 'iclassifieds' && PEEP::getPluginManager()->isPluginActive($key) )
        {
            $query = $params['query'];

            $cDao = ICLASSIFIEDS_BOL_ItemDao::getInstance();
            $sql =
                "SELECT COUNT(*) FROM `" . $cDao->getTableName() . "`
                WHERE status = 'approved' AND privacy = 'everybody' AND (expiry >= NOW() OR expiry = '0000-00-00')
                    AND (`name` LIKE :query collate utf8_general_ci OR `description` LIKE :query collate utf8_general_ci)";

            $count = PEEP::getDbo()->queryForColumn($sql, array('query' => '%'.$query.'%'));

            $result[$key] = $count;

            $ec->add($result);
        }
    }

    

    public function init()
    {
        $em = PEEP::getEventManager();

        // add component to console
        $em->bind('console.collect_items', array($this, 'addConsoleSearchCmp'));

        // add searchable groups
        $em->bind('searchsys.collect_group', array($this, 'addUsersSearchResultGroup'));
        $em->bind('searchsys.collect_group', array($this, 'addStoriesSearchResultGroup'));
        $em->bind('searchsys.collect_group', array($this, 'addForumSearchResultGroup'));
        $em->bind('searchsys.collect_group', array($this, 'addEventSearchResultGroup'));
        $em->bind('searchsys.collect_group', array($this, 'addGroupsSearchResultGroup'));
        $em->bind('searchsys.collect_group', array($this, 'addVideoSearchResultGroup'));
        $em->bind('searchsys.collect_group', array($this, 'addPhotoSearchResultGroup'));
        $em->bind('searchsys.collect_group', array($this, 'addLinksSearchResultGroup'));
        $em->bind('searchsys.collect_group', array($this, 'addShopProSearchResultGroup'));
        $em->bind('searchsys.collect_group', array($this, 'addEventXSearchResultGroup'));
        $em->bind('searchsys.collect_group', array($this, 'addGamesSearchResultGroup'));
        $em->bind('searchsys.collect_group', array($this, 'addQuestionsSearchResultGroup'));
        $em->bind('searchsys.collect_group', array($this, 'addEQuestionsSearchResultGroup'));
        $em->bind('searchsys.collect_group', array($this, 'addIClassifiedsSearchResultGroup'));
        

        // perform search in each group
        $em->bind('searchsys.search_in_groups', array($this, 'searchUsersGroup'));
        $em->bind('searchsys.search_in_groups', array($this, 'searchStoriesGroup'));
        $em->bind('searchsys.search_in_groups', array($this, 'searchForumGroup'));
        $em->bind('searchsys.search_in_groups', array($this, 'searchEventGroup'));
        $em->bind('searchsys.search_in_groups', array($this, 'searchGroupsGroup'));
        $em->bind('searchsys.search_in_groups', array($this, 'searchVideoGroup'));
        $em->bind('searchsys.search_in_groups', array($this, 'searchPhotoGroup'));
        $em->bind('searchsys.search_in_groups', array($this, 'searchLinksGroup'));
        $em->bind('searchsys.search_in_groups', array($this, 'searchShopProGroup'));
        $em->bind('searchsys.search_in_groups', array($this, 'searchEventXGroup'));
        $em->bind('searchsys.search_in_groups', array($this, 'searchGamesGroup'));
        $em->bind('searchsys.search_in_groups', array($this, 'searchQuestionsGroup'));
        $em->bind('searchsys.search_in_groups', array($this, 'searchEQuestionsGroup'));
        $em->bind('searchsys.search_in_groups', array($this, 'searchIClassifiedsGroup'));
        

        // count search results
        $em->bind('searchsys.count_search_result', array($this, 'countUsersGroupResult'));
        $em->bind('searchsys.count_search_result', array($this, 'countStoriesGroupResult'));
        $em->bind('searchsys.count_search_result', array($this, 'countForumGroupResult'));
        $em->bind('searchsys.count_search_result', array($this, 'countEventGroupResult'));
        $em->bind('searchsys.count_search_result', array($this, 'countGroupsGroupResult'));
        $em->bind('searchsys.count_search_result', array($this, 'countVideoGroupResult'));
        $em->bind('searchsys.count_search_result', array($this, 'countPhotoGroupResult'));
        $em->bind('searchsys.count_search_result', array($this, 'countLinksGroupResult'));
        $em->bind('searchsys.count_search_result', array($this, 'countShopProGroupResult'));
        $em->bind('searchsys.count_search_result', array($this, 'countEventXGroupResult'));
        $em->bind('searchsys.count_search_result', array($this, 'countGamesGroupResult'));
        $em->bind('searchsys.count_search_result', array($this, 'countQuestionsGroupResult'));
        $em->bind('searchsys.count_search_result', array($this, 'countEQuestionsGroupResult'));
        $em->bind('searchsys.count_search_result', array($this, 'countIClassifiedsGroupResult'));
        
    }
}