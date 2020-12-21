<?php

$plugin = PEEP::getPluginManager()->getPlugin('stories');

PEEP::getAutoloader()->addClass('Post', $plugin->getBolDir() . 'dto' . DS . 'post.php');
PEEP::getAutoloader()->addClass('PostDao', $plugin->getBolDir() . 'dao' . DS . 'post_dao.php');
PEEP::getAutoloader()->addClass('PostService', $plugin->getBolDir() . 'service' . DS . 'post_service.php');

PEEP::getRouter()->addRoute(new PEEP_Route('stories-uninstall', 'admin/stories/uninstall', 'STORIES_CTRL_Admin', 'uninstall'));

PEEP::getRouter()->addRoute(new PEEP_Route('post-save-new', 'stories/post/new', "STORIES_CTRL_Save", 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('post-save-edit', 'stories/post/edit/:id', "STORIES_CTRL_Save", 'index'));

PEEP::getRouter()->addRoute(new PEEP_Route('post', 'stories/post/:id', "STORIES_CTRL_View", 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('post-approve', 'stories/post/approve/:id', "STORIES_CTRL_View", 'approve'));

PEEP::getRouter()->addRoute(new PEEP_Route('post-part', 'stories/post/:id/:part', "STORIES_CTRL_View", 'index'));

PEEP::getRouter()->addRoute(new PEEP_Route('user-story', 'stories/user/:user', "STORIES_CTRL_UserStory", 'index'));

PEEP::getRouter()->addRoute(new PEEP_Route('user-post', 'stories/:id', "STORIES_CTRL_View", 'index'));

PEEP::getRouter()->addRoute(new PEEP_Route('stories', 'stories', "STORIES_CTRL_Story", 'index', array('list' => array(PEEP_Route::PARAM_OPTION_HIDDEN_VAR => 'latest'))));
PEEP::getRouter()->addRoute(new PEEP_Route('stories.list', 'stories/list/:list', "STORIES_CTRL_Story", 'index'));

PEEP::getRouter()->addRoute(new PEEP_Route('story-manage-posts', 'stories/my-published-posts/', "STORIES_CTRL_ManagementPost", 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('story-manage-drafts', 'stories/my-drafts/', "STORIES_CTRL_ManagementPost", 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('story-manage-comments', 'stories/my-incoming-comments/', "STORIES_CTRL_ManagementComment", 'index'));

PEEP::getRouter()->addRoute(new PEEP_Route('stories-admin', 'admin/stories', "STORIES_CTRL_Admin", 'index'));

$eventHandler = STORIES_CLASS_EventHandler::getInstance();
$eventHandler->genericInit();
STORIES_CLASS_ContentProvider::getInstance()->init();

PEEP::getEventManager()->bind(BASE_CMP_AddNewContent::EVENT_NAME,     array($eventHandler, 'onCollectAddNewContentItem'));
PEEP::getEventManager()->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME,  array($eventHandler, 'onCollectQuickLinks'));

