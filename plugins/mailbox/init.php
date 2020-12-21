<?php

$plugin = PEEP::getPluginManager()->getPlugin('mailbox');

$classesToAutoload = array(
    'CreateConversationForm' => $plugin->getRootDir() . 'classes' . DS . 'create_conversation_form.php',
);

PEEP::getAutoloader()->addClassArray($classesToAutoload);

PEEP::getRouter()->addRoute(new PEEP_Route('mailbox_messages_default', 'messages', 'MAILBOX_CTRL_Messages', 'index'));

PEEP::getRouter()->addRoute(new PEEP_Route('mailbox_default', 'mailbox', 'MAILBOX_CTRL_Messages', 'index'));

PEEP::getRouter()->addRoute(new PEEP_Route('mailbox_conversation', 'messages/mail/:convId', 'MAILBOX_CTRL_Messages', 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('mailbox_file_upload', 'mailbox/conversation/:entityId/:formElement', 'MAILBOX_CTRL_Mailbox', 'fileUpload'));
PEEP::getRouter()->addRoute(new PEEP_Route('mailbox_admin_config', 'admin/plugins/mailbox', 'MAILBOX_CTRL_Admin', 'index'));

PEEP::getRouter()->addRoute(new PEEP_Route('mailbox_chat_conversation', 'messages/chat/:userId', 'MAILBOX_CTRL_Messages', 'chatConversation'));
PEEP::getRouter()->addRoute(new PEEP_Route('mailbox_mail_conversation', 'messages/mail/:convId', 'MAILBOX_CTRL_Messages', 'index'));

PEEP::getRouter()->addRoute(new PEEP_Route('mailbox_user_list', 'mailbox/users', 'MAILBOX_CTRL_Mailbox', 'users'));
PEEP::getRouter()->addRoute(new PEEP_Route('mailbox_conv_list', 'mailbox/convs', 'MAILBOX_CTRL_Mailbox', 'convs'));

PEEP::getRouter()->addRoute(new PEEP_Route('mailbox_ajax_autocomplete', 'mailbox/ajax/autocomplete', 'MAILBOX_CTRL_Ajax', 'autocomplete'));
PEEP::getRouter()->addRoute(new PEEP_Route('mailbox_compose_mail_conversation', 'messages/compose/:opponentId', 'MAILBOX_MCTRL_Messages', 'composeMailConversation'));


$eventHandler = new MAILBOX_CLASS_EventHandler();
$eventHandler->genericInit();
$eventHandler->init();