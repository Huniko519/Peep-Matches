<?php

class STORIES_CMP_UserStoryWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $service = PostService::getInstance();

        if ( empty($params->additionalParamList['entityId']) )
        {
            
        }

        $userId = $params->additionalParamList['entityId'];

        
        if ( $userId != PEEP::getUser()->getId() && !PEEP::getUser()->isAuthorized('stories', 'view') )
        {
            $this->setVisible(false);
            return;
        }
        
        /* Check privacy permissions */
        $eventParams = array(
            'action' => PostService::PRIVACY_ACTION_VIEW_STORY_POSTS,
            'ownerId' => $userId,
            'viewerId' => PEEP::getUser()->getId()
        );

        try
        {
            PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $ex )
        {
            $this->setVisible(false);
            return;
        }
        /* */

        if ( $service->countUserPost($userId) == 0 && !$params->customizeMode )
        {
            $this->setVisible(false);
            return;
        }

        $this->assign('displayname', BOL_UserService::getInstance()->getDisplayName($userId));
        $this->assign('username', BOL_UserService::getInstance()->getUsername($userId));

        $list = array();

        $count = $params->customParamList['count'];

        $userPostList = $service->findUserPostList($userId, 0, $count);

        foreach ( $userPostList as $id => $item )
        {
            /* Check privacy permissions */
            if ( $item->authorId != PEEP::getUser()->getId() && !PEEP::getUser()->isAuthorized('stories') )
            {
                $eventParams = array(
                    'action' => PostService::PRIVACY_ACTION_VIEW_STORY_POSTS,
                    'ownerId' => $item->authorId,
                    'viewerId' => PEEP::getUser()->getId()
                );

                try
                {
                    PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
                }
                catch ( RedirectException $ex )
                {
                    continue;
                }
            }
            /* */

            $list[$id] = $item;
            $list[$id]->setPost(strip_tags($item->getPost()));

            $idList[] = $item->id;
        }

        $commentInfo = array();

        if ( !empty($idList) )
        {
            $commentInfo = BOL_CommentService::getInstance()->findCommentCountForEntityList('story-post', $idList);
            $tb = array();
            foreach ( $list as $key => $item )
            {

                if ( mb_strlen($item->getPost()) > 100 )
                {
                    $list[$key]->setPost(UTIL_String::splitWord(UTIL_String::truncate($item->getPost(), 100, '...')));
                }
                if ( mb_strlen($item->getTitle()) > 50 )
                {
                    $list[$key]->setTitle(UTIL_String::splitWord(UTIL_String::truncate($item->getTitle(), 50, '...')));
                }                
                if ( $commentInfo[$item->getId()] == 0 )
                {
                    $comments_tb_link = array('label' => '', 'href' => '');
                }
                else
                {
                    $comments_tb_link = array(
                        'label' => '<span class="peep_txt_value">' . $commentInfo[$item->getId()] . '</span> ' . PEEP::getLanguage()->text('stories', 'toolbar_comments'),
                        'href' => PEEP::getRouter()->urlForRoute('post', array('id' => $item->getId()))
                    );
                }

                $tb[$item->getId()] = array(
                    $comments_tb_link,
                    array(
                        'label' => UTIL_DateTime::formatDate($item->getTimestamp()),
                        'class' => 'peep_ic_date'
                    )
                );
            }

            $this->assign('tb', $tb);
        }

        $itemList = array();
        foreach($list as $post)
        {
            $itemList[] = array(
                'dto' => $post,
                'titleHref' => PEEP::getRouter()->urlForRoute('user-post', array('id'=>$post->getId()))
            );
        }

        $this->assign('list', $itemList);

        $user = BOL_UserService::getInstance()->findUserById($userId);

        $this->setSettingValue(
            self::SETTING_TOOLBAR, array(
            array(
                'label' => PEEP::getLanguage()->text('stories', 'view_all'),
                'href' => PEEP::getRouter()->urlForRoute('user-story', array('user' => $user->getUsername()))
            )
            )
        );
    }

    public static function getSettingList()
    {
        $settingList = array();

        $options = array();

        for ( $i = 3; $i <= 10; $i++ )
        {
            $options[$i] = $i;
        }

        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => PEEP::getLanguage()->text('stories', 'cmp_widget_post_count'),
            'optionList' => $options,
            'value' => 3,
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('stories', 'story'),
            self::SETTING_ICON => 'peep_ic_write',
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_SHOW_TITLE => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}