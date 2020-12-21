<?php



PEEP::getRouter()->addRoute(new PEEP_Route('emoticons.admin', 'emoticons/start', 'EMOTICONS_CTRL_Admin', 'index'));

PEEP::getRouter()->addRoute(new PEEP_Route('emoticons.admin_reorder', 'emoticons/changeorder', 'EMOTICONS_CTRL_Admin', 'reorder'));
PEEP::getRouter()->addRoute(new PEEP_Route('emoticons.admin_add', 'emoticons/new-add', 'EMOTICONS_CTRL_Admin', 'add'));
PEEP::getRouter()->addRoute(new PEEP_Route('emoticons.admin_edit', 'emoticons/emoticon-edit', 'EMOTICONS_CTRL_Admin', 'edit'));
PEEP::getRouter()->addRoute(new PEEP_Route('emoticons.admin_delete', 'emoticons/emoticon-delete', 'EMOTICONS_CTRL_Admin', 'delete'));


PEEP::getRouter()->addRoute(new PEEP_Route('emoticons.admin-rsp', 'emoticons/admin-rsp', 'EMOTICONS_CTRL_Admin', 'emoticonsRsp'));
PEEP::getRouter()->addRoute(new PEEP_Route('emoticons.smileLoader', 'emoticons/loader', 'EMOTICONS_CTRL_Emoticons', 'getEmoticonsByCategory'));

EMOTICONS_CLASS_EventHandler::getInstance()->init();


$eventManager = PEEP::getEventManager();

function emoticons_after_master_page_render( PEEP_Event $event )
{
    EMOTICONS_CLASS_HtmlDocument::getInstance()->replaceBaseWysisyg();
    EMOTICONS_CLASS_HtmlDocument::getInstance()->replaceBaseComment();
}
$eventManager->bind( PEEP_EventManager::ON_BEFORE_DOCUMENT_RENDER, 'emoticons_after_master_page_render', 99 );

function emoticons_feed_on_item_render( PEEP_Event $event )
{
    $params = $event->getParams();
    $entityTypes = array('forum-topic', 'user-comment', 'question');

    if ( !in_array($params['action']['entityType'], $entityTypes) )
    {
        return;
    }
    
    $data = $event->getData();
    $language = PEEP::getLanguage();

    switch ( $params['action']['entityType'] )
    {
        case 'forum-topic':
            $service = FORUM_BOL_ForumService::getInstance();
            $postCount = $service->findTopicPostCount( $params['action']['entityId'] ) - 1;

            if ( !$postCount )
            {
                return;
            }

            $event->setData($data);

            $postIds = array();
            foreach ( $params['activity'] as $activity )
            {
                if ( $activity['activityType'] == 'forum-post' )
                {
                    $postIds[] = $activity['data']['postId'];
                }
            }

            if ( empty($postIds) )
            {
                return;
            }

            $postDto = null;
            foreach ( $postIds as $pid )
            {
                $postDto = $service->findPostById( $pid );
                if ( $postDto !== null )
                {
                    break;
                }
            }

            if ( $postDto === null )
            {
                return;
            }

            $postUrl = $service->getPostUrl( $postDto->topicId, $postDto->id );
            
            $content = preg_replace( EMOTICONS_CLASS_HtmlDocument::PATTERN, '', strip_tags(UTIL_String::truncate(str_replace("&nbsp;", '', $postDto->text), 1000, '...'), '<img>') );
            $usersData = BOL_AvatarService::getInstance()->getDataForUserAvatars( array($postDto->userId), true, true, true, false );

            $avatarData = $usersData[$postDto->userId];
            $postUrl = $service->getPostUrl( $postDto->topicId, $postDto->id );

            $ipcContent = PEEP::getThemeManager()->processDecorator( 'mini_ipc', array(
                    'avatar' => $avatarData, 'profileUrl' => $avatarData['url'], 'displayName' => $avatarData['title'], 'content' => $content) );

            $data['assign']['activity'] = array('template' => 'activity', 'vars' => array(
                    'title' => $language->text('forum', 'feed_activity_last_reply', array('postUrl' => $postUrl)),
                    'content' => $ipcContent
                ));
            break;
        case 'user-comment':
        case 'question':
            if ( isset($data['string']['vars']['comment']) )
            {
                $data['string']['vars']['comment'] = preg_replace( '/\[([^\/]+\/[^\/]+)\]/', '<img src="' . PEEP::getPluginManager()->getPlugin('emoticons')->getUserFilesUrl() . 'images/' . '${1}.gif" />', $data['string']['vars']['comment'] );
            }
            else
            {
                $data['string'] = preg_replace( '/\[([^\/]+\/[^\/]+)\]/', '<img src="' . PEEP::getPluginManager()->getPlugin('emoticons')->getUserFilesUrl() . 'images/' . '${1}.gif" />', $data['string'] );
            }
            break;
        case 'user-status':
            PEEP::getDocument()->addScriptDeclarationBeforeIncludes(
                UTIL_JsGenerator::composeJsString('
                    ;window["commentedotir-user-status" + {$actionId}] = {$status}
                    ',
                    array(
                        'actionId' => $params['action']['id'],
                        'status' => UTIL_HtmlTag::autoLink($data['data']['status'])
                    )
                )
            );
            
            if ( isset($data['string']['vars']['comment']) )
            {
                $data['string']['vars']['comment'] = preg_replace( '/\[([^\/]+\/[^\/]+)\]/', '<img src="' . PEEP::getPluginManager()->getPlugin('emoticons')->getUserFilesUrl() . 'images/' . '${1}.gif" />', $data['string']['vars']['comment'] );
            }
            elseif ( isset($data['data']['status']) )
            {
                $data['data']['status'] = preg_replace( '/\[([^\/]+\/[^\/]+)\]/', '<img src="' . PEEP::getPluginManager()->getPlugin('emoticons')->getUserFilesUrl() . 'images/' . '${1}.gif" />', $data['data']['status'] );
            }
            else
            {
                $data['string'] = preg_replace( '/\[([^\/]+\/[^\/]+)\]/', '<img src="' . PEEP::getPluginManager()->getPlugin('emoticons')->getUserFilesUrl() . 'images/' . '${1}.gif" />', $data['string'] );
            }
            break;
    }

    $event->setData($data);
}
PEEP::getEventManager()->bind( 'feed.on_item_render', 'emoticons_feed_on_item_render');


