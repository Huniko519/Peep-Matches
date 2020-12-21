<?php

PEEP::getConfig()->deleteConfig('mailbox', 'results_per_page');

$sql = "DROP TABLE IF EXISTS `" . PEEP_DB_PREFIX . "mailbox_conversation`";

PEEP::getDbo()->query($sql);

$sql = "DROP TABLE IF EXISTS `" . PEEP_DB_PREFIX . "mailbox_last_message`";

PEEP::getDbo()->query($sql);

$sql = "DROP TABLE IF EXISTS `" . PEEP_DB_PREFIX . "mailbox_message`";

PEEP::getDbo()->query($sql);

BOL_PreferenceService::getInstance()->deletePreference('mailbox_create_conversation_display_capcha');
BOL_PreferenceService::getInstance()->deletePreference('mailbox_create_conversation_stamp');