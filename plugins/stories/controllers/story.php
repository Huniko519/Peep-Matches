<?php

class STORIES_CTRL_Story extends PEEP_ActionController
{

    public function index($params)
    {
        if ( empty($params['list']) )
        {
            $params['list'] = 'latest';
        }

        $plugin = PEEP::getPluginManager()->getPlugin('stories');
        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, 'stories', 'main_menu_item');

        $this->setPageHeading(PEEP::getLanguage()->text('stories', 'list_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_write');

        if ( !PEEP::getUser()->isAdmin() && !PEEP::getUser()->isAuthorized('stories', 'view') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('stories', 'view');
            throw new AuthorizationException($status['msg']);

            return;
        }

        /*
          @var $service PostService
         */
        $service = PostService::getInstance();

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;

        $addNew_promoted = false;
        $addNew_isAuthorized = false;
        if (PEEP::getUser()->isAuthenticated())
        {
            if (PEEP::getUser()->isAuthorized('stories', 'add'))
            {
                $addNew_isAuthorized = true;
            }
            else
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('stories', 'add');
                if ($status['status'] == BOL_AuthorizationService::STATUS_PROMOTED)
                {
                    $addNew_promoted = true;
                    $addNew_isAuthorized = true;
                    $script = '$("#btn-add-new-post").click(function(){
                        PEEP.authorizationLimitedFloatbox('.json_encode($status['msg']).');
                        return false;
                    });';
                    PEEP::getDocument()->addOnloadScript($script);
                }
                else
                {
                    $addNew_isAuthorized = false;
                }
            }
        }

        $this->assign('addNew_isAuthorized', $addNew_isAuthorized);
        $this->assign('addNew_promoted', $addNew_promoted);

        $rpp = (int) PEEP::getConfig()->getValue('stories', 'results_per_page');

        $first = ($page - 1) * $rpp;

        $count = $rpp;

        $case = $params['list'];
        if ( !in_array($case, array( 'latest', 'browse-by-tag', 'most-discussed', 'top-rated' )) )
        {
            throw new Redirect404Exception();
        }
        $showList = true;
        $isBrowseByTagCase = $case == 'browse-by-tag';

        $contentMenu = $this->getContentMenu();
        $contentMenu->getElement($case)->setActive(true);
        $this->addComponent('menu', $contentMenu );
        $this->assign('listType', $case);

        $this->assign('isBrowseByTagCase', $isBrowseByTagCase);

        $tagSearch = new BASE_CMP_TagSearch(PEEP::getRouter()->urlForRoute('stories.list', array('list'=>'browse-by-tag')));

        $this->addComponent('tagSearch', $tagSearch);

        $tagCloud = new BASE_CMP_EntityTagCloud('story-post', PEEP::getRouter()->urlForRoute('stories.list', array('list'=>'browse-by-tag')));

        if ( $isBrowseByTagCase )
        {
            $tagCloud->setTemplate(PEEP::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'big_tag_cloud.html');

            $tag = !(empty($_GET['tag'])) ? UTIL_HtmlTag::stripTags($_GET['tag']) : '';
            $this->assign('tag', $tag );

            if (empty($tag))
            {
                $showList = false;
            }
        }

        $this->addComponent('tagCloud', $tagCloud);


        $this->assign('showList', $showList);

        $list = array();
        $itemsCount = 0;

        list($list, $itemsCount) = $this->getData($case, $first, $count);

        $posts = array();

        $toolbars = array();

        $userService = BOL_UserService::getInstance();

        $authorIdList = array();

        $previewLength = 50;

        foreach ( $list as $item )
        {
            $dto = $item['dto'];

            $dto->setPost($dto->getPost());
            $dto->setTitle( UTIL_String::truncate( strip_tags($dto->getTitle()), 65, '...' )  );

            $text = explode("<!--more-->", UTIL_String::truncate( strip_tags($dto->getpost()), 239, "<!--more-->" ) );

            $isPreview = count($text) > 1;

            if ( !$isPreview )
            {
                $text = explode('<!--page-->', $text[0]);
                $showMore = count($text) > 1;
            }
            else
            {
                $showMore = true;
            }

            $text = $text[0];

            $posts[] = array(
                'dto' => $dto,
                'text' => $text,
                'showMore' => $showMore,
                'url' => PEEP::getRouter()->urlForRoute('user-post', array('id'=>$dto->getId()))
            );

            $authorIdList[] = $dto->authorId;
            $idList[] = $dto->getId();
        }

        if ( !empty($idList) )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($authorIdList, true, false);
            $this->assign('avatars', $avatars);

            $nlist = array();
            foreach ( $avatars as $userId => $avatar )
            {
                $nlist[$userId] = $avatar['title'];
            }
            $urls = BOL_UserService::getInstance()->getUserUrlsForList($authorIdList);
            $this->assign('toolbars', $this->getToolbar($idList, $list, $urls, $nlist));
        }

        $this->assign('list', $posts);
        $this->assign('url_new_post', PEEP::getRouter()->urlForRoute('post-save-new'));

        $paging = new BASE_CMP_Paging($page, ceil($itemsCount / $rpp), 5);

        $this->addComponent('paging', $paging);
    }

    private function getData( $case, $first, $count )
    {
        $service = PostService::getInstance();

        $list = array();
        $itemsCount = 0;

        switch ( $case )
        {
            case 'most-discussed':

                PEEP::getDocument()->setTitle(PEEP::getLanguage()->text('stories', 'most_discussed_title'));
                PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('stories', 'most_discussed_description'));

                $commentService = BOL_CommentService::getInstance();

                $info = array();

                $info = $commentService->findMostCommentedEntityList('story-post', $first, $count);

                $idList = array();

                foreach ( $info as $item )
                {
                    $idList[] = $item['id'];
                }

                if ( empty($idList) )
                {
                    break;
                }

                $dtoList = $service->findListByIdList($idList);

                foreach ( $dtoList as $dto )
                {
                    if ($dto->isDraft())
                    {
                        continue;
                    }
                    $info[$dto->id]['dto'] = $dto;

                    $list[] = array(
                        'dto' => $dto,
                        'commentCount' => $info[$dto->id] ['commentCount'],
                    );
                }

                function sortMostCommented( $e, $e2 )
                {

                    return $e['commentCount'] < $e2['commentCount'];
                }
                usort($list, 'sortMostCommented');

                $itemsCount = $commentService->findCommentedEntityCount('story-post');

                break;

            case 'top-rated':

                PEEP::getDocument()->setTitle(PEEP::getLanguage()->text('stories', 'top_rated_title'));
                PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('stories', 'top_rated_description'));

                $info = array();

                $info = BOL_RateService::getInstance()->findMostRatedEntityList('story-post', $first, $count);

                $idList = array();

                foreach ( $info as $item )
                {
                    $idList[] = $item['id'];
                }

                if ( empty($idList) )
                {
                    break;
                }

                $dtoList = $service->findListByIdList($idList);

                foreach ( $dtoList as $dto )
                {
                    if ($dto->isDraft())
                    {
                        continue;
                    }
                    $list[] = array(
                        'dto' => $dto,
                        'avgScore' => $info[$dto->id] ['avgScore'],
                        'ratesCount' => $info[$dto->id] ['ratesCount']
                    );
                }

                function sortTopRated( $e, $e2 )
                {
                    if ($e['avgScore'] == $e2['avgScore'])
                    {
                        if ($e['ratesCount'] == $e2['ratesCount'])
                        {
                            return 0;
                        }

                        return $e['ratesCount'] < $e2['ratesCount'];
                    }
                    return $e['avgScore'] < $e2['avgScore'];
                }
                usort($list, 'sortTopRated');

                $itemsCount = BOL_RateService::getInstance()->findMostRatedEntityCount('story-post');

                break;

            case 'browse-by-tag':
                if ( empty($_GET['tag']) )
                {
                    $mostPopularTagsArray = BOL_TagService::getInstance()->findMostPopularTags('story-post', 20);
                    $mostPopularTags = "";

                    foreach ( $mostPopularTagsArray as $tag )
                    {
                        $mostPopularTags .= $tag['label'] . ", ";
                    }

                    PEEP::getDocument()->setTitle(PEEP::getLanguage()->text('stories', 'browse_by_tag_title'));
                    PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('stories', 'browse_by_tag_description', array('tags' => $mostPopularTags)));

                    break;
                }

                $info = BOL_TagService::getInstance()->findEntityListByTag('story-post', UTIL_HtmlTag::stripTags($_GET['tag']), $first, $count);

                $itemsCount = BOL_TagService::getInstance()->findEntityCountByTag('story-post', UTIL_HtmlTag::stripTags($_GET['tag']));

                foreach ( $info as $item )
                {
                    $idList[] = $item;
                }

                if ( empty($idList) )
                {
                    break;
                }

                $dtoList = $service->findListByIdList($idList);

                function sortByTimestamp( $post1, $post2 )
                {
                    return $post1->timestamp < $post2->timestamp;
                }
                usort($dtoList, 'sortByTimestamp');


                foreach ( $dtoList as $dto )
                {
                    if ($dto->isDraft())
                    {
                        continue;
                    }
                    $list[] = array('dto' => $dto);
                }

                PEEP::getDocument()->setTitle(PEEP::getLanguage()->text('stories', 'browse_by_tag_item_title', array('tag' => UTIL_HtmlTag::stripTags($_GET['tag']))));
                PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('stories', 'browse_by_tag_item_description', array('tag' => UTIL_HtmlTag::stripTags($_GET['tag']))));

                break;

            case 'latest':
                PEEP::getDocument()->setTitle(PEEP::getLanguage()->text('stories', 'latest_title'));
                PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('stories', 'latest_description'));

                $arr = $service->findList($first, $count);

                foreach ( $arr as $item )
                {
                    $list[] = array('dto' => $item);
                }

                $itemsCount = $service->countPosts();

                break;
        }

        return array($list, $itemsCount);
    }

    /**
     * Get top menu for Story post list
     *
     * @return BASE_CMP_ContentMenu
     */
    private function getContentMenu()
    {
        $menuItems = array();

        $listNames = array(
            'browse-by-tag' => array('iconClass' => 'peep_ic_tag'),
            'most-discussed' => array('iconClass' => 'peep_ic_comment'),
            'top-rated' => array('iconClass' => 'peep_ic_star'),
            'latest' => array('iconClass' => 'peep_ic_clock')
        );

        foreach ( $listNames as $listKey => $listArr )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setKey($listKey);
            $menuItem->setUrl(PEEP::getRouter()->urlForRoute('stories.list', array('list' => $listKey)));
            $menuItemKey = explode('-', $listKey);
            $listKey = "";
            foreach ($menuItemKey as $key)
            {
                $listKey .= strtoupper(substr($key, 0, 1)).substr($key, 1);
            }

            $menuItem->setLabel(PEEP::getLanguage()->text('stories', 'menuItem'.$listKey));
            $menuItem->setIconClass($listArr['iconClass']);
            $menuItems[] = $menuItem;
        }

        return new BASE_CMP_ContentMenu($menuItems);
    }

    private function getToolbar( $idList, $list, $ulist, $nlist )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $info = array();

        $info['comment'] = BOL_CommentService::getInstance()->findCommentCountForEntityList('story-post', $idList);

        $info['rate'] = BOL_RateService::getInstance()->findRateInfoForEntityList('story-post', $idList);

        $info['tag'] = BOL_TagService::getInstance()->findTagListByEntityIdList('story-post', $idList);

        $toolbars = array();

        foreach ( $list as $item )
        {
            $id = $item['dto']->id;

            $userId = $item['dto']->authorId;

            $toolbars[$id] = array(
                array(
                    'class' => 'peep_icon_control peep_ic_user',
                    'label' => !empty($nlist[$userId]) ? $nlist[$userId] : PEEP::getLanguage()->text('base', 'deleted_user'),
                    'href' => !empty($ulist[$userId]) ? $ulist[$userId] : '#'
                ),
                array(
                    'class' => 'peep_ipc_date',
                    'label' => UTIL_DateTime::formatDate($item['dto']->timestamp)
                ),
            );

            if ( $info['rate'][$id]['avg_score'] > 0 )
            {
                $toolbars[$id][] = array(
                    'label' => PEEP::getLanguage()->text('stories', 'rate') . ' <span class="peep_txt_value">' . ( ( $info['rate'][$id]['avg_score'] - intval($info['rate'][$id]['avg_score']) == 0 ) ? intval($info['rate'][$id]['avg_score']) : sprintf('%.2f', $info['rate'][$id]['avg_score']) ) . '</span>',
                );
            }

            if ( !empty($info['comment'][$id]) )
            {
                $toolbars[$id][] = array(
                    'label' => PEEP::getLanguage()->text('stories', 'comments') . ' <span class="peep_txt_value">' . $info['comment'][$id] . '</span>',
                );
            }


            if ( empty($info['tag'][$id]) )
            {
                continue;
            }

            $value = "<span class='peep_wrap_normal'>" . PEEP::getLanguage()->text('stories', 'tags') . ' ';

            foreach ( $info['tag'][$id] as $tag )
            {
                $value .='<a href="' . PEEP::getRouter()->urlForRoute('stories.list', array('list'=>'browse-by-tag')) . "?tag={$tag}" . "\">{$tag}</a>, ";
            }

            $value = mb_substr($value, 0, mb_strlen($value) - 2);
            $value .= "</span>";
            $toolbars[$id][] = array(
                'label' => $value,
            );
        }

        return $toolbars;
    }
}