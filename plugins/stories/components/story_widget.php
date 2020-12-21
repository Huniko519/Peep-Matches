<?php

class STORIES_CMP_StoryWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $service = PostService::getInstance();

        $count = $params->customParamList['count'];
        $previewLength = $params->customParamList['previewLength'];

        $list = $service->findList(0, $count);

        if ( (empty($list) || (false && !PEEP::getUser()->isAuthorized('stories', 'add') && !PEEP::getUser()->isAuthorized('stories', 'view'))) && !$params->customizeMode )
        {
            $this->setVisible(false);

            return;
        }

        $posts = array();

        $userService = BOL_UserService::getInstance();

        $postIdList = array();
        foreach ( $list as $dto )
        {
            /* @var $dto Post */

            if ( mb_strlen($dto->getTitle()) > 50 )
            {
                $dto->setTitle(UTIL_String::splitWord(UTIL_String::truncate($dto->getTitle(), 50, '...')));
            }
            $text = $service->processPostText($dto->getPost());

            $posts[] = array(
                'dto' => $dto,
                'text' => UTIL_String::splitWord(UTIL_String::truncate($text, $previewLength)),
                'truncated' => ( mb_strlen($text) > $previewLength ),
                'url' => PEEP::getRouter()->urlForRoute('user-post', array('id'=>$dto->getId()))
            );

            $idList[] = $dto->getAuthorId();
            $postIdList[] = $dto->id;
        }

        $commentInfo = array();

        if ( !empty($idList) )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList, true, false);
            $this->assign('avatars', $avatars);

            $urls = BOL_UserService::getInstance()->getUserUrlsForList($idList);

            $commentInfo = BOL_CommentService::getInstance()->findCommentCountForEntityList('story-post', $postIdList);

            $toolbars = array();

            foreach ( $list as $dto )
            {
                $toolbars[$dto->getId()] = array(
                    array(
                        'class' => 'peep_icon_control peep_ic_user',
                        'href' => isset($urls[$dto->getAuthorId()]) ? $urls[$dto->getAuthorId()] : '#',
                        'label' => isset($avatars[$dto->getAuthorId()]['title']) ? $avatars[$dto->getAuthorId()]['title'] : ''
                    ),
                    array(
                        'class' => 'peep_remark peep_ipc_date',
                        'label' => UTIL_DateTime::formatDate($dto->getTimestamp())
                    )
                );
            }
            $this->assign('tbars', $toolbars);
        }

        $this->assign('commentInfo', $commentInfo);
        $this->assign('list', $posts);


        if ( $service->countPosts() > 0 )
        {
            $toolbar = array();

            if ( PEEP::getUser()->isAuthorized('stories', 'add') )
            {
                $toolbar[] = array(
                        
                    );
            }

            if ( PEEP::getUser()->isAuthorized('stories', 'view') )
            {
                $toolbar[] = array(
                    'label' => PEEP::getLanguage()->text('stories', 'go_to_story'),
                    'href' => Peep::getRouter()->urlForRoute('stories')
                    );
            }

            if (!empty($toolbar))
            {
                $this->setSettingValue(self::SETTING_TOOLBAR, $toolbar);
            }

        }
    }

    public static function getSettingList()
    {

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
        $settingList['previewLength'] = array(
            'presentation' => self::PRESENTATION_TEXT,
            'label' => PEEP::getLanguage()->text('stories', 'story_widget_preview_length_lbl'),
            'value' => 100,
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        $list = array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('stories', 'main_menu_item'),
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_ICON => 'peep_ic_write'
        );

        return $list;
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}

