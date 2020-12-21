<?php

//remove 'birthdays' widget when plugin deactivated
BOL_ComponentAdminService::getInstance()->deleteWidget('BIRTHDAYS_CMP_BirthdaysWidget');

//remove 'friends birthdays' widget when plugin deactivated
BOL_ComponentAdminService::getInstance()->deleteWidget('BIRTHDAYS_CMP_FriendBirthdaysWidget');

//remove 'My birthday' widget when plugin deactivated
BOL_ComponentAdminService::getInstance()->deleteWidget('BIRTHDAYS_CMP_MyBirthdayWidget');

//remove 'My birthday' widget when plugin deactivated
BOL_ComponentAdminService::getInstance()->deleteWidget('BIRTHDAYS_CMP_CelebrationWidget');