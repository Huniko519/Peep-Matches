--
-- Table structure for table `%%TBL-PREFIX%%base_attachment`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_attachment`;
CREATE TABLE `%%TBL-PREFIX%%base_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `addStamp` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `fileName` varchar(100) DEFAULT NULL,
  `origFileName` varchar(100) DEFAULT NULL,
  `size` int(11) NOT NULL DEFAULT '0',
  `bundle` varchar(128) DEFAULT NULL,
  `pluginKey` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `bundle` (`bundle`),
  KEY `pluginKey` (`pluginKey`),
  KEY `userId_2` (`userId`),
  KEY `bundle_2` (`bundle`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Records of `%%TBL-PREFIX%%base_attachment`
--

LOCK TABLES `%%TBL-PREFIX%%base_attachment` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_authorization_action`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_authorization_action`;
CREATE TABLE `%%TBL-PREFIX%%base_authorization_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `availableForGuest` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupId` (`groupId`,`name`)
) ENGINE=MyISAM AUTO_INCREMENT=172 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_authorization_action`
--

LOCK TABLES `%%TBL-PREFIX%%base_authorization_action` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_authorization_action` VALUES (11,6,'add_comment',0),(67,6,'search_users',1),(171,6,'view_profile',1);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_authorization_group`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_authorization_group`;
CREATE TABLE `%%TBL-PREFIX%%base_authorization_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `moderated` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;


--
-- Records of `%%TBL-PREFIX%%base_authorization_group`
--

LOCK TABLES `%%TBL-PREFIX%%base_authorization_group` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_authorization_group` VALUES (3,'rate',0),(6,'base',1),(7,'admin',1);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_authorization_moderator`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_authorization_moderator`;
CREATE TABLE `%%TBL-PREFIX%%base_authorization_moderator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Records of `%%TBL-PREFIX%%base_authorization_moderator`
--

LOCK TABLES `%%TBL-PREFIX%%base_authorization_moderator` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_authorization_moderator_permission`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_authorization_moderator_permission`;
CREATE TABLE `%%TBL-PREFIX%%base_authorization_moderator_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moderatorId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `moderatorId` (`moderatorId`),
  KEY `groupId` (`groupId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Records of `%%TBL-PREFIX%%base_authorization_moderator_permission`
--

LOCK TABLES `%%TBL-PREFIX%%base_authorization_moderator_permission` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_authorization_permission`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_authorization_permission`;

CREATE TABLE `%%TBL-PREFIX%%base_authorization_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actionId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `actionId` (`actionId`,`roleId`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;


--
-- Records of `%%TBL-PREFIX%%base_authorization_permission`
--

LOCK TABLES `%%TBL-PREFIX%%base_authorization_permission` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_authorization_permission` VALUES (42,11,12),(43,11,27),(44,11,28),(45,11,29),(46,11,30),(50,67,12),(51,67,27),(52,67,28),(53,67,29),(54,67,30),(55,171,1),(56,171,12),(57,171,27),(58,171,28),(59,171,29),(60,171,30);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_authorization_role`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_authorization_role`;
CREATE TABLE `%%TBL-PREFIX%%base_authorization_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `sortOrder` int(11) NOT NULL,
  `displayLabel` tinyint(1) DEFAULT '0',
  `custom` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_authorization_role`
--

LOCK TABLES `%%TBL-PREFIX%%base_authorization_role` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_authorization_role` VALUES (1,'guest',0,0,NULL),(12,'wqewq',1,0,NULL);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_authorization_user_role`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_authorization_user_role`;
CREATE TABLE `%%TBL-PREFIX%%base_authorization_user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user2role` (`userId`,`roleId`),
  KEY `userId` (`userId`),
  KEY `roleId` (`roleId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Records of `%%TBL-PREFIX%%base_authorization_user_role`
--

LOCK TABLES `%%TBL-PREFIX%%base_authorization_user_role` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_avatar`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_avatar`;
CREATE TABLE `%%TBL-PREFIX%%base_avatar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `hash` int(11) NOT NULL DEFAULT '0',
  `status` varchar(32) NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';

--
-- Records of `%%TBL-PREFIX%%base_avatar`
--

LOCK TABLES `%%TBL-PREFIX%%base_avatar` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_billing_gateway`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_billing_gateway`;
CREATE TABLE `%%TBL-PREFIX%%base_billing_gateway` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gatewayKey` varchar(50) NOT NULL,
  `adapterClassName` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `mobile` tinyint(1) NOT NULL DEFAULT '0',
  `recurring` tinyint(1) NOT NULL DEFAULT '0',
  `dynamic` tinyint(1) DEFAULT '1',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `currencies` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gatewayKey` (`gatewayKey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_billing_gateway`
--

LOCK TABLES `%%TBL-PREFIX%%base_billing_gateway` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_billing_gateway_config`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_billing_gateway_config`;
CREATE TABLE `%%TBL-PREFIX%%base_billing_gateway_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `gatewayId` int(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_billing_gateway_config`
--

LOCK TABLES `%%TBL-PREFIX%%base_billing_gateway_config` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_billing_gateway_product`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_billing_gateway_product`;

CREATE TABLE `%%TBL-PREFIX%%base_billing_gateway_product` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `gatewayId` int(10) NOT NULL,
  `pluginKey` varchar(255) NOT NULL,
  `entityType` varchar(50) NOT NULL,
  `entityId` int(10) NOT NULL,
  `productId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Records of `%%TBL-PREFIX%%base_billing_gateway_product`
--

LOCK TABLES `%%TBL-PREFIX%%base_billing_gateway_product` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_billing_product`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_billing_product`;
CREATE TABLE `%%TBL-PREFIX%%base_billing_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productKey` varchar(255) NOT NULL,
  `adapterClassName` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `productKey` (`productKey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_billing_product`
--

LOCK TABLES `%%TBL-PREFIX%%base_billing_product` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_billing_sale`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_billing_sale`;
CREATE TABLE `%%TBL-PREFIX%%base_billing_sale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL,
  `pluginKey` varchar(255) DEFAULT NULL,
  `entityKey` varchar(50) NOT NULL,
  `entityId` int(10) DEFAULT NULL,
  `entityDescription` varchar(255) DEFAULT NULL,
  `gatewayId` int(11) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `transactionUid` varchar(32) DEFAULT NULL,
  `price` float(9,3) NOT NULL,
  `period` int(10) DEFAULT NULL,
  `quantity` int(10) NOT NULL,
  `totalAmount` float(9,3) NOT NULL DEFAULT '0.000',
  `currency` varchar(3) NOT NULL,
  `recurring` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('init','prepared','verified','delivered','processing','error') NOT NULL DEFAULT 'init',
  `timeStamp` int(10) NOT NULL DEFAULT '0',
  `extraData` text,
  PRIMARY KEY (`id`),
  KEY `entityKey` (`entityKey`),
  KEY `entityId` (`entityId`),
  KEY `userId` (`userId`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_billing_sale`
--

LOCK TABLES `%%TBL-PREFIX%%base_billing_sale` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_cache`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_cache`;
CREATE TABLE `%%TBL-PREFIX%%base_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `expireTimestamp` int(11) NOT NULL,
  `instantLoad` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `key_index` (`key`),
  KEY `expire_index` (`expireTimestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_cache`
--

LOCK TABLES `%%TBL-PREFIX%%base_cache` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_cache_tag`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_cache_tag`;
CREATE TABLE `%%TBL-PREFIX%%base_cache_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) NOT NULL,
  `cacheId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tag_index` (`tag`),
  KEY `cacheId_index` (`cacheId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_cache_tag`
--

LOCK TABLES `%%TBL-PREFIX%%base_cache_tag` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_comment`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_comment`;
CREATE TABLE `%%TBL-PREFIX%%base_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `commentEntityId` int(11) NOT NULL,
  `message` text NOT NULL,
  `createStamp` int(11) NOT NULL,
  `attachment` text,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `commentEntityId` (`commentEntityId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_comment`
--

LOCK TABLES `%%TBL-PREFIX%%base_comment` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_comment_entity`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_comment_entity`;
CREATE TABLE `%%TBL-PREFIX%%base_comment_entity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityType` varchar(255) NOT NULL,
  `entityId` int(11) NOT NULL,
  `pluginKey` varchar(100) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `entityType` (`entityType`,`entityId`),
  KEY `pluginKey` (`pluginKey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_comment_entity`
--

LOCK TABLES `%%TBL-PREFIX%%base_comment_entity` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_component`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_component`;
CREATE TABLE `%%TBL-PREFIX%%base_component` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `className` varchar(50) NOT NULL,
  `clonable` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=768 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_component`
--

LOCK TABLES `%%TBL-PREFIX%%base_component` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_component` VALUES (68,'BASE_CMP_UserViewWidget',0),(66,'BASE_CMP_ProfileWallWidget',0),(65,'BASE_CMP_UserAvatarWidget',0),(62,'BASE_CMP_CustomHtmlWidget',1),(786,'BASE_CMP_UserDashWidget',0),(207,'BASE_CMP_MyAvatarWidget',0),(765,'BASE_CMP_ModerationToolsWidget',0),(766,'BASE_CMP_WelcomeWidget',0);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_component_entity_place`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_component_entity_place`;
CREATE TABLE `%%TBL-PREFIX%%base_component_entity_place` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `componentId` int(11) NOT NULL,
  `placeId` int(11) NOT NULL,
  `clone` tinyint(4) NOT NULL DEFAULT '0',
  `entityId` int(11) NOT NULL,
  `uniqName` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`entityId`,`uniqName`),
  KEY `componentId` (`componentId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';

--
-- Records of`%%TBL-PREFIX%%base_component_entity_place`
--

LOCK TABLES `%%TBL-PREFIX%%base_component_entity_place` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_component_entity_position`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_component_entity_position`;
CREATE TABLE `%%TBL-PREFIX%%base_component_entity_position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `componentPlaceUniqName` varchar(50) NOT NULL,
  `section` enum('top','left','bottom','right') NOT NULL,
  `order` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`entityId`,`componentPlaceUniqName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';

--
-- Records of `%%TBL-PREFIX%%base_component_entity_position`
--

LOCK TABLES `%%TBL-PREFIX%%base_component_entity_position` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_component_entity_setting`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_component_entity_setting`;
CREATE TABLE `%%TBL-PREFIX%%base_component_entity_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityId` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `value` longtext NOT NULL,
  `componentPlaceUniqName` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'string',
  PRIMARY KEY (`id`),
  UNIQUE KEY `componentUniqName` (`entityId`,`componentPlaceUniqName`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';

--
-- Records of `%%TBL-PREFIX%%base_component_entity_setting`
--

LOCK TABLES `%%TBL-PREFIX%%base_component_entity_setting` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_component_place`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_component_place`;
CREATE TABLE `%%TBL-PREFIX%%base_component_place` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `componentId` int(11) NOT NULL,
  `placeId` int(11) NOT NULL,
  `clone` tinyint(1) unsigned DEFAULT '0',
  `uniqName` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqName` (`uniqName`),
  KEY `componentId` (`componentId`)
) ENGINE=MyISAM AUTO_INCREMENT=100792 DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';

--
-- Records of `%%TBL-PREFIX%%base_component_place`
--

LOCK TABLES `%%TBL-PREFIX%%base_component_place` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_component_place` VALUES (325,68,3,0,'profile-BASE_CMP_UserViewWidget'),(323,66,3,0,'profile-BASE_CMP_ProfileWallWidget'),(322,65,3,0,'profile-BASE_CMP_UserAvatarWidget'),(318,62,1,0,'dashboard-BASE_CMP_CustomHtmlWidget'),(319,62,3,0,'profile-BASE_CMP_CustomHtmlWidget'),(100811,786,1,0,'dashboard-BASE_CMP_UserDashWidget'),(326,62,2,0,'index-BASE_CMP_CustomHtmlWidget'),(100790,766,1,0,'dashboard-BASE_CMP_WelcomeWidget'),(100152,207,1,0,'dashboard-BASE_CMP_MyAvatarWidget'),(100787,762,5,1,'admin-5295f2e03ec8a'),(100788,762,5,1,'admin-5295f2e40db5c'),(100789,765,1,0,'dashboard-BASE_CMP_ModerationToolsWidget');
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_component_place_cache`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_component_place_cache`;
CREATE TABLE `%%TBL-PREFIX%%base_component_place_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `placeId` int(11) NOT NULL,
  `state` longtext NOT NULL,
  `entityId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userId` (`entityId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';

--
-- Records of `%%TBL-PREFIX%%base_component_place_cache`
--

LOCK TABLES `%%TBL-PREFIX%%base_component_place_cache` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_component_position`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_component_position`;
CREATE TABLE `%%TBL-PREFIX%%base_component_position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `componentPlaceUniqName` varchar(50) NOT NULL DEFAULT '',
  `section` varchar(100) DEFAULT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `componentPlaceUniqName` (`componentPlaceUniqName`)
) ENGINE=MyISAM AUTO_INCREMENT=11270 DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';

--
-- Records of `%%TBL-PREFIX%%base_component_position`
--

LOCK TABLES `%%TBL-PREFIX%%base_component_position` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_component_position` VALUES (6975,'admin-4c627f1bdc9db','top',0),(6986,'admin-4c62811170310','top',0),(10403,'profile-BASE_CMP_ProfileWallWidget','right',0),(11344,'dashboard-BASE_CMP_UserDashWidget','right',3),(11258,'profile-BASE_CMP_UserAvatarWidget','left',0),(11264,'dashboard-BASE_CMP_WelcomeWidget','right',1),(11256,'profile-BASE_CMP_UserViewWidget','right',1),(11263,'dashboard-BASE_CMP_ModerationToolsWidget','right',0);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_component_setting`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_component_setting`;
CREATE TABLE `%%TBL-PREFIX%%base_component_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `componentPlaceUniqName` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` longtext NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'string',
  PRIMARY KEY (`id`),
  UNIQUE KEY `componentPlaceUniqName` (`componentPlaceUniqName`,`name`)
) ENGINE=MyISAM AUTO_INCREMENT=1447 DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';

--
-- Records of `%%TBL-PREFIX%%base_component_setting`
--

LOCK TABLES `%%TBL-PREFIX%%base_component_setting` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_component_setting` VALUES (1432,'admin-5295f2e03ec8a','content','Welcome to our community! Here you\'ll find like-minded individuals who are passionate about the same things as you!','string'),(1433,'admin-5295f2e03ec8a','nl_to_br','0','string'),(1434,'admin-5295f2e03ec8a','title','Welcome!','string'),(1435,'admin-5295f2e03ec8a','show_title','1','string'),(1436,'admin-5295f2e03ec8a','wrap_in_box','1','string'),(1437,'admin-5295f2e03ec8a','restrict_view','0','string'),(1438,'admin-5295f2e03ec8a','access_restrictions','[\"1\",\"12\"]','json'),(1439,'admin-5295f2e40db5c','content','Feel free to participate! Take a look around and help yourself.','string'),(1440,'admin-5295f2e40db5c','nl_to_br','0','string'),(1441,'admin-5295f2e40db5c','title','annotation','string'),(1442,'admin-5295f2e40db5c','show_title','0','string'),(1443,'admin-5295f2e40db5c','wrap_in_box','0','string'),(1444,'admin-5295f2e40db5c','restrict_view','0','string'),(1445,'admin-5295f2e40db5c','access_restrictions','[\"1\",\"12\"]','json');
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_config`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_config`;
CREATE TABLE `%%TBL-PREFIX%%base_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `value` text,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`,`name`)
) ENGINE=MyISAM AUTO_INCREMENT=730 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Records of `%%TBL-PREFIX%%base_config`
--

LOCK TABLES `%%TBL-PREFIX%%base_config` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_config` VALUES (16,'base','avatar_big_size','190','User avatar width'),(17,'base','avatar_size','90','User avatar height'),(18,'admin','admin_menu_state','[]',NULL),(19,'base','selectedTheme','peeptheme','Selected theme.'),(20,'base','military_time','1','Desc'),(21,'base','site_name','peep-1.2.0','Site name'),(22,'base','confirm_email','1','Confirm email'),(23,'base','user_view_presentation','table','User view presentation'),(24,'base','site_tagline','peep-1.2.0','Site tagline'),(25,'base','site_description','Advanced dating and social meet network','Site Description'),(26,'base','site_timezone','US/Pacific','Site Timezone'),(27,'base','site_use_relative_time','1','Use relative date/time'),(31,'base','display_name_question','realname','Question used for display name'),(32,'base','site_email','qwe@mail.com','Email address from which your users will receive notifications and newsletter.'),(34,'base','google_analytics',NULL,NULL),(46,'base','mail_smtp_enabled','0','Smtp enabled'),(45,'base','date_field_format','dmy','Date format'),(47,'base','mail_smtp_host','Host','Smtp Host'),(48,'base','mail_smtp_user','Username','Smtp User'),(49,'base','mail_smtp_password','Password','Smtp passwprd'),(50,'base','mail_smtp_port','Port','Smtp Port'),(51,'base','mail_smtp_connection_prefix','','Smpt connection prefix (tsl, ssl)'),(56,'base','splash_screen','0',NULL),(57,'base','who_can_join','1',NULL),(58,'base','who_can_invite','1',NULL),(59,'base','guests_can_view','1',NULL),(61,'base','guests_can_view_password','',NULL),(62,'base','splash_leave_url','http://google.com',NULL),(70,'base','maintenance','0',NULL),(69,'base','mandatory_user_approve','0','mandatory_user_approve'),(74,'base','billing_currency','USD','Site currency 3-char code'),(79,'base','tf_max_pic_size','2.500000',NULL),(80,'base','soft_build','1','Current soft version'),(81,'base','update_soft','0','Soft core update flag'),(85,'base','unverify_site_email','','Email address from which your users will receive notifications and newsletter.'),(115,'base','soft_version','1.2.0',NULL),(139,'base','site_installed','0',NULL),(140,'base','check_mupdates_ts','0','Last manual updates check timestamp.'),(676,'contact_importer','yahoo_consumer_key','',''),(674,'contact_importer','facebook_app_secret','',''),(179,'admin','mass_mailing_timestamp','0',NULL),(200,'base','dev_mode','1',NULL),(725,'base','log_file_max_size_mb','20',NULL),(726,'base','attch_file_max_size_mb','2',NULL),(727,'base','attch_ext_list','[\"txt\",\"doc\",\"docx\",\"sql\",\"csv\",\"xls\",\"ppt\",\"pdf\",\"jpg\",\"jpeg\",\"png\",\"gif\",\"bmp\",\"psd\",\"ai\",\"avi\",\"wmv\",\"mp3\",\"3gp\",\"flv\",\"mkv\",\"mpeg\",\"mpg\",\"swf\",\"zip\",\"gz\",\"tgz\",\"gzip\",\"7z\",\"bzip2\",\"rar\"]',NULL),(723,'base','admin_cookie','turUXYruzErYXEJymA8eBeZy7aWyqYju',NULL),(241,'base','default_avatar','[]','Default avatar'),(249,'base','language_switch_allowed','1','Allow users switch languages on site'),(276,'base','rss_loading','0',NULL),(277,'base','cron_is_active','1','Flag showing if cron script is activated after soft install'),(675,'contact_importer','yahoo_app_id','',''),(673,'contact_importer','facebook_api_key','',''),(348,'base','users_count_on_page','30','Users count on page'),(367,'base','join_display_photo_upload','display','Display \'Photo Upload\' field on Join page.'),(368,'base','join_photo_upload_set_required','1','Make \'Photo Upload\' a required field on Join Page.'),(369,'base','join_display_terms_of_use',NULL,'Display \'Terms of use\' field on Join page.'),(448,'base','favicon','1',NULL),(404,'base','html_head_code','','Code (meta, css, js) added from admin panel into head section of HTML document.'),(405,'base','html_prebody_code','','Code (js) added before \'body\' closing tag.'),(678,'contact_importer','yahoo_consumer_secret','',''),(444,'base','tf_user_custom_html_disable','1',NULL),(445,'base','tf_user_rich_media_disable','0',NULL),(446,'base','tf_comments_rich_media_disable','0',NULL),(447,'base','tf_resource_list','[\"clipfish.de\",\"youtube.com\",\"google.com\",\"metacafe.com\",\"myspace.com\",\"novamov.com\",\"myvideo.de\"]',NULL),(677,'contact_importer','yahoo_domain_verification_file','',''),(683,'base','cachedEntitiesPostfix','53b266e920eba',NULL),(685,'base','master_page_theme_info','[]',NULL),(686,'base','user_invites_limit','50',NULL),(687,'base','profile_question_edit_stamp','1402999957',NULL),(688,'base','install_complete','0',NULL),(728,'base','users_on_page','12',NULL),(729,'base','avatar_max_upload_size','1','Enable file attachments');
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_cron_job`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_cron_job`;
CREATE TABLE `%%TBL-PREFIX%%base_cron_job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `methodName` varchar(200) NOT NULL DEFAULT '',
  `runStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `className` (`methodName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_cron_job`
--

LOCK TABLES `%%TBL-PREFIX%%base_cron_job` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_db_cache`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_db_cache`;
CREATE TABLE `%%TBL-PREFIX%%base_db_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expireStamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';

--
-- Records of `%%TBL-PREFIX%%base_db_cache`
--

LOCK TABLES `%%TBL-PREFIX%%base_db_cache` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_document`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_document`;
CREATE TABLE `%%TBL-PREFIX%%base_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `class` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `uri` varchar(255) DEFAULT NULL,
  `isStatic` tinyint(1) NOT NULL DEFAULT '0',
  `isMobile` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `uriIndex` (`uri`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_document`
--

LOCK TABLES `%%TBL-PREFIX%%base_document` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_document` VALUES (3,'admin_pages','ADMIN_Pages','index',NULL,0,0),(23,'page-679283',NULL,NULL,'join',1,0),(39,'page-119658',NULL,NULL,'terms-of-use',1,0),(54,'page_81959573',NULL,NULL,'privacy-policy',1,0);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_email_verify`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_email_verify`;
CREATE TABLE `%%TBL-PREFIX%%base_email_verify` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL DEFAULT '0',
  `type` enum('user','site') NOT NULL,
  `email` varchar(128) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `createStamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_email_verify`
--

LOCK TABLES `%%TBL-PREFIX%%base_email_verify` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_entity_tag`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_entity_tag`;
CREATE TABLE `%%TBL-PREFIX%%base_entity_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entityId` int(10) unsigned NOT NULL,
  `entityType` varchar(255) NOT NULL,
  `tagId` int(10) unsigned NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `entityId` (`entityId`),
  KEY `entityType` (`entityType`),
  KEY `tagId` (`tagId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_entity_tag`
--

LOCK TABLES `%%TBL-PREFIX%%base_entity_tag` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_flag`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_flag`;
CREATE TABLE `%%TBL-PREFIX%%base_flag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityType` varchar(100) NOT NULL,
  `entityId` int(11) DEFAULT NULL,
  `userId` int(11) DEFAULT NULL,
  `reason` varchar(50) DEFAULT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entityType` (`entityType`,`entityId`,`userId`),
  KEY `timeStamp` (`timeStamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_flag`
--

LOCK TABLES `%%TBL-PREFIX%%base_flag` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_geolocation_country`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_geolocation_country`;
CREATE TABLE `%%TBL-PREFIX%%base_geolocation_country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cc2` char(2) NOT NULL,
  `cc3` char(3) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=239 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_geolocation_country`
--

LOCK TABLES `%%TBL-PREFIX%%base_geolocation_country` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_geolocation_country` VALUES (1,'AW','ABW','ARUBA'),(2,'AF','AFG','AFGHANISTAN'),(3,'AO','AGO','ANGOLA'),(4,'AI','AIA','ANGUILLA'),(5,'AL','ALB','ALBANIA'),(6,'AD','AND','ANDORRA'),(7,'AN','ANT','NETHERLANDS ANTILLES'),(8,'AE','ARE','UNITED ARAB EMIRATES'),(9,'AR','ARG','ARGENTINA'),(10,'AM','ARM','ARMENIA'),(11,'AS','ASM','AMERICAN SAMOA'),(12,'AQ','ATA','ANTARCTICA'),(13,'TF','ATF','FRENCH SOUTHERN TERRITORIES'),(14,'AG','ATG','ANTIGUA AND BARBUDA'),(15,'AU','AUS','AUSTRALIA'),(16,'AT','AUT','AUSTRIA'),(17,'AZ','AZE','AZERBAIJAN'),(18,'BI','BDI','BURUNDI'),(19,'BE','BEL','BELGIUM'),(20,'BJ','BEN','BENIN'),(21,'BF','BFA','BURKINA FASO'),(22,'BD','BGD','BANGLADESH'),(23,'BG','BGR','BULGARIA'),(24,'BH','BHR','BAHRAIN'),(25,'BS','BHS','BAHAMAS'),(26,'BA','BIH','BOSNIA AND HERZEGOVINA'),(27,'BY','BLR','BELARUS'),(28,'BZ','BLZ','BELIZE'),(29,'BM','BMU','BERMUDA'),(30,'BO','BOL','BOLIVIA'),(31,'BR','BRA','BRAZIL'),(32,'BB','BRB','BARBADOS'),(33,'BN','BRN','BRUNEI DARUSSALAM'),(34,'BT','BTN','BHUTAN'),(35,'BV','BVT','BOUVET ISLAND'),(36,'BW','BWA','BOTSWANA'),(37,'CF','CAF','CENTRAL AFRICAN REPUBLIC'),(38,'CA','CAN','CANADA'),(39,'CH','CHE','SWITZERLAND'),(40,'CL','CHL','CHILE'),(41,'CN','CHN','CHINA'),(42,'CI','CIV','COTE D\'IVOIRE'),(43,'CM','CMR','CAMEROON'),(44,'CD','COD','THE DEMOCRATIC REPUBLIC OF THE CONGO'),(45,'CG','COG','CONGO'),(46,'CK','COK','COOK ISLANDS'),(47,'CO','COL','COLOMBIA'),(48,'KM','COM','COMOROS'),(49,'CV','CPV','CAPE VERDE'),(50,'CR','CRI','COSTA RICA'),(51,'CU','CUB','CUBA'),(52,'KY','CYM','CAYMAN ISLANDS'),(53,'CY','CYP','CYPRUS'),(54,'CZ','CZE','CZECH REPUBLIC'),(55,'DE','DEU','GERMANY'),(56,'DJ','DJI','DJIBOUTI'),(57,'DM','DMA','DOMINICA'),(58,'DK','DNK','DENMARK'),(59,'DO','DOM','DOMINICAN REPUBLIC'),(60,'DZ','DZA','ALGERIA'),(61,'EC','ECU','ECUADOR'),(62,'EG','EGY','EGYPT'),(63,'ER','ERI','ERITREA'),(64,'ES','ESP','SPAIN'),(65,'EE','EST','ESTONIA'),(66,'ET','ETH','ETHIOPIA'),(67,'FI','FIN','FINLAND'),(68,'FJ','FJI','FIJI'),(69,'FK','FLK','FALKLAND ISLANDS (MALVINAS)'),(70,'FR','FRA','FRANCE'),(71,'FO','FRO','FAROE ISLANDS'),(72,'FM','FSM','FEDERATED STATES OF MICRONESIA'),(73,'GA','GAB','GABON'),(74,'GB','GBR','UNITED KINGDOM'),(75,'GE','GEO','GEORGIA'),(76,'GG','GGY','GUERNSEY'),(77,'GH','GHA','GHANA'),(78,'GI','GIB','GIBRALTAR'),(79,'GN','GIN','GUINEA'),(80,'GP','GLP','GUADELOUPE'),(81,'GM','GMB','GAMBIA'),(82,'GW','GNB','GUINEA-BISSAU'),(83,'GQ','GNQ','EQUATORIAL GUINEA'),(84,'GR','GRC','GREECE'),(85,'GD','GRD','GRENADA'),(86,'GL','GRL','GREENLAND'),(87,'GT','GTM','GUATEMALA'),(88,'GF','GUF','FRENCH GUIANA'),(89,'GU','GUM','GUAM'),(90,'GY','GUY','GUYANA'),(91,'HK','HKG','HONG KONG'),(92,'HN','HND','HONDURAS'),(93,'HR','HRV','CROATIA'),(94,'HT','HTI','HAITI'),(95,'HU','HUN','HUNGARY'),(96,'ID','IDN','INDONESIA'),(97,'IM','IMN','ISLE OF MAN'),(98,'IN','IND','INDIA'),(99,'IO','IOT','BRITISH INDIAN OCEAN TERRITORY'),(100,'IE','IRL','IRELAND'),(101,'IR','IRN','ISLAMIC REPUBLIC OF IRAN'),(102,'IQ','IRQ','IRAQ'),(103,'IS','ISL','ICELAND'),(104,'IL','ISR','ISRAEL'),(105,'IT','ITA','ITALY'),(106,'JM','JAM','JAMAICA'),(107,'JE','JEY','JERSEY'),(108,'JO','JOR','JORDAN'),(109,'JP','JPN','JAPAN'),(110,'KZ','KAZ','KAZAKHSTAN'),(111,'KE','KEN','KENYA'),(112,'KG','KGZ','KYRGYZSTAN'),(113,'KH','KHM','CAMBODIA'),(114,'KI','KIR','KIRIBATI'),(115,'KN','KNA','SAINT KITTS AND NEVIS'),(116,'KR','KOR','REPUBLIC OF KOREA'),(117,'KW','KWT','KUWAIT'),(118,'LA','LAO','LAO PEOPLE\'S DEMOCRATIC REPUBLIC'),(119,'LB','LBN','LEBANON'),(120,'LR','LBR','LIBERIA'),(121,'LY','LBY','LIBYAN ARAB JAMAHIRIYA'),(122,'LC','LCA','SAINT LUCIA'),(123,'LI','LIE','LIECHTENSTEIN'),(124,'LK','LKA','SRI LANKA'),(125,'LS','LSO','LESOTHO'),(126,'LT','LTU','LITHUANIA'),(127,'LU','LUX','LUXEMBOURG'),(128,'LV','LVA','LATVIA'),(129,'MO','MAC','MACAO'),(130,'MF','MAF','SAINT MARTIN'),(131,'MA','MAR','MOROCCO'),(132,'MC','MCO','MONACO'),(133,'MD','MDA','REPUBLIC OF MOLDOVA'),(134,'MG','MDG','MADAGASCAR'),(135,'MV','MDV','MALDIVES'),(136,'MX','MEX','MEXICO'),(137,'MH','MHL','MARSHALL ISLANDS'),(138,'MK','MKD','THE FORMER YUGOSLAV REPUBLIC OF MACEDONIA'),(139,'ML','MLI','MALI'),(140,'MT','MLT','MALTA'),(141,'MM','MMR','MYANMAR'),(142,'ME','MNE','MONTENEGRO'),(143,'MN','MNG','MONGOLIA'),(144,'MP','MNP','NORTHERN MARIANA ISLANDS'),(145,'MZ','MOZ','MOZAMBIQUE'),(146,'MR','MRT','MAURITANIA'),(147,'MS','MSR','MONTSERRAT'),(148,'MQ','MTQ','MARTINIQUE'),(149,'MU','MUS','MAURITIUS'),(150,'MW','MWI','MALAWI'),(151,'MY','MYS','MALAYSIA'),(152,'YT','MYT','MAYOTTE'),(153,'NA','NAM','NAMIBIA'),(154,'NC','NCL','NEW CALEDONIA'),(155,'NE','NER','NIGER'),(156,'NF','NFK','NORFOLK ISLAND'),(157,'NG','NGA','NIGERIA'),(158,'NI','NIC','NICARAGUA'),(159,'NU','NIU','NIUE'),(160,'NL','NLD','NETHERLANDS'),(161,'NO','NOR','NORWAY'),(162,'NP','NPL','NEPAL'),(163,'NR','NRU','NAURU'),(164,'NZ','NZL','NEW ZEALAND'),(165,'OM','OMN','OMAN'),(166,'PK','PAK','PAKISTAN'),(167,'PA','PAN','PANAMA'),(168,'PE','PER','PERU'),(169,'PH','PHL','PHILIPPINES'),(170,'PW','PLW','PALAU'),(171,'PG','PNG','PAPUA NEW GUINEA'),(172,'PL','POL','POLAND'),(173,'PR','PRI','PUERTO RICO'),(174,'KP','PRK','DEMOCRATIC PEOPLE\'S REPUBLIC OF KOREA'),(175,'PT','PRT','PORTUGAL'),(176,'PY','PRY','PARAGUAY'),(177,'PS','PSE','PALESTINIAN TERRITORY, OCCUPIED'),(178,'PF','PYF','FRENCH POLYNESIA'),(179,'QA','QAT','QATAR'),(180,'RE','REU','REUNION'),(181,'RO','ROM','ROMANIA'),(182,'RU','RUS','RUSSIAN FEDERATION'),(183,'RW','RWA','RWANDA'),(184,'SA','SAU','SAUDI ARABIA'),(185,'CS','SCG','SERBIA AND MONTENEGRO'),(186,'SD','SDN','SUDAN'),(187,'SN','SEN','SENEGAL'),(188,'SG','SGP','SINGAPORE'),(189,'GS','SGS','SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS'),(190,'SB','SLB','SOLOMON ISLANDS'),(191,'SL','SLE','SIERRA LEONE'),(192,'SV','SLV','EL SALVADOR'),(193,'SM','SMR','SAN MARINO'),(194,'SO','SOM','SOMALIA'),(195,'PM','SPM','SAINT PIERRE AND MIQUELON'),(196,'RS','SRB','SERBIA'),(197,'ST','STP','SAO TOME AND PRINCIPE'),(198,'SR','SUR','SURINAME'),(199,'SK','SVK','SLOVAKIA'),(200,'SI','SVN','SLOVENIA'),(201,'SE','SWE','SWEDEN'),(202,'SZ','SWZ','SWAZILAND'),(203,'SC','SYC','SEYCHELLES'),(204,'SY','SYR','SYRIAN ARAB REPUBLIC'),(205,'TC','TCA','TURKS AND CAICOS ISLANDS'),(206,'TD','TCD','CHAD'),(207,'TG','TGO','TOGO'),(208,'TH','THA','THAILAND'),(209,'TJ','TJK','TAJIKISTAN'),(210,'TK','TKL','TOKELAU'),(211,'TM','TKM','TURKMENISTAN'),(212,'TL','TLS','TIMOR-LESTE'),(213,'TO','TON','TONGA'),(214,'TT','TTO','TRINIDAD AND TOBAGO'),(215,'TN','TUN','TUNISIA'),(216,'TR','TUR','TURKEY'),(217,'TV','TUV','TUVALU'),(218,'TW','TWN','TAIWAN'),(219,'TZ','TZA','UNITED REPUBLIC OF TANZANIA'),(220,'UG','UGA','UGANDA'),(221,'UA','UKR','UKRAINE'),(222,'UM','UMI','UNITED STATES MINOR OUTLYING ISLANDS'),(223,'UY','URY','URUGUAY'),(224,'US','USA','UNITED STATES'),(225,'UZ','UZB','UZBEKISTAN'),(226,'VA','VAT','HOLY SEE (VATICAN CITY STATE)'),(227,'VC','VCT','SAINT VINCENT AND THE GRENADINES'),(228,'VE','VEN','VENEZUELA'),(229,'VG','VGB','VIRGIN ISLANDS, BRITISH'),(230,'VI','VIR','VIRGIN ISLANDS, U.S.'),(231,'VN','VNM','VIET NAM'),(232,'VU','VUT','VANUATU'),(233,'WF','WLF','WALLIS AND FUTUNA'),(234,'WS','WSM','SAMOA'),(235,'YE','YEM','YEMEN'),(236,'ZA','ZAF','SOUTH AFRICA'),(237,'ZM','ZMB','ZAMBIA'),(238,'ZW','ZWE','ZIMBABWE');
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_invitation`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_invitation`;
CREATE TABLE `%%TBL-PREFIX%%base_invitation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityType` varchar(255) NOT NULL,
  `entityId` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `userId` int(11) NOT NULL,
  `pluginKey` varchar(255) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `viewed` int(11) NOT NULL,
  `sent` tinyint(4) NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `data` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entityType` (`entityType`,`entityId`,`userId`),
  KEY `timeStamp` (`timeStamp`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_invitation`
--

LOCK TABLES `%%TBL-PREFIX%%base_invitation` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_invite_code`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_invite_code`;
CREATE TABLE `%%TBL-PREFIX%%base_invite_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `expiration_stamp` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_invite_code`
--

LOCK TABLES `%%TBL-PREFIX%%base_invite_code` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_language`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_language`;
CREATE TABLE `%%TBL-PREFIX%%base_language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(32) NOT NULL,
  `label` varchar(32) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '1',
  `status` enum('active','inactive') DEFAULT 'inactive',
  `rtl` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_language`
--

LOCK TABLES `%%TBL-PREFIX%%base_language` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_language` VALUES (1,'en','English',1,'active',0);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_language_key`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_language_key`;
CREATE TABLE `%%TBL-PREFIX%%base_language_key` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prefixId` int(11) NOT NULL DEFAULT '0',
  `key` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prefix_key` (`prefixId`,`key`)
) ENGINE=MyISAM AUTO_INCREMENT=18668 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_language_key`
--

LOCK TABLES `%%TBL-PREFIX%%base_language_key` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_language_key` VALUES (5837,7,'forgot_password_heading'),(1603,1,'you_are_editing'),(1604,1,'check_other_langs'),(1605,1,'add_new_text'),(1606,1,'go'),(1607,1,'search_results_for_keyword'),(1608,1,'search'),(1609,1,'original_value'),(1610,1,'translation'),(1611,1,'delete'),(1612,1,'custom_keys'),(1613,1,'save_this_page'),(1614,1,'section'),(1615,1,'all_sections'),(1616,1,'missing_text'),(1617,1,'page_note_part_1'),(1618,1,'page_note_part_2'),(1619,1,'msg_one_active_constraint'),(1620,1,'empty'),(1621,1,'missing_keys2'),(1622,1,'active_languages'),(1623,1,'language'),(1624,1,'btn_label_edit'),(1625,1,'btn_label_clone'),(1626,1,'btn_label_deactivate'),(1627,1,'btn_label_activate'),(1628,1,'btn_label_delete'),(9030,7,'authorization_action_newsfeed_add_comment'),(1682,1,'import_lang_note'),(1683,1,'add_new_lang_or_pack'),(1684,1,'select_items_to_import_note'),(1685,1,'lang_import_check_all'),(1686,1,'import_lang_button_label'),(1687,1,'export_lang_header'),(1688,1,'export_lang_note'),(1689,1,'export_lang_button_label'),(1690,1,'export_lang_note2'),(1691,1,'export_lang_button_label2'),(1692,1,'show'),(1693,1,'edit_language'),(1694,1,'available_languages'),(1695,1,'add_key_form_lbl_key'),(1696,1,'add_key_form_lbl_val'),(1697,1,'add_key_form_lbl_add'),(1698,1,'lang_file'),(1699,1,'inactive_languages'),(1700,1,'clone_language'),(1701,1,'clone_form_lbl_label'),(1702,1,'title_add_new_text'),(1703,1,'clone_form_descr_label'),(1704,1,'are_you_sure'),(1705,1,'clone_form_lbl_tag'),(1706,1,'clone_form_descr_tag'),(1708,1,'clone_form_lbl_submit'),(1709,1,'search_no_results_for_keyword'),(1710,1,'no_values'),(1711,7,'forgot_password_submit_label'),(1712,1,'def'),(1713,1,'msg_dublicate_key'),(9029,7,'authorization_group_newsfeed'),(1741,7,'month_2'),(1742,7,'month_8'),(1743,7,'month_9'),(1745,7,'all'),(1746,7,'archive'),(1747,7,'are_you_sure'),(15468,1,'themes_item_add_success_message'),(1752,7,'comments_label'),(1754,7,'by'),(1755,7,'more'),(5833,7,'forgot_password_no_user_error_message'),(15469,1,'theme_add_duplicated_dir_error'),(1768,7,'flag'),(1769,7,'edit'),(1770,7,'delete'),(15467,1,'themes_add_theme_page_heading'),(1773,7,'approve'),(1774,7,'disapprove'),(15472,7,'user_search_back_to_search'),(15471,7,'edit_remote_field_synchronize_title'),(1855,7,'avatar_avatar_is'),(1856,7,'avatar_current'),(1857,7,'avatar_new'),(1858,7,'avatar_crop'),(1859,7,'avatar_crop_instructions'),(1860,7,'avatar_picture'),(1861,7,'avatar_apply_crop'),(16390,1,'error_cant_connect_to_host'),(1863,7,'avatar_preview'),(1886,1,'permissions_roles'),(1887,1,'sidebar_menu_item_settings_language'),(1889,1,'sidebar_menu_item_pages_manage'),(1890,1,'themes_choose_activate_button_label'),(1891,1,'theme_settings_form_submit_label'),(2583,1,'permissions_edit_role'),(1893,1,'theme_settings_no_controls_label'),(1894,7,'not_valid_image'),(1895,7,'questions_question_user_photo_label'),(1896,7,'join_error_photo_file_is_not_valid'),(1897,1,'sidebar_menu_item_theme_choose'),(1898,7,'join_error_password_too_long'),(1899,7,'join_error_password_too_short'),(1900,7,'join_error_password_not_valid'),(1901,7,'join_error_email_already_exist'),(1902,7,'join_error_email_not_valid'),(1903,7,'join_error_username_not_valid'),(1904,1,'themes_choose_list_cap_title'),(1905,1,'themes_choose_page_title'),(1906,7,'join_error_username_already_exist'),(1918,1,'delete_btn_label'),(1919,7,'date_time_today'),(1920,1,'save_btn_label'),(1921,1,'updated_msg'),(1922,1,'page_default_title'),(1923,1,'page_default_heading'),(1926,11,'page_custom_title'),(1927,7,'main_menu_my_profile'),(1928,7,'main_menu_index'),(1930,11,'page_default_title'),(1931,11,'page_default_heading'),(1932,11,'page_default_description'),(1933,11,'page_default_keywords'),(1934,1,'themes_settings_page_title'),(1935,1,'sidebar_menu_settings'),(1936,1,'sidebar_menu_pages'),(1937,1,'sidebar_menu_appearance'),(1938,1,'sidebar_menu_users'),(1939,1,'sidebar_menu_plugins'),(1940,7,'sign_in_submit_label'),(1942,1,'sidebar_menu_item_theme_edit'),(1943,1,'theme_css_edit_css_box_cap_label'),(1944,1,'theme_css_edit_submit_label'),(1948,7,'tag_cloud_cap_label'),(1949,1,'permissions_moderators'),(1950,1,'permissions_index'),(1951,11,'menu_item_main_photo_list'),(1952,11,'menu_item_main_forum'),(1953,1,'theme_css_existing_css_box_cap_label'),(1954,1,'theme_graphics_delete_success_message'),(1955,1,'theme_graphics_upload_form_fail_message'),(1956,1,'theme_graphics_upload_form_success_message'),(1957,1,'theme_graphics_upload_form_submit_label'),(1958,1,'theme_graphics_table_delete'),(1959,1,'theme_graphics_table_url'),(1960,1,'theme_graphics_table_preview'),(1961,1,'theme_graphics_list_cap_label'),(1962,1,'theme_graphics_upload_label'),(1963,7,'questions_question_account_type_label'),(2170,1,'site_tagline'),(1965,1,'theme_settings_cap_label'),(1966,1,'theme_info_author_url_label'),(1967,1,'theme_info_author_label'),(1968,7,'pages_label'),(1970,1,'main_menu_admin'),(1971,7,'date_time_within_one_minute'),(1972,7,'date_time_one_minute_ago'),(1973,7,'date_time_minutes_ago'),(1974,7,'users_main_menu_item'),(1976,1,'theme_info_compatibility_label'),(1977,7,'cmp_add_new_content_box_cap_label'),(1978,1,'theme_info_version_label'),(2601,1,'permissions_default_role'),(2556,7,'email_verify_template_html'),(2476,1,'questions_question_description_label'),(3807,1,'sidebar_menu_privacy'),(1992,7,'comment_add_submit_label'),(1993,7,'comment_box_cap_label'),(1994,7,'avatar_btn_upload'),(1995,7,'avatar_upload_types'),(2643,7,'questions_question_realname_description'),(1998,7,'avatar_change_avatar'),(1999,7,'questions_no_section_label'),(2000,7,'questions_add_new_account_type'),(2008,7,'join_submit_button_continue'),(2009,7,'join_submit_button_join'),(2011,7,'questions_question_c441a8a9b955647cdf4c81562d39068a_label'),(2012,7,'questions_section_47f3a94e6cfe733857b31116ce21c337_label'),(2013,1,'sidebar_menu_item_plugin_photo'),(2014,1,'sidebar_menu_item_plugin_video'),(2015,7,'comment_add_post_error'),(2016,7,'comment_add_auth_error'),(2017,7,'comment_no_comments'),(2018,7,'comment_delete_label'),(2019,7,'date_time_one_hour_ago'),(2020,7,'date_time_hours_ago'),(2021,7,'date_time_yesterday'),(2022,7,'date_time_at_label'),(2025,7,'date_time_month_short_9'),(2026,7,'date_time_month_short_1'),(2027,7,'date_time_month_short_2'),(2028,7,'date_time_month_short_3'),(2029,7,'date_time_month_short_4'),(2030,7,'date_time_month_short_5'),(2031,7,'date_time_month_short_6'),(2032,7,'date_time_month_short_7'),(2033,7,'date_time_month_short_8'),(2034,7,'date_time_month_short_10'),(2035,7,'date_time_month_short_11'),(2036,7,'date_time_month_short_12'),(2037,1,'sidebar_menu_item_graphics'),(2038,1,'sidebar_menu_item_css'),(2039,1,'sidebar_menu_item_settings'),(2041,7,'questions_question_repeat_password_label'),(2042,7,'questions_question_password_label'),(2043,7,'questions_question_email_label'),(2044,1,'sidebar_menu_item_questions'),(2047,7,'questions_question_username_label'),(2397,7,'questions_account_type_ef5e279523aed72d87fd8a1fd59d592f'),(2427,7,'questions_section_f90cde5913235d172603cc4e7b9726e3_label'),(2051,1,'sidebar_menu_item_permissions'),(2058,7,'widgets_customize_btn'),(2059,7,'avatar_change'),(2060,7,'avatar_console_edit_profile'),(2061,7,'base_document_404_heading'),(2062,7,'base_document_404'),(2065,7,'view_all'),(2066,7,'month_12'),(2070,7,'user_list_menu_item_latest'),(2071,7,'user_list_menu_item_featured'),(2072,7,'widgets_finish_customize_btn'),(2073,7,'widgets_reset_customization'),(2074,7,'custom_html_widget_default_title'),(2075,1,'sidebar_menu_admin'),(2076,7,'widgets_section_box_title'),(2077,1,'sidebar_menu_dashboard'),(2078,1,'sidebar_menu_item_user_dashboard'),(2079,1,'sidebar_menu_item_user_profile'),(2080,7,'join_index_join_button'),(2081,1,'sidebar_menu_plugins_manage'),(2082,7,'authorization_role_guest'),(2611,7,'massmailing_unsubscribe_successful'),(2085,7,'widgets_action_edit'),(2086,7,'widgets_action_delete'),(2088,7,'custom_html_widget_content_label'),(2093,7,'custom_html_widget_nl2br_label'),(2096,7,'authorization_group_video'),(2097,7,'authorization_action_video_add'),(2098,7,'authorization_action_video_view'),(2099,7,'authorization_group_photo'),(2100,7,'authorization_action_photo_view'),(2101,7,'authorization_action_photo_upload'),(2102,7,'authorization_action_photo_add_comment'),(2103,7,'authorization_action_photo_delete_comment_by_content_owner'),(2104,7,'authorization_group_base'),(2105,7,'authorization_action_base_add_comment'),(2106,7,'authorization_action_base_delete_comment_by_content_owner'),(2107,1,'back_to_site_label'),(2108,7,'widgets_fb_default_settings_label'),(2109,7,'widgets_default_settings_title'),(2110,7,'widgets_default_settings_show_title'),(2111,7,'widgets_default_settings_icon'),(2112,7,'widgets_default_settings_wib'),(2113,7,'widgets_default_settings_freeze'),(2114,7,'widgets_admin_section_information'),(2115,7,'widgets_admin_legend'),(2116,7,'widgets_allow_customize_legend'),(2117,7,'widgets_delete_component_confirm'),(2118,7,'widgets_allow_customize_label'),(2122,7,'widgets_fb_setting_box_title'),(2130,1,'main_menu_item'),(2125,7,'cmp_widget_wall_comments_count'),(2126,7,'cmp_widget_wall_comments_mode'),(2127,7,'cmp_widget_wall_comments_mode_option_2'),(2128,7,'cmp_widget_wall_comments_mode_option_1'),(2131,7,'user_list_widget_empty'),(2132,7,'user_list_menu_item_online'),(2134,7,'widgets_customize_label'),(2135,7,'questions_question_birthdate_label'),(2997,7,'base_join_menu_item'),(2141,1,'admin_dashboard'),(2142,1,'sidebar_menu_item_users'),(2143,1,'sidebar_menu_item_main_settings'),(2144,7,'view_index'),(2145,7,'rss_widget_default_title'),(2146,7,'rss_widget_count_label'),(2147,7,'rss_widget_url_label'),(2148,7,'rss_widget_url_invalid_msg'),(2555,7,'email_verify_send_verify_mail_button_label'),(2257,1,'questions_delete_question_confirmation'),(2155,7,'join_promo'),(2536,1,'questions_add_new_account_type'),(2523,1,'questions_add_values_description'),(2161,1,'heading_main_settings'),(2162,1,'menu_item_basics'),(9873,1,'heading_user_settings'),(2164,7,'questions_question_accountType_description'),(2165,7,'questions_question_username_description'),(2166,7,'questions_question_email_description'),(2167,7,'join_index'),(2168,1,'site_installation'),(2169,1,'site_title'),(2171,1,'site_tagline_desc'),(2172,1,'site_description'),(2173,1,'site_description_desc'),(2174,1,'time_settings'),(2175,1,'timezone'),(2176,1,'use_relative_time'),(2177,1,'site_relative_time_desc'),(2178,7,'questions_question_sex_label'),(2179,7,'questions_question_sex_description'),(2180,7,'questions_question_sex_value_1'),(2181,7,'questions_question_sex_value_2'),(2182,7,'questions_question_accountType_label'),(2183,7,'questions_checkbox_value_true'),(2184,7,'questions_checkbox_value_false'),(2185,1,'main_settings_updated'),(2186,7,'widgets_no_content'),(2187,7,'rss_widget_title_only_label'),(2188,7,'view_no_section_label'),(4717,1,'core_update_leave_button_label'),(3812,1,'menu_item_users_recently_active'),(2196,1,'menu_item_user_roles'),(2200,7,'questions_empty_lang_value'),(2207,7,'authorization_group_blogs'),(2208,7,'authorization_action_blogs_add_comment'),(2214,7,'about_me_widget_inv_text'),(2215,7,'about_me_widget_default_title'),(2216,7,'about_me_widget_content_saved'),(2217,1,'heading_browse_users'),(2218,1,'no_users'),(2219,7,'check_all'),(2220,7,'with_selected'),(2231,1,'joined'),(2232,1,'user'),(2233,7,'form_element_select_field_invitation_label'),(2234,7,'form_element_common_invitation_text'),(2235,1,'user_delete_msg'),(2236,1,'confirm_delete_users'),(2642,7,'questions_question_realname_label'),(2554,7,'email_verify_index'),(2582,1,'massmailing_send_mails_message'),(5832,7,'forgot_password_form_text'),(2256,1,'questions_question_was_deleted'),(2269,7,'component_sign_in_login_invitation'),(2270,7,'rates_box_cap_label'),(2271,1,'questions_section_was_deleted'),(2430,7,'questions_admin_description_label'),(2429,7,'questions_admin_question_label'),(2329,7,'questions_menu_add'),(2330,7,'questions_menu_editAccountType'),(2436,7,'copyright'),(2324,7,'questions_account_type_was_added'),(2314,1,'questions_account_type_was_deleted'),(2307,7,'questions_admin_edit_label'),(2308,7,'questions_admin_delete_label'),(2660,7,'suspended_user_page_content'),(2328,7,'questions_menu_index'),(2312,7,'forgot_password_cap_label'),(2313,7,'forgot_password_email_invitation_message'),(2315,7,'forgot_password_mail_template_subject'),(6052,7,'reset_password_mail_template_subject'),(2365,7,'questions_menu_settings'),(16387,1,'add_button'),(2333,7,'comments_add_auth_message'),(2334,7,'questions_question_presentation_text_label'),(15499,7,'questions_question_55c95a36e50b0d7a795fb1caa8a8e520_label'),(2337,7,'questions_question_presentation_textarea_label'),(2338,7,'questions_question_presentation_checkbox_label'),(2339,7,'questions_question_presentation_multicheckbox_label'),(2340,7,'questions_question_presentation_date_label'),(2341,7,'questions_question_presentation_url_label'),(2342,7,'questions_question_presentation_password_label'),(2654,7,'email_verify_verify_mail_was_sent'),(2655,7,'suspend_user_btn'),(2656,7,'user_suspend_btn_lbl'),(2366,7,'questions_config_user_view_presentation_description'),(2355,1,'sidebar_menu_item_plugin_blogs'),(2356,1,'sidebar_menu_item_plugin_links'),(2367,7,'questions_config_user_view_presentation_label'),(2368,7,'authorization_action_blogs_add'),(2370,7,'authorization_action_blogs_view'),(2374,7,'edit_button'),(2375,7,'edit_successfull_edit'),(2376,7,'edit_index'),(2390,1,'questions_edit_section_name_title'),(2381,7,'widgets_panel_dashboard_label'),(2382,1,'page_title_manage_plugins'),(2383,1,'manage_plugins_active_box_cap_label'),(2384,1,'manage_plugins_inactive_box_cap_label'),(2385,1,'manage_plugins_settings_button_label'),(2386,1,'manage_plugins_deactivate_button_label'),(2387,1,'manage_plugins_activate_button_label'),(2388,1,'manage_plugins_uninstall_button_label'),(2389,1,'manage_plugins_install_button_label'),(2391,1,'questions_edit_question_value_title'),(2392,1,'questions_edit_question_name_title'),(2393,1,'questions_edit_question_description_title'),(2394,7,'questions_account_type_all'),(2395,1,'questions_edit_account_type_name_title'),(2396,1,'manage_plugins_install_success_message'),(2406,7,'questions_account_type_290365aadde35a97f11207ca7e4279cc'),(16389,1,'default_role'),(2593,7,'massmailing_unsubscribe'),(2594,1,'permissions_roles_deleted_msg'),(2599,1,'permissions_user_role'),(2600,1,'permissions_number_of_users'),(2595,1,'permissions_please_select_role'),(2589,1,'permissions_role_added_msg'),(2588,1,'permissions_add_form_role_lbl'),(2581,7,'rate_cmp_success_message'),(2425,7,'auth_identity_not_found_error_message'),(2426,7,'auth_invlid_password_error_message'),(2431,7,'auth_success_message'),(2468,1,'msg_lang_cloned'),(2469,1,'heading_questions'),(2472,1,'languages_page_heading'),(2473,1,'questions_account_type_label'),(2474,1,'questions_new_question_label'),(2475,1,'questions_question_name_label'),(2477,1,'questions_for_account_type_label'),(2478,1,'questions_for_account_type_description'),(2479,1,'questions_question_section_label'),(2480,1,'questions_answer_type_label'),(18463,1,'questions_delete_account_type_confirmation'),(2486,1,'questions_possible_values_description'),(2487,1,'questions_columns_count_label'),(2488,1,'questions_required_label'),(2489,1,'questions_required_description'),(2490,1,'questions_on_sing_up_label'),(2491,1,'questions_on_sing_up_description'),(2492,1,'questions_on_edit_label'),(2493,1,'questions_on_edit_description'),(2494,1,'questions_on_view_label'),(2495,1,'questions_on_view_description'),(2496,1,'questions_on_search_description'),(2497,1,'questions_on_search_label'),(2498,1,'questions_save_and_new_label'),(2585,1,'massmailing_unsubscribe_link_text'),(2584,1,'massmailing_unsubscribe_link_html'),(2501,1,'questions_add_question_message'),(2504,1,'questions_update_question_message'),(18472,253,'admin_nav_item_type_external'),(2505,1,'pages_new_form_or'),(2506,1,'questions_edit_delete_value_confirm_message'),(2511,1,'pages_page_field_meta_desc'),(16388,1,'add_question_value_description'),(2513,1,'questions_edit_question_description_label'),(2515,1,'questions_admin_existing_values'),(2516,1,'questions_admin_dragndrop_reorder'),(2517,1,'questions_add_question_values_title'),(2518,1,'questions_question_was_not_update_message'),(2519,1,'questions_question_was_not_updated_message'),(2541,1,'questions_section_was_added'),(2522,1,'questions_add_values_label'),(2525,1,'questions_add_values_submit_button'),(2526,1,'pages_page_heading'),(2531,1,'questions_empty_lang_value'),(2529,1,'questions_question_is_not_exist'),(2533,1,'questions_add_question_values_message'),(2542,1,'questions_index_info_txt'),(2540,1,'questions_account_type_was_added'),(2543,1,'questions_index_drag_n_drop_info_txt'),(2544,1,'questions_add_new_question_button'),(2545,1,'questions_profile_question_sections_title'),(2546,1,'questions_section_info_txt'),(2547,1,'questions_new_section_label'),(2549,1,'questions_delete_section_confirmation'),(2557,7,'email_verify_template_text'),(2558,7,'email_verify_email_verify_success'),(2559,1,'questions_add_new_section_button'),(2561,7,'new_message_count_text'),(2562,1,'sidebar_menu_item_dev_langs'),(2661,7,'user_feedback_profile_unsuspended'),(2564,7,'email_verify_promo'),(2565,7,'email_verify_email_verify_fail'),(2568,1,'permissions_edit_role_btn'),(2569,1,'permissions_add_role_btn'),(2570,1,'massmailing_ignore_unsubscribe_label'),(2571,1,'massmailing_email_format_html'),(2572,1,'massmailing_email_format_text'),(2573,1,'massmailing_body_label'),(2574,1,'massmailing_subject_label'),(2575,1,'massmailing_email_format_label'),(2576,1,'massmailing_user_section_label'),(2577,1,'massmailing_total_members'),(2578,1,'massmailing_preview_label'),(2579,1,'massmailing_compose_email'),(2580,1,'massmailing_start_mailing_button'),(2602,1,'permissions_check_all_selected'),(2603,1,'permissions_delete_role'),(2604,1,'permissions_are_you_sure'),(2605,1,'permissions_go_to_permissions_page'),(2609,7,'authorization_role_wqewq'),(2612,7,'massmailing_unsubscribe_failed'),(2613,1,'massmailing'),(2615,7,'questions_section_user_photo_label'),(2616,7,'questions_section_captcha_label'),(2707,7,'questions_question_relationship_label'),(2620,1,'massmailing_following_variables_text'),(2850,7,'widgets_admin_customization_box_title'),(2626,1,'user_display_settings'),(2627,1,'user_avatar_settings'),(2628,1,'user_settings_updated'),(2629,1,'user_settings_avatar_size'),(2630,1,'user_settings_big_avatar_size'),(2632,1,'user_settings_display_name'),(2633,1,'user_settings_display_name_desc'),(2634,1,'user_settings_avatar_size_desc'),(2635,1,'user_settings_big_avatar_size_desc'),(2662,7,'user_unsuspend_btn_lbl'),(2657,7,'user_feedback_profile_suspended'),(2659,1,'user_feedback_profiles_suspended'),(2736,1,'pages_edit_local_page_content'),(2735,1,'pages_edit_local_visible_for'),(2743,1,'themes_settings_reset_label'),(2667,7,'widgets_admin_dashboard_heading'),(2668,7,'widgets_admin_profile_heading'),(2670,7,'join_successful_join'),(2671,7,'user_page_suspended'),(2673,7,'profile_view_title'),(2674,7,'profile_view_heading'),(2675,7,'my_profile_title'),(2676,1,'my_profile_heading'),(2677,1,'theme_settings_reset_confirm_message'),(2680,1,'theme_graphics_image_delete_confirm_message'),(2732,1,'pages_edit_local_menu_name'),(2784,1,'permissions_successfully_updated'),(2734,1,'pages_edit_local_page_url'),(2733,1,'pages_edit_local_page_title'),(2698,7,'questions_question_match_sex_label'),(2703,7,'questions_question_match_sex_value_2'),(2702,7,'questions_question_match_sex_value_1'),(2731,1,'pages_edit_external_visible_for'),(2834,1,'permissions_go_to_role_management_page'),(2727,1,'pages_edit_external_menu_name_label'),(2728,1,'pages_edit_external_url_label'),(2729,7,'comments_add_login_message'),(2730,1,'pages_edit_external_url_open_in_new_window'),(2723,7,'questions_question_relationship_value_1'),(2724,7,'questions_question_relationship_value_2'),(2725,7,'questions_question_relationship_value_4'),(2726,7,'questions_question_relationship_value_8'),(2737,1,'pages_edit_visible_for_guests'),(2738,1,'pages_edit_visible_for_members'),(2739,1,'pages_add_menu_name'),(2740,1,'pages_add_page_content'),(2741,1,'page_add_page_address'),(15519,7,'page_81959573'),(2747,1,'themes_settings_graphics_preview_cap_label'),(2748,7,'my_profile_heading'),(3016,7,'local_page_title_page-283934'),(2860,7,'component_add_new_box_cap_label'),(2783,1,'permissions_page_heading'),(2785,1,'permissions_feedback_user_not_found'),(2786,1,'permissions_feedback_moderator_added'),(2789,1,'permissions_feedback_user_kicked_from_moders'),(2788,1,'permissions_feedback_user_is_already_moderator'),(2790,1,'permissions_feedback_cant_remove_moder'),(2791,1,'permissions_index_who_can_join'),(2792,1,'permissions_index_anyone_can_join'),(2793,1,'permissions_index_by_invitation_only_can_join'),(2794,1,'permissions_index_who_can_invite'),(2795,1,'permissions_index_all_users_can_invate'),(2796,1,'permissions_index_admin_only_can_invate'),(2798,1,'permissions_index_mandatory_member_approve'),(2799,1,'permissions_index_moders_approve_members_manually'),(2800,1,'permissions_index_guests_can_view_site'),(2801,1,'permissions_index_yes'),(2802,1,'permissions_index_no'),(2804,1,'permissions_index_with_password'),(2805,1,'permissions_idex_if_not_yes_will_override_settings'),(2806,1,'permissions_index_save'),(2807,1,'permissions_moders_content'),(2809,1,'permissions_moders_add_moder'),(2810,1,'permissions_moders_username'),(2812,1,'permissions_moders_make_moderator'),(2815,7,'empty_list'),(2822,1,'sidebar_menu_item_plugin_mass_mailing'),(2823,7,'authorization_action_video_add_comment'),(2824,7,'authorization_action_video_delete_comment_by_content_owner'),(2825,1,'confirm_suspend_users'),(2827,7,'authorization_action_blogs_delete_comment_by_content_owner'),(2829,7,'users_browse_page_heading'),(2830,1,'manage_plugins_activate_success_message'),(2831,1,'manage_plugins_deactivate_success_message'),(2835,7,'authorization_failed_feedback'),(2843,7,'authorization_failed_msg'),(2844,7,'form_validator_required_error_message'),(2845,7,'comments_widget_label'),(2846,7,'avatar_widget'),(2847,7,'email_verify_subject'),(2868,7,'questions_question_d37d41b71a78dfb62b379d0aa7bd3ba5_value_1'),(2876,1,'languages_values_updated'),(2877,7,'user_no_users'),(2880,1,'site_email'),(2881,1,'site_email_desc'),(2884,7,'activity_widget_title'),(3011,1,'sidebar_menu_item_plugin_contact_importer'),(3012,7,'rate_cmp_auth_error_message'),(3021,7,'local_page_title_page-225003'),(3029,7,'page-728242'),(3018,7,'local_page_content_page-283934'),(2895,7,'month_1'),(2899,7,'user_feedback_marked_as_featured'),(2900,7,'user_feedback_unmarked_as_featured'),(2901,7,'user_action_mark_as_featured'),(2902,7,'user_action_unmark_as_featured'),(2903,7,'authorization_group_friends'),(2904,7,'authorization_action_friends_add_friend'),(2906,7,'avatar_select_image'),(2923,7,'change_password'),(2924,7,'change_password_old_password'),(2925,7,'change_password_new_password'),(2926,7,'change_password_repeat_password'),(2927,7,'change_password_success'),(2928,7,'change_password_error'),(2932,7,'avatar_activity_string'),(2934,7,'join_activity_string'),(2935,7,'join_activity_user_avatar'),(2936,7,'edit_activity_string'),(2958,7,'widgets_reset_position_confirm'),(5796,7,'authorization_group_membership'),(2993,7,'unsuspend_user_btn'),(2994,7,'user_feedback_profiles_unsuspended'),(2995,1,'user_feedback_profiles_unsuspended'),(3770,7,'flag_spam'),(3023,7,'local_page_content_page-225003'),(3025,7,'local_page_title_page-6940'),(3027,7,'local_page_content_page-6940'),(3030,7,'local_page_title_page-728242'),(3032,7,'local_page_content_page-728242'),(3037,7,'delete_user_cancel_button'),(3034,7,'local_page_title_page-366195'),(3036,7,'local_page_content_page-366195'),(3038,7,'delete_user_delete_button'),(3039,7,'delete_user_index'),(3040,7,'edit_profile_link'),(3041,7,'delete_profile'),(3045,7,'delete_user_confirmation'),(3046,7,'email_verify_verification_code_label'),(3050,7,'email_verify_invalid_verification_code'),(3048,7,'email_verify_verification_code_submit_button_label'),(3049,7,'email_verify_form_promo'),(3051,7,'questions_config_date_field_format_label'),(3052,7,'questions_config_date_field_format_mdy'),(3053,7,'questions_config_date_field_format_dmy'),(3054,1,'question_settings_updated'),(3056,7,'delete_user_content_label'),(3066,7,'local_page_title_page-775058'),(3068,7,'local_page_content_page-775058'),(3070,7,'local_page_title_page-312097'),(3072,7,'local_page_content_page-312097'),(3077,7,'profile_toolbar_user_delete_label'),(3078,7,'delete_user_confirmation_label'),(3079,7,'delete_user_success_message'),(3081,7,'admin_delete_user_text'),(3082,1,'mail_settings_updated'),(3083,1,'menu_item_mail_settings'),(3084,1,'mail_smtp_title_enabled'),(3085,1,'mail_smtp_title_enabled_desc'),(3086,1,'mail_smtp_title_host'),(3087,1,'mail_smtp_title_user'),(3088,1,'mail_smtp_title_password'),(3089,1,'mail_smtp_connection_prefix'),(3090,1,'mail_smtp_connection_prefix_desc'),(3092,1,'heading_mail_settings'),(3097,1,'mail_smtp_secure_invitation'),(3096,1,'mail_smtp_test_connection_title'),(3098,1,'mail_smtp_connection_desc'),(3099,1,'mail_smtp_test_connection_btn'),(3100,1,'smtp_test_connection_success'),(3101,7,'month_3'),(3102,7,'month_4'),(3104,7,'month_5'),(3105,7,'month_6'),(3106,7,'month_7'),(3107,7,'month_10'),(3108,7,'month_11'),(15498,7,'questions_question_55c95a36e50b0d7a795fb1caa8a8e520_description'),(3116,7,'questions_age_year_old'),(4661,1,'manage_plugins_core_update_request_box_cap_label'),(4662,1,'manage_plugins_core_update_request_text'),(3349,1,'sign_in_button_list_text'),(3256,1,'splash_screen_page_title'),(3257,1,'splash_screen_page_heading'),(3230,7,'join_form_title'),(3231,7,'join_connect_title'),(3232,7,'join_or'),(3352,7,'authorization_feedback_roles_updated'),(3235,1,'permission_global_privacy_settings_success_message'),(3236,7,'password_protection_cap_label'),(3237,7,'password_protection_text'),(3238,7,'password_protection_submit_label'),(3239,7,'password_protection_success_message'),(3240,7,'password_protection_error_message'),(3241,7,'paging_label_first'),(3242,7,'paging_label_prev'),(3243,7,'paging_label_next'),(3244,7,'paging_label_last'),(3246,1,'splash_intro_label'),(3247,1,'splash_intro_desc'),(3248,1,'splash_button_label'),(3249,1,'splash_button_label_desc'),(3250,1,'splash_leave_url_label'),(3251,1,'splash_leave_url_desc'),(3252,1,'splash_intro_value'),(3253,1,'splash_button_value'),(3261,1,'sidebar_menu_item_splash_screen'),(3258,1,'splash_screen_section_label'),(3259,1,'splash_screen_submit_success_message'),(3260,1,'splash_leave_button_label'),(3274,7,'join_not_valid_invite_code'),(3308,7,'authorization_user_roles'),(3283,1,'splash_enable_label'),(3284,1,'splash_enable_desc'),(3285,1,'invite_members_button_label'),(3286,1,'invite_members_cap_label'),(3287,1,'invite_members_textarea_invitation_text'),(3288,1,'invite_members_submit_label'),(3289,1,'invite_members_max_limit_message'),(3290,1,'invite_members_min_limit_message'),(3291,1,'invite_members_success_message'),(3292,7,'mail_template_admin_invite_user_subject'),(3293,7,'mail_template_admin_invite_user_content_html'),(3294,7,'mail_template_admin_invite_user_content_text'),(3309,7,'authorization_give_user_role'),(3345,7,'moderator_panel'),(3346,7,'approve_users'),(3347,7,'wait_for_approval'),(3348,1,'permissions_index_user_approve'),(3351,7,'profile_toolbar_user_approve_label'),(3353,7,'user_approved'),(3354,7,'user_approved_mail_subject'),(3355,7,'user_approved_mail_txt'),(3356,7,'user_approved_mail_html'),(3371,1,'sidebar_menu_item_maintenance'),(3372,1,'maintenance_page_heading'),(3373,1,'maintenance_section_label'),(3374,1,'maintenance_text_value'),(3379,1,'maintenance_enable_label'),(3380,1,'maintenance_enable_desc'),(3381,1,'maintenance_text_label'),(3382,1,'maintenance_text_desc'),(3383,1,'maintenance_submit_success_message'),(3391,7,'geolocation_country_name_ABW'),(3392,7,'geolocation_country_name_AFG'),(3393,7,'geolocation_country_name_AGO'),(3394,7,'geolocation_country_name_AIA'),(3395,7,'geolocation_country_name_ALB'),(3396,7,'geolocation_country_name_AND'),(3397,7,'geolocation_country_name_ANT'),(3398,7,'geolocation_country_name_ARE'),(3399,7,'geolocation_country_name_ARG'),(3400,7,'geolocation_country_name_ARM'),(3401,7,'geolocation_country_name_ASM'),(3402,7,'geolocation_country_name_ATA'),(3403,7,'geolocation_country_name_ATF'),(3404,7,'geolocation_country_name_ATG'),(3405,7,'geolocation_country_name_AUS'),(3406,7,'geolocation_country_name_AUT'),(3407,7,'geolocation_country_name_AZE'),(3408,7,'geolocation_country_name_BDI'),(3409,7,'geolocation_country_name_BEL'),(3410,7,'geolocation_country_name_BEN'),(3411,7,'geolocation_country_name_BFA'),(3412,7,'geolocation_country_name_BGD'),(3413,7,'geolocation_country_name_BGR'),(3414,7,'geolocation_country_name_BHR'),(3415,7,'geolocation_country_name_BHS'),(3416,7,'geolocation_country_name_BIH'),(3417,7,'geolocation_country_name_BLR'),(3418,7,'geolocation_country_name_BLZ'),(3419,7,'geolocation_country_name_BMU'),(3420,7,'geolocation_country_name_BOL'),(3421,7,'geolocation_country_name_BRA'),(3422,7,'geolocation_country_name_BRB'),(3423,7,'geolocation_country_name_BRN'),(3424,7,'geolocation_country_name_BTN'),(3425,7,'geolocation_country_name_BVT'),(3426,7,'geolocation_country_name_BWA'),(3427,7,'geolocation_country_name_CAF'),(3428,7,'geolocation_country_name_CAN'),(3429,7,'geolocation_country_name_CHE'),(3430,7,'geolocation_country_name_CHL'),(3431,7,'geolocation_country_name_CHN'),(3432,7,'geolocation_country_name_CIV'),(3433,7,'geolocation_country_name_CMR'),(3434,7,'geolocation_country_name_COD'),(3435,7,'geolocation_country_name_COG'),(3436,7,'geolocation_country_name_COK'),(3437,7,'geolocation_country_name_COL'),(3438,7,'geolocation_country_name_COM'),(3439,7,'geolocation_country_name_CPV'),(3440,7,'geolocation_country_name_CRI'),(3441,7,'geolocation_country_name_CUB'),(3442,7,'geolocation_country_name_CYM'),(3443,7,'geolocation_country_name_CYP'),(3444,7,'geolocation_country_name_CZE'),(3445,7,'geolocation_country_name_DEU'),(3446,7,'geolocation_country_name_DJI'),(3447,7,'geolocation_country_name_DMA'),(3448,7,'geolocation_country_name_DNK'),(3449,7,'geolocation_country_name_DOM'),(3450,7,'geolocation_country_name_DZA'),(3451,7,'geolocation_country_name_ECU'),(3452,7,'geolocation_country_name_EGY'),(3453,7,'geolocation_country_name_ERI'),(3454,7,'geolocation_country_name_ESP'),(3455,7,'geolocation_country_name_EST'),(3456,7,'geolocation_country_name_ETH'),(3457,7,'geolocation_country_name_FIN'),(3458,7,'geolocation_country_name_FJI'),(3459,7,'geolocation_country_name_FLK'),(3460,7,'geolocation_country_name_FRA'),(3461,7,'geolocation_country_name_FRO'),(3462,7,'geolocation_country_name_FSM'),(3463,7,'geolocation_country_name_GAB'),(3464,7,'geolocation_country_name_GBR'),(3465,7,'geolocation_country_name_GEO'),(3466,7,'geolocation_country_name_GGY'),(3467,7,'geolocation_country_name_GHA'),(3468,7,'geolocation_country_name_GIB'),(3469,7,'geolocation_country_name_GIN'),(3470,7,'geolocation_country_name_GLP'),(3471,7,'geolocation_country_name_GMB'),(3472,7,'geolocation_country_name_GNB'),(3473,7,'geolocation_country_name_GNQ'),(3474,7,'geolocation_country_name_GRC'),(3475,7,'geolocation_country_name_GRD'),(3476,7,'geolocation_country_name_GRL'),(3477,7,'geolocation_country_name_GTM'),(3478,7,'geolocation_country_name_GUF'),(3479,7,'geolocation_country_name_GUM'),(3480,7,'geolocation_country_name_GUY'),(3481,7,'geolocation_country_name_HKG'),(3482,7,'geolocation_country_name_HND'),(3483,7,'geolocation_country_name_HRV'),(3484,7,'geolocation_country_name_HTI'),(3485,7,'geolocation_country_name_HUN'),(3486,7,'geolocation_country_name_IDN'),(3487,7,'geolocation_country_name_IMN'),(3488,7,'geolocation_country_name_IND'),(3489,7,'geolocation_country_name_IOT'),(3490,7,'geolocation_country_name_IRL'),(3491,7,'geolocation_country_name_IRN'),(3492,7,'geolocation_country_name_IRQ'),(3493,7,'geolocation_country_name_ISL'),(3494,7,'geolocation_country_name_ISR'),(3495,7,'geolocation_country_name_ITA'),(3496,7,'geolocation_country_name_JAM'),(3497,7,'geolocation_country_name_JEY'),(3498,7,'geolocation_country_name_JOR'),(3499,7,'geolocation_country_name_JPN'),(3500,7,'geolocation_country_name_KAZ'),(3501,7,'geolocation_country_name_KEN'),(3502,7,'geolocation_country_name_KGZ'),(3503,7,'geolocation_country_name_KHM'),(3504,7,'geolocation_country_name_KIR'),(3505,7,'geolocation_country_name_KNA'),(3506,7,'geolocation_country_name_KOR'),(3507,7,'geolocation_country_name_KWT'),(3508,7,'geolocation_country_name_LAO'),(3509,7,'geolocation_country_name_LBN'),(3510,7,'geolocation_country_name_LBR'),(3511,7,'geolocation_country_name_LBY'),(3512,7,'geolocation_country_name_LCA'),(3513,7,'geolocation_country_name_LIE'),(3514,7,'geolocation_country_name_LKA'),(3515,7,'geolocation_country_name_LSO'),(3516,7,'geolocation_country_name_LTU'),(3517,7,'geolocation_country_name_LUX'),(3518,7,'geolocation_country_name_LVA'),(3519,7,'geolocation_country_name_MAC'),(3520,7,'geolocation_country_name_MAF'),(3521,7,'geolocation_country_name_MAR'),(3522,7,'geolocation_country_name_MCO'),(3523,7,'geolocation_country_name_MDA'),(3524,7,'geolocation_country_name_MDG'),(3525,7,'geolocation_country_name_MDV'),(3526,7,'geolocation_country_name_MEX'),(3527,7,'geolocation_country_name_MHL'),(3528,7,'geolocation_country_name_MKD'),(3529,7,'geolocation_country_name_MLI'),(3530,7,'geolocation_country_name_MLT'),(3531,7,'geolocation_country_name_MMR'),(3532,7,'geolocation_country_name_MNE'),(3533,7,'geolocation_country_name_MNG'),(3534,7,'geolocation_country_name_MNP'),(3535,7,'geolocation_country_name_MOZ'),(3536,7,'geolocation_country_name_MRT'),(3537,7,'geolocation_country_name_MSR'),(3538,7,'geolocation_country_name_MTQ'),(3539,7,'geolocation_country_name_MUS'),(3540,7,'geolocation_country_name_MWI'),(3541,7,'geolocation_country_name_MYS'),(3542,7,'geolocation_country_name_MYT'),(3543,7,'geolocation_country_name_NAM'),(3544,7,'geolocation_country_name_NCL'),(3545,7,'geolocation_country_name_NER'),(3546,7,'geolocation_country_name_NFK'),(3547,7,'geolocation_country_name_NGA'),(3548,7,'geolocation_country_name_NIC'),(3549,7,'geolocation_country_name_NIU'),(3550,7,'geolocation_country_name_NLD'),(3551,7,'geolocation_country_name_NOR'),(3552,7,'geolocation_country_name_NPL'),(3553,7,'geolocation_country_name_NRU'),(3554,7,'geolocation_country_name_NZL'),(3555,7,'geolocation_country_name_OMN'),(3556,7,'geolocation_country_name_PAK'),(3557,7,'geolocation_country_name_PAN'),(3558,7,'geolocation_country_name_PER'),(3559,7,'geolocation_country_name_PHL'),(3560,7,'geolocation_country_name_PLW'),(3561,7,'geolocation_country_name_PNG'),(3562,7,'geolocation_country_name_POL'),(3563,7,'geolocation_country_name_PRI'),(3564,7,'geolocation_country_name_PRK'),(3565,7,'geolocation_country_name_PRT'),(3566,7,'geolocation_country_name_PRY'),(3567,7,'geolocation_country_name_PSE'),(3568,7,'geolocation_country_name_PYF'),(3569,7,'geolocation_country_name_QAT'),(3570,7,'geolocation_country_name_REU'),(3571,7,'geolocation_country_name_ROM'),(3572,7,'geolocation_country_name_RUS'),(3573,7,'geolocation_country_name_RWA'),(3574,7,'geolocation_country_name_SAU'),(3575,7,'geolocation_country_name_SCG'),(3576,7,'geolocation_country_name_SDN'),(3577,7,'geolocation_country_name_SEN'),(3578,7,'geolocation_country_name_SGP'),(3579,7,'geolocation_country_name_SGS'),(3580,7,'geolocation_country_name_SLB'),(3581,7,'geolocation_country_name_SLE'),(3582,7,'geolocation_country_name_SLV'),(3583,7,'geolocation_country_name_SMR'),(3584,7,'geolocation_country_name_SOM'),(3585,7,'geolocation_country_name_SPM'),(3586,7,'geolocation_country_name_SRB'),(3587,7,'geolocation_country_name_STP'),(3588,7,'geolocation_country_name_SUR'),(3589,7,'geolocation_country_name_SVK'),(3590,7,'geolocation_country_name_SVN'),(3591,7,'geolocation_country_name_SWE'),(3592,7,'geolocation_country_name_SWZ'),(3593,7,'geolocation_country_name_SYC'),(3594,7,'geolocation_country_name_SYR'),(3595,7,'geolocation_country_name_TCA'),(3596,7,'geolocation_country_name_TCD'),(3597,7,'geolocation_country_name_TGO'),(3598,7,'geolocation_country_name_THA'),(3599,7,'geolocation_country_name_TJK'),(3600,7,'geolocation_country_name_TKL'),(3601,7,'geolocation_country_name_TKM'),(3602,7,'geolocation_country_name_TLS'),(3603,7,'geolocation_country_name_TON'),(3604,7,'geolocation_country_name_TTO'),(3605,7,'geolocation_country_name_TUN'),(3606,7,'geolocation_country_name_TUR'),(3607,7,'geolocation_country_name_TUV'),(3608,7,'geolocation_country_name_TWN'),(3609,7,'geolocation_country_name_TZA'),(3610,7,'geolocation_country_name_UGA'),(3611,7,'geolocation_country_name_UKR'),(3612,7,'geolocation_country_name_UMI'),(3613,7,'geolocation_country_name_URY'),(3614,7,'geolocation_country_name_USA'),(3615,7,'geolocation_country_name_UZB'),(3616,7,'geolocation_country_name_VAT'),(3617,7,'geolocation_country_name_VCT'),(3618,7,'geolocation_country_name_VEN'),(3619,7,'geolocation_country_name_VGB'),(3620,7,'geolocation_country_name_VIR'),(3621,7,'geolocation_country_name_VNM'),(3622,7,'geolocation_country_name_VUT'),(3623,7,'geolocation_country_name_WLF'),(3624,7,'geolocation_country_name_WSM'),(3625,7,'geolocation_country_name_YEM'),(3626,7,'geolocation_country_name_ZAF'),(3627,7,'geolocation_country_name_ZMB'),(3628,7,'geolocation_country_name_ZWE'),(3665,7,'unique_local_page_error'),(3790,7,'flags_users_reported'),(3791,7,'flags_deleted'),(3792,7,'dashboard_heading'),(3798,1,'sidebar_menu_item_plugin_forum'),(3808,1,'maintenance_page_title'),(3813,1,'menu_item_users_suspended'),(3814,1,'menu_item_users_unverified'),(3815,1,'menu_item_users_unapproved'),(3816,1,'user_status'),(3819,1,'user_status_unapproved'),(3817,1,'user_status_suspended'),(3818,1,'user_status_unverified'),(3735,1,'search_by_name'),(3771,7,'flag_offence'),(3772,7,'flag_illegal'),(3773,7,'flag_as'),(3774,7,'flag_flag'),(3776,7,'flag_accepted'),(3777,7,'flag_already_flagged'),(3823,1,'user_search_result'),(3820,7,'activity_stamp'),(3821,7,'activity_online'),(3843,7,'pages_add_submit'),(15466,1,'themes_add_new_box_cap_label'),(3828,7,'users'),(3829,7,'for_approval'),(3830,7,'flagged_content'),(3831,1,'heading_user_roles'),(3832,1,'heading_user_role'),(3833,1,'back_to_roles'),(3834,1,'sidebar_menu_item_permission'),(3835,1,'sidebar_menu_item_roles'),(3836,1,'sidebar_menu_item_permission_roles'),(3837,1,'sidebar_menu_item_permission_moders'),(3844,7,'pages_add_item'),(3845,7,'pages_back'),(3854,7,'forgot_password_success_message'),(3852,1,'theme_change_success_message'),(3851,7,'dashboard'),(3858,1,'sidebar_menu_plugins_installed'),(3859,1,'sidebar_menu_plugins_available'),(3860,1,'sidebar_menu_plugins_add'),(3861,1,'finance_settings'),(3862,1,'currency'),(3863,1,'manage_plugins_available_box_cap_label'),(3864,1,'manage_plugins_plugin_not_found'),(3865,1,'manage_plugins_delete_confirm_message'),(3866,1,'manage_plugins_delete_button_label'),(3867,1,'manage_plugins_uninstall_success_message'),(3868,1,'page_title_manage_plugins_ftp_info'),(3869,1,'manage_plugins_ftp_box_cap_label'),(3870,7,'upload'),(3871,1,'plugins_manage_ftp_form_login_label'),(3872,1,'plugins_manage_ftp_form_password_label'),(3873,1,'plugins_manage_ftp_form_submit_label'),(3874,1,'plugins_manage_need_ftp_attrs_message'),(3875,1,'plugins_manage_ftp_form_port_label'),(5840,7,'reset_password_request_submit_label'),(3878,1,'plugins_manage_ftp_attrs_invalid_host_message'),(4126,7,'user_list_menu_item_birthdays'),(3887,1,'plugins_manage_ftp_form_host_label'),(3888,7,'forgot_password_label'),(5839,7,'reset_password_request_form_text'),(3892,7,'sign_in_remember_me_label'),(5842,7,'reset_password_request_invalid_code_error_message'),(3902,1,'manage_plugins_delete_success_message'),(5125,1,'user_settings_email'),(5126,1,'user_settings_confirm_email'),(5124,1,'user_settings_confirm_email_desc'),(3918,1,'plugins_manage_add_submit_label'),(3920,1,'manage_plugins_add_box_cap_label'),(3921,7,'tf_allow_pics'),(3922,7,'tf_max_img_size'),(3923,7,'tf_sett_section'),(3924,7,'tf_img_url'),(3925,7,'tf_insert'),(3926,7,'tf_img_types'),(3927,7,'tf_img_max_size'),(3928,7,'tf_img_choose_file'),(3929,7,'tf_img_from_url'),(3930,7,'tf_img_gal'),(3931,7,'mp_gal_show'),(3932,7,'mp_gal_hide'),(3933,7,'mp_gal_pic_url'),(3934,7,'mp_gal_delete'),(3935,7,'mp_gal_preview'),(3936,7,'mp_gal_fullsize'),(3937,7,'mp_gal_align'),(3938,7,'mp_gal_none'),(3939,7,'mp_gal_left'),(3940,7,'mp_gal_center'),(3941,7,'mp_gal_right'),(3942,7,'mp_gal_ins_into_post'),(3943,1,'manage_plugins_update_button_label'),(3945,7,'billing_no_gateways'),(3950,1,'manage_plugins_update_request_box_cap_label'),(3951,1,'free_plugin_request_text'),(3952,1,'plugin_update_yes_button_label'),(3953,1,'plugin_update_no_button_label'),(3956,7,'billing_pay_with'),(5841,7,'reset_password_request_heading'),(3987,1,'plugins_manage_ftp_attrs_invalid_login_params_message'),(3988,7,'billing_currency_not_supported'),(4004,1,'com_plugin_request_key_label'),(4005,1,'license_form_leave_label'),(4006,1,'license_form_submit_label'),(4010,1,'com_plugin_request_text'),(4012,7,'date_time_week_0'),(4013,7,'date_time_week_1'),(4014,7,'date_time_week_2'),(4015,7,'date_time_week_3'),(4016,7,'date_time_week_4'),(4017,7,'date_time_week_5'),(4018,7,'date_time_week_6'),(4019,1,'plugins_manage_invalid_license_key_error_message'),(4020,7,'user_search_submit_button_label'),(4021,7,'user_search_display_name_search_label'),(4022,7,'user_search_page_heading'),(4032,7,'user_search_main_search_label'),(4034,7,'user_search_menu_item_label'),(4035,7,'user_search_back_to_search_from'),(15465,1,'sidebar_menu_themes_add'),(4038,7,'form_element_from'),(4039,7,'form_element_to'),(4040,7,'form_element_age_range'),(4043,7,'user_search_authorization_warning'),(4044,7,'authorization_action_base_search_users'),(4046,7,'rate_cmp_owner_cant_rate_error_message'),(4047,7,'comment_delete_confirm_message'),(4048,7,'year'),(4049,7,'month'),(4050,7,'day'),(4051,1,'permission_global_privacy_empty_pass_error_message'),(4078,1,'permission_global_privacy_pass_length_error_message'),(4087,1,'manage_plugins_uninstall_confirm_message'),(4090,7,'base_sign_in_cap_label'),(4091,7,'base_sign_in_or_label'),(4100,7,'confirm_page_ok_label'),(4101,7,'confirm_page_cancel_label'),(4102,7,'email_notifications_setting_user_comment'),(4103,7,'profile_comment_notification'),(5836,7,'forgot_password_code_exists_error_message'),(4128,1,'verify_site_email'),(4129,1,'send_verification_email'),(4130,1,'email_already_verify'),(4131,1,'site_email_verify_promo'),(4132,7,'site_email_verify_subject'),(4133,7,'site_email_verify_template_text'),(4134,7,'site_email_verify_template_html'),(4140,7,'date_time_tomorrow'),(4145,1,'manage_plugins_uninstall_request_box_cap_label'),(4146,1,'plugin_uninstall_request_text'),(4149,7,'form_validator_captcha_error_message'),(4165,7,'form_validator_float_error_message'),(4164,7,'form_validator_date_error_message'),(4166,7,'form_validator_int_error_message'),(4167,7,'form_validator_alphanumeric_error_message'),(4168,7,'form_validator_url_error_message'),(4169,7,'form_validator_email_error_message'),(4170,7,'form_validator_regexp_error_message'),(4171,7,'form_validator_string_error_message'),(4515,7,'console_item_label_preferences'),(4516,7,'console_item_label_profile'),(4514,7,'console_item_label_dashboard'),(4517,7,'console_item_label_mailbox'),(4518,7,'console_item_label_sign_out'),(4519,1,'notification_soft_update'),(4520,1,'notification_plugins_to_update'),(5838,7,'reset_password_request_cap_label'),(4595,1,'manage_plugins_uninstall_error_message'),(4598,7,'media_panel_file_deleted'),(4939,1,'manage_plugins_update_success_message'),(4766,7,'my_avatar_widget'),(4767,1,'page_title_finance'),(4770,1,'sidebar_menu_item_dashboard_finance'),(4769,7,'join_error_username_restricted'),(5843,7,'reset_password_heading'),(4772,1,'sidebar_menu_item_restricted_usernames'),(4773,1,'restrictedusernames'),(4774,1,'restrictedusernames_add_username'),(4775,1,'restrictedusernames_username_label'),(4776,1,'restrictedusernames_add_username_button'),(4777,1,'restrictedusernames_restricted_list_label'),(4783,1,'restrictedusernames_username_added'),(4785,1,'manage_plugins_manual_update_request'),(4786,1,'plugin_manual_update_button_label'),(4793,7,'billing_gateway_not_found'),(4832,1,'plugin'),(4810,7,'billing_sale_not_found'),(5853,7,'reset_password_success_message'),(4843,7,'billing_amount'),(4842,7,'time'),(4811,7,'billing_order_init_failed'),(5852,7,'reset_password_length_error_message'),(5851,7,'reset_password_not_equal_error_message'),(4854,7,'billing_details'),(4855,7,'billing_gateway'),(4856,7,'billing_transaction_id'),(4857,7,'billing_statistics'),(4868,1,'questions_config_year_range_label'),(4869,7,'form_element_year_range'),(4870,7,'billing_gateway_unavailable'),(4871,7,'billing_order_canceled'),(4872,7,'billing_order_failed'),(4873,7,'billing_order_completed_successfully'),(4874,7,'billing_order_verified'),(4875,7,'billing_order_processing'),(4906,7,'billing_order_page_heading'),(4907,7,'billing_order_status_page_heading'),(5849,7,'reset_password_repeat_field_label'),(5848,7,'reset_password_field_label'),(5846,7,'reset_password_form_text'),(5845,7,'forgot_password_instructions'),(5844,7,'reset_password_cap_label'),(5576,1,'massmailing_expire_text'),(6079,7,'questions_question_7f2450f06779439551c75a8566c4070e_value_1'),(6073,7,'authorization_action_market_post_jobs'),(5847,7,'reset_password_submit_label'),(6051,7,'reset_password_mail_template_content_txt'),(6050,7,'reset_password_mail_template_content_html'),(6048,7,'authorization_action_market_delete_comment_by_content_owner'),(6046,7,'authorization_group_market'),(6045,1,'questions_edit_username_warning'),(6082,7,'questions_question_7f2450f06779439551c75a8566c4070e_value_8'),(6081,7,'questions_question_7f2450f06779439551c75a8566c4070e_value_4'),(6080,7,'questions_question_7f2450f06779439551c75a8566c4070e_value_2'),(6047,7,'authorization_action_market_add_comment'),(6020,7,'questions_question_c5dc53f371fe6ba3001a7c7e31bd95fc_value_1'),(6021,7,'questions_question_c5dc53f371fe6ba3001a7c7e31bd95fc_value_2'),(6022,7,'questions_question_c5dc53f371fe6ba3001a7c7e31bd95fc_value_4'),(6023,7,'questions_question_c5dc53f371fe6ba3001a7c7e31bd95fc_value_8'),(6024,7,'questions_question_c5dc53f371fe6ba3001a7c7e31bd95fc_value_16'),(6083,7,'questions_question_7f2450f06779439551c75a8566c4070e_value_16'),(6084,7,'questions_question_7f2450f06779439551c75a8566c4070e_value_32'),(6155,7,'authorization_group_links'),(6156,7,'authorization_action_links_add_comment'),(6157,7,'authorization_action_links_add'),(6158,7,'authorization_action_links_delete_comment_by_content_owner'),(6159,7,'authorization_action_links_view'),(6377,1,'css_edit_success_message'),(6378,7,'comment_view_all'),(6406,7,'ajax_floatbox_users_title'),(6697,7,'feed_user_join'),(6696,7,'feed_user_edit_profile'),(6275,7,'cannot_delete_admin_user_msg'),(6301,7,'white_spaces_dissalowed'),(6308,1,'user_feedback_email_verified'),(6306,7,'mark_email_verified_btn'),(9251,7,'tag_search'),(6366,1,'user_settings_avatar_image'),(8574,7,'authorization_action_virtualgifts_send_gift'),(8573,7,'authorization_group_virtualgifts'),(6376,1,'plugins_manage_ftp_attrs_invalid_user'),(6473,1,'user_settings_avatar_image_desc'),(6477,7,'change'),(6478,7,'cancel'),(6486,1,'confirm_avatar_delete'),(6487,1,'default_avatar_deleted'),(6489,7,'empty_user_avatar_list'),(6497,7,'avatar_user_list_select_count_label'),(6498,7,'avatar_user_list_select_button_label'),(6516,7,'user_page_heading_status'),(7918,7,'authorization_action_event_add_comment'),(7915,7,'authorization_group_event'),(6568,7,'base_document_403_heading'),(6569,7,'base_document_403'),(6570,7,'base_document_404_title'),(6571,7,'base_document_403_title'),(7917,7,'authorization_action_event_view_event'),(7916,7,'authorization_action_event_add_event'),(9493,7,'form_element_submit_default_value'),(7429,7,'empty_user_avatar_list_select'),(9728,7,'questions_question_a5115de7f38988e748370a59ba0b311d_value_8'),(9727,7,'questions_question_a5115de7f38988e748370a59ba0b311d_value_4'),(9726,7,'questions_question_a5115de7f38988e748370a59ba0b311d_value_2'),(9725,7,'questions_question_a5115de7f38988e748370a59ba0b311d_value_1'),(8664,7,'authorization_group_usercredits'),(6671,7,'avatar_feed_string'),(6673,7,'widgets_enable_js'),(6674,7,'widgets_disable_js'),(6675,7,'reset_password_code_expired_cap_label'),(6677,7,'reset_password_code_expired_text'),(6678,1,'total_users'),(6714,7,'avatar_user_select_empty_list_message'),(6779,7,'authorization_group_mailbox'),(6780,7,'authorization_action_mailbox_read_message'),(6781,7,'authorization_action_mailbox_send_message'),(6823,7,'manage_plugins_add_success_message'),(9509,7,'questions_question_7fbd88047415229961f4d2aac620fe25_value_8'),(9508,7,'questions_question_7fbd88047415229961f4d2aac620fe25_value_4'),(9507,7,'questions_question_7fbd88047415229961f4d2aac620fe25_value_2'),(9506,7,'questions_question_7fbd88047415229961f4d2aac620fe25_value_1'),(6861,7,'date_time_cap_hour'),(6862,7,'date_time_cap_minute'),(9510,7,'questions_question_7fbd88047415229961f4d2aac620fe25_value_16'),(9495,7,'user_list_widget_settings_count'),(9494,7,'user_list_widget_settings_title'),(9411,7,'user_dash_widget_settings_title'),(6890,1,'plugins_manage_no_available_items'),(7218,7,'manage_plugins_install_empty_key_error_message'),(7219,7,'manage_plugins_install_error_message'),(7302,1,'add_language_pack_empty_file_error_message'),(7304,1,'language_import_complete_success_message'),(7528,7,'questions_question_joinStamp_label'),(7828,1,'warning_cron_is_not_active'),(8987,7,'authorization_group_forum'),(8988,7,'authorization_action_forum_view'),(8989,7,'authorization_action_forum_edit'),(8990,7,'authorization_action_forum_subscribe'),(8993,1,'avatar_label'),(8994,7,'yes'),(8995,7,'no'),(8996,1,'display_avatar_label'),(8997,1,'avatar_label_color'),(8999,1,'permissions_role_updated'),(9106,7,'checkout'),(9129,1,'permissions_role_actions_label'),(9133,1,'sidebar_menu_item_users_roles'),(15464,1,'manage_theme_add_extract_error'),(16076,7,'ignore'),(9173,7,'usercredits_action_daily_login'),(9174,7,'usercredits_action_user_join'),(9175,7,'usercredits_action_search_users'),(9176,7,'usercredits_action_add_comment'),(9208,7,'your_rate_label'),(9209,7,'total_score_label'),(10306,7,'local_page_title_page_13904610'),(9285,1,'add_new_role_block_cap_label'),(9286,1,'user_role_permissions_cap_label'),(9297,1,'questions_edit_account_types_button'),(9291,7,'add_comment'),(9293,1,'questions_values_count'),(9294,7,'delete_comment_by_content_owner'),(9295,7,'search_users'),(9296,1,'auth_group_label'),(9375,1,'question_column_question'),(9377,1,'question_column_type'),(9381,1,'theme_info_cap_label'),(9382,1,'question_column_values'),(9386,1,'pages_and_menus_instructions'),(9387,1,'pages_and_menus_main_menu_label'),(9388,1,'question_column_require'),(9389,1,'pages_and_menus_bottom_menu_label'),(9390,1,'pages_and_menus_hidden_pages_label'),(9391,1,'question_column_sign_up'),(9392,1,'pages_and_menus_hidden_desc'),(9393,1,'question_column_profile_edit'),(9395,1,'question_column_view'),(9396,1,'pages_and_menus_item_label'),(9397,1,'pages_and_menus_legend_everyone_label'),(9398,1,'question_column_search'),(9399,1,'pages_and_menus_legend_guests_label'),(9400,1,'pages_and_menus_legend_members_label'),(9401,7,'auth_view_profile'),(9402,1,'pages_and_menus_legend_label'),(9403,1,'or'),(9404,1,'question_column_account_type'),(9405,1,'question_column_exclusive_questions'),(9406,7,'view_profile_no_permission'),(18462,1,'questions_admin_add_new_values'),(9417,7,'tag_search_empty_value_error'),(9423,7,'form_element_submit_default_balue'),(9462,7,'notification_section_label'),(9469,7,'user_list_activity'),(9467,7,'user_list_online'),(9472,1,'soft_version'),(9511,7,'questions_question_7fbd88047415229961f4d2aac620fe25_value_32'),(9512,7,'questions_question_7fbd88047415229961f4d2aac620fe25_value_64'),(9522,1,'clone_language_cap_label'),(9528,1,'join_display_photo_upload'),(9530,1,'join_display_terms_of_use'),(9531,1,'join_page'),(9532,1,'join_display_terms_of_use_desc'),(9534,1,'join_display_photo_upload_desc'),(9542,7,'local_page_title_page-119658'),(16385,1,'account_type_sort_order'),(9541,7,'page-119658'),(9539,7,'questions_section_terms_of_use_label'),(9540,7,'questions_question_user_terms_of_use_label'),(9544,7,'local_page_content_page-119658'),(9913,1,'language_edit_form_error_message'),(9912,1,'language_edit_form_success_message'),(9567,1,'use_military_time'),(9568,1,'sidebar_menu_item_permission_role'),(9683,7,'preference_index'),(9694,7,'config_join_display_photo_upload_display_and_require_label'),(9693,7,'config_join_display_photo_upload_display_label'),(9686,7,'preference_submit_button'),(9687,7,'preference_section_general'),(9688,7,'preference_preference_data_was_saved'),(9689,7,'preference_preference_data_not_changed'),(9690,7,'preference_no_items'),(9691,7,'preference_mass_mailing_subscribe_label'),(9692,7,'preference_mass_mailing_subscribe_description'),(9695,7,'config_join_display_photo_upload_not_display_label'),(9911,1,'lang_edit_form_rtl_desc'),(9910,1,'lang_edit_form_rtl_label'),(9909,1,'edit_langs_cap_label'),(9908,1,'btn_label_edit_values'),(9870,7,'privacy_action_view_profile'),(9871,1,'core_update_download_error'),(9872,1,'sidebar_menu_item_user_settings'),(9874,1,'menu_item_page_settings'),(9875,1,'heading_page_settings'),(9879,1,'page_settings_form_headcode_label'),(9877,1,'settings_submit_error_message'),(9878,1,'settings_submit_success_message'),(9880,1,'page_settings_form_headcode_desc'),(9881,1,'page_settings_form_bottomcode_label'),(9882,1,'page_settings_form_bottomcode_desc'),(9883,1,'page_settings_form_favicon_label'),(9884,1,'page_settings_form_favicon_desc'),(10253,7,'view_more_label'),(10266,7,'ws_button_label_bold'),(10267,7,'ws_button_label_italic'),(10268,7,'ws_button_label_underline'),(10269,7,'ws_button_label_orderedlist'),(10270,7,'ws_button_label_unorderedlist'),(10271,7,'ws_button_label_link'),(10272,7,'ws_button_label_image'),(10273,7,'ws_button_label_video'),(10274,7,'ws_button_label_html'),(10275,7,'ws_button_label_switch_html'),(10282,7,'ws_add_label'),(10283,7,'ws_insert_label'),(10284,7,'ws_link_empty_fields'),(10285,7,'ws_video_head_label'),(10287,7,'ws_video_empty_field'),(10288,7,'ws_html_head_label'),(10289,7,'welcome_letter_subject'),(18575,1,'admin_suspend_floatbox_title'),(18576,1,'input_settings_avatar_max_upload_size_label'),(10308,7,'local_page_content_page_13904610'),(10578,7,'ws_button_label_more'),(10580,1,'feed_content_registration'),(10581,1,'feed_content_edit'),(10689,7,'authorization_action_groups_add_topic'),(10688,7,'authorization_action_groups_add_comment'),(10687,7,'forgot_password_mail_template_content_txt'),(10601,1,'menu_item_user_input_settings'),(10602,1,'user_input_settings_user_content'),(10605,1,'user_input_settings_comments'),(10607,1,'user_input_settings_rich_media'),(18660,7,'form_validator_range_error_message'),(10609,1,'input_settings_max_upload_size_label'),(10610,1,'input_settings_resource_list_label'),(10611,1,'heading_user_input_settings'),(10612,7,'ws_html_textarea_label'),(10613,7,'ws_video_textarea_label'),(10614,1,'feed_content_user_comment'),(15470,1,'theme_add_extract_error'),(10618,1,'menu_item_user_settings_general'),(10619,1,'menu_item_user_settings_content_input'),(10666,1,'input_settings_user_rich_media_disable_label'),(10665,1,'input_settings_user_custom_html_disable_desc'),(10664,1,'input_settings_user_custom_html_disable_label'),(10623,1,'input_settings_resource_list_desc'),(10626,7,'privacy_action_view_my_presence_on_site'),(10667,1,'input_settings_user_rich_media_disable_desc'),(10668,1,'input_settings_comments_rich_media_disable_label'),(10669,1,'input_settings_comments_rich_media_disable_desc'),(10685,1,'menu_item_user_settings'),(10686,7,'forgot_password_mail_template_content_html'),(10676,1,'page_settings_favicon_submit_error_message'),(10690,7,'authorization_action_groups_create'),(10678,7,'local_page_title_page_7680520'),(10679,7,'local_page_meta_tags_page_7680520'),(10680,7,'local_page_content_page_7680520'),(10691,7,'authorization_action_groups_delete_comment_by_content_owner'),(10692,7,'authorization_group_groups'),(16065,7,'accept'),(10725,1,'join_photo_upload_set_required'),(10726,1,'join_photo_upload_set_required_desc'),(10727,7,'local_page_content_page-290357'),(10729,7,'local_page_title_page-290357'),(10730,7,'page-290357'),(10731,7,'local_page_content_page_24622628'),(10733,7,'local_page_content_page_88027910'),(10734,7,'local_page_meta_tags_page_24622628'),(10737,7,'local_page_title_page_24622628'),(10738,7,'local_page_title_page_45248787'),(10739,7,'local_page_title_page_88027910'),(10740,7,'page_13904610'),(10741,7,'page_24622628'),(10742,7,'page_45248787'),(10743,7,'page_88027910'),(11634,7,'questions_section_e46697c921740b10b1ac7223a14155b2_label'),(11631,1,'manage_plugins_add_ftp_move_error'),(10835,1,'found_users'),(10836,1,'search_by'),(10839,7,'questions_question_d8aa20d67fbb6c6864e46c474d0bde10_value_1'),(10923,7,'attch_video_add_button_label'),(10924,7,'attch_video_button_label'),(10925,7,'attch_photo_button_label'),(10926,7,'attch_add_video_button_label'),(10927,7,'attch_attachment_label'),(10928,7,'upload_file_max_upload_filesize_error'),(10929,7,'upload_file_file_partially_uploaded_error'),(10930,7,'upload_file_no_file_error'),(10931,7,'upload_file_no_tmp_dir_error'),(10932,7,'upload_file_cant_write_file_error'),(10933,7,'upload_file_invalid_extention_error'),(10934,7,'upload_file_fail'),(10940,7,'questions_question_d8aa20d67fbb6c6864e46c474d0bde10_value_4'),(10939,7,'questions_question_d8aa20d67fbb6c6864e46c474d0bde10_value_2'),(10941,7,'questions_question_d8aa20d67fbb6c6864e46c474d0bde10_value_8'),(10942,7,'questions_question_d8aa20d67fbb6c6864e46c474d0bde10_value_16'),(10943,7,'questions_question_d8aa20d67fbb6c6864e46c474d0bde10_value_32'),(10944,7,'questions_question_d8aa20d67fbb6c6864e46c474d0bde10_value_64'),(10945,7,'questions_question_d8aa20d67fbb6c6864e46c474d0bde10_value_128'),(10946,7,'questions_question_d8aa20d67fbb6c6864e46c474d0bde10_value_256'),(10947,7,'questions_question_d8aa20d67fbb6c6864e46c474d0bde10_value_512'),(11296,7,'comment_required_validator_message'),(11310,7,'sidebar_menu_view_all_questions'),(11311,1,'sidebar_menu_question_settings'),(11312,1,'sidebar_menu_basic_label'),(11313,1,'sidebar_menu_theme_css_label'),(11314,1,'sidebar_menu_pages_menus_label'),(11317,7,'widgets_avaliable_legend'),(11318,7,'widgets_avaliable_description'),(11573,1,'core_update_unzip_error'),(11574,7,'ws_link_text_label'),(11579,1,'page_settings_no_favicon_label'),(11577,7,'ws_link_url_label'),(11578,7,'ws_link_new_window_label'),(11635,7,'max_upload_filesize'),(15361,1,'massmailing_user_roles_label'),(15362,1,'settings_max_upload_size_error'),(15363,1,'user_feedback_email_unverified'),(15364,7,'authorization_action_forum_move_topic_to_hidden'),(15365,7,'authorization_role_gold'),(15366,7,'authorization_role_silver'),(15367,7,'authorization_role_silver1'),(15368,7,'billing_gateway_products_binding'),(15369,7,'billing_product'),(15370,7,'billing_product_id'),(15371,7,'block_user_confirmation_label'),(15372,7,'cancel_button'),(15373,7,'confirm_button'),(15374,7,'local_page_content_page_10919570'),(15375,7,'local_page_content_page_61525903'),(15376,7,'local_page_meta_tags_page_10919570'),(15377,7,'local_page_meta_tags_page_61525903'),(15378,7,'local_page_title_page_10919570'),(15379,7,'local_page_title_page_61525903'),(15380,7,'maintenance_disable_message'),(15381,7,'mark_email_unverified_btn'),(15382,7,'massmailing_unsubscribe_confirmation'),(15383,7,'page_10919570'),(15385,7,'questions_account_type_77f92557420351da5a45e37a13857657'),(15386,7,'questions_account_type_db5c851569c8660abb5ad2441d38d3fc'),(15387,7,'questions_account_type_df9807bd2ba1808afb2ab52a7b74fc90'),(15388,7,'questions_question_06c1ce07a38cb0267e45ec955ed84a6f_description'),(15389,7,'questions_question_06c1ce07a38cb0267e45ec955ed84a6f_label'),(15390,7,'questions_question_2be5f9666f2a291d33705d58a902d84a_description'),(15391,7,'questions_question_2be5f9666f2a291d33705d58a902d84a_label'),(15392,7,'questions_question_739a83996f701966485290779f4ff2b5_description'),(15393,7,'questions_question_739a83996f701966485290779f4ff2b5_label'),(15394,7,'questions_question_739a83996f701966485290779f4ff2b5_value_1'),(15395,7,'questions_question_739a83996f701966485290779f4ff2b5_value_1024'),(15396,7,'questions_question_739a83996f701966485290779f4ff2b5_value_128'),(15397,7,'questions_question_739a83996f701966485290779f4ff2b5_value_16'),(15398,7,'questions_question_739a83996f701966485290779f4ff2b5_value_16384'),(15399,7,'questions_question_739a83996f701966485290779f4ff2b5_value_2'),(15400,7,'questions_question_739a83996f701966485290779f4ff2b5_value_2048'),(15401,7,'questions_question_739a83996f701966485290779f4ff2b5_value_256'),(15402,7,'questions_question_739a83996f701966485290779f4ff2b5_value_32'),(15403,7,'questions_question_739a83996f701966485290779f4ff2b5_value_32768'),(15404,7,'questions_question_739a83996f701966485290779f4ff2b5_value_4'),(15405,7,'questions_question_739a83996f701966485290779f4ff2b5_value_4096'),(15406,7,'questions_question_739a83996f701966485290779f4ff2b5_value_512'),(15407,7,'questions_question_739a83996f701966485290779f4ff2b5_value_64'),(15408,7,'questions_question_739a83996f701966485290779f4ff2b5_value_8'),(15409,7,'questions_question_739a83996f701966485290779f4ff2b5_value_8192'),(15411,7,'questions_question_c5dc53f371fe6ba3001a7c7e31bd95fc_label'),(15413,7,'questions_question_d8aa20d67fbb6c6864e46c474d0bde10_label'),(15414,7,'questions_question_ecef9fe729d11241c04609c3a7ca468e_description'),(15415,7,'questions_question_ecef9fe729d11241c04609c3a7ca468e_label'),(15416,7,'user_block_btn_lbl'),(15417,7,'user_block_confirm_message'),(15418,7,'user_block_message'),(15419,7,'user_feedback_profile_blocked'),(15420,7,'user_feedback_profile_unblocked'),(15421,7,'user_unblock_btn_lbl'),(15473,1,'manage_plugins_add_empty_field_error_message'),(15475,1,'manage_plugins_update_process_error'),(15476,1,'manage_plugins_up_to_date_message'),(15477,1,'manage_plugin_add_extract_error'),(15478,1,'plugin_update_download_error'),(15479,1,'plugin_update_request_error'),(15480,7,'forgot_password_request_exists_error_message'),(15481,1,'user_feedback_profiles_approved'),(15482,1,'user_feedback_profiles_disapproved'),(15483,1,'user_settings_avatar_size_error'),(15484,1,'user_settings_avatar_size_label'),(15485,1,'user_settings_big_avatar_size_error'),(15486,1,'user_settings_big_avatar_size_label'),(15487,1,'warning_url_fopen_disabled'),(15488,7,'ajax_attachment_select_image'),(15489,7,'ajax_attachment_select_image_title'),(15490,7,'approve_user_btn'),(15491,7,'disapprove_user_btn'),(15492,7,'feed_activity_avatar_string'),(15493,7,'feed_activity_avatar_string_like'),(15494,7,'feed_activity_join_profile_string'),(15495,7,'feed_activity_join_profile_string_like'),(15496,7,'not_writable_avatar_dir'),(15497,7,'upload_file_extension_is_not_allowed'),(15500,7,'questions_question_55c95a36e50b0d7a795fb1caa8a8e520_value_1'),(15501,7,'questions_question_55c95a36e50b0d7a795fb1caa8a8e520_value_2'),(15502,7,'questions_question_55c95a36e50b0d7a795fb1caa8a8e520_value_4'),(15503,7,'questions_question_7747d430cc0be5d22223baf1082b34c9_description'),(15504,7,'questions_question_7747d430cc0be5d22223baf1082b34c9_label'),(15505,7,'questions_question_7747d430cc0be5d22223baf1082b34c9_value_1'),(15506,7,'questions_question_7747d430cc0be5d22223baf1082b34c9_value_2'),(15507,7,'questions_question_7747d430cc0be5d22223baf1082b34c9_value_4'),(15508,7,'questions_question_dc15d35b1be0e31b4c69bdaddb5298ff_description'),(15509,7,'questions_question_dc15d35b1be0e31b4c69bdaddb5298ff_label'),(15590,1,'questions_account_type_has_exclusive_questions'),(15588,1,'cron_configuration_required_notice'),(15589,1,'manage_plugins_install_error_message'),(15513,7,'questions_question_presentation_range_label'),(15515,7,'quick_links_cap_label'),(15516,7,'sort_control_sortby'),(15517,7,'tags_cloud_cap_label'),(15518,7,'user_deleted_message'),(15520,7,'local_page_title_page_81959573'),(15522,7,'local_page_content_page_81959573'),(15523,1,'console_item_admin_dashboard'),(15524,1,'console_item_manage_pages'),(15525,1,'console_item_manage_plugins'),(15526,1,'console_item_manage_theme'),(15527,1,'console_item_manage_users'),(15528,1,'feed_content_avatar_change'),(15529,1,'language_activated'),(15530,1,'language_deactivated'),(15531,1,'language_deleted'),(15532,1,'msg_lang_clone_failed'),(15533,1,'msg_lang_invalid_language_tag'),(15534,1,'restrictedusernames_username_already_exists'),(15535,1,'restrictedusernames_username_deleted'),(15536,7,'authorization_role_admin'),(15537,7,'auth_action_add_comment'),(15538,7,'base_invisible_profile_field_tooltip'),(15539,7,'base_welcome_promo_theme'),(15540,7,'cannot_delete_admin_user_message'),(15541,7,'console_item_invitations_label'),(15542,7,'console_item_sign_up_label'),(15543,7,'contex_action_comment_delete_label'),(15544,7,'contex_action_user_delete_label'),(15545,7,'custom_html_widget_no_content'),(15546,7,'deleted_user'),(15547,7,'flag_own_content_not_accepted'),(15550,7,'local_page_content_page_65118983'),(15551,7,'local_page_meta_tags_page_19271127'),(15554,7,'local_page_title_page_19271127'),(15555,7,'local_page_title_page_58148315'),(15556,7,'local_page_title_page_65118983'),(15557,7,'pages_wrong_local_url'),(15558,7,'page_19271127'),(15559,7,'page_3784014'),(15560,7,'page_58148315'),(15561,7,'page_65118983'),(15562,7,'preference_menu_item'),(15563,7,'questions_account_type_7f23216f76dfbd9e7f6dacd58ea58beb'),(15564,7,'questions_question_44e544653804655d52235ac14aaa10fb_description'),(15565,7,'questions_question_44e544653804655d52235ac14aaa10fb_label'),(15566,7,'questions_section_a9073700994b20e339e21d061c44e5b0_label'),(15568,7,'text_is_too_long'),(15569,7,'user_deleted_page_message'),(15570,7,'user_list_chat_now'),(15591,1,'questions_cant_delete_default_account_type'),(15592,7,'comment_form_element_invitation_text'),(15593,7,'page_57271912'),(15594,7,'profile_toolbar_group_moderation'),(15595,7,'questions_account_type_8c103538900bda26f08d9ccd2aad61f8'),(15596,7,'questions_question_07f4c1294598ea15ae43fafdefbc6919_description'),(15597,7,'questions_question_07f4c1294598ea15ae43fafdefbc6919_label'),(15598,7,'questions_question_07f4c1294598ea15ae43fafdefbc6919_value_1'),(15599,7,'questions_question_07f4c1294598ea15ae43fafdefbc6919_value_2'),(15600,7,'questions_question_07f4c1294598ea15ae43fafdefbc6919_value_4'),(15601,7,'questions_question_2e9938cadc1786aa6ad39edea8d00ddf_description'),(15602,7,'questions_question_2e9938cadc1786aa6ad39edea8d00ddf_label'),(15604,7,'questions_question_googlemap_location_label'),(15605,7,'questions_question_presentation_age_label'),(15606,7,'questions_question_presentation_birthdate_label'),(15607,7,'questions_question_presentation_radio_label'),(15608,7,'questions_question_presentation_select_label'),(15609,7,'questions_section_location_label'),(15610,7,'tags_input_field_invitation'),(15611,7,'widgets_default_settings_access_restrictions'),(15612,7,'widgets_default_settings_restrict_view'),(15613,1,'back_to_theme_list'),(15614,1,'com_theme_request_name_label'),(15615,1,'com_theme_request_text'),(15616,1,'delete_content_warning'),(15617,1,'free_theme_request_text'),(15618,1,'manage_themes_theme_not_found'),(15619,1,'manage_themes_update_process_error'),(15620,1,'manage_themes_update_request_box_cap_label'),(15621,1,'manage_themes_update_success_message'),(15622,1,'manage_themes_up_to_date_message'),(15624,1,'questions_delete_question_parent_confirmation'),(16386,1,'account_type_translation'),(15627,1,'themes_manage_invalid_license_key_error_message'),(15628,1,'theme_update_download_error'),(15629,1,'theme_update_not_available_error'),(15630,1,'theme_update_request_error'),(15636,7,'authorization_role_ekip_yesi'),(15637,7,'authorization_role_ewrwe'),(15641,7,'authorization_role_ms_test'),(15644,7,'desktop_version_menu_item'),(15646,7,'index_menu_item'),(15647,7,'local_page_content_page_31935233'),(15648,7,'local_page_content_page_61962499'),(15649,7,'local_page_content_page_80273168'),(15650,7,'local_page_content_test'),(15651,7,'local_page_content_test2'),(15653,7,'local_page_meta_tags_page_61962499'),(15654,7,'local_page_meta_tags_page_80273168'),(15655,7,'local_page_meta_tags_test'),(15656,7,'local_page_meta_tags_test2'),(15657,7,'local_page_title_page_31935233'),(15658,7,'local_page_title_page_61962499'),(15659,7,'local_page_title_page_80273168'),(15660,7,'local_page_title_test'),(15661,7,'local_page_title_test2'),(15664,7,'page_38157820'),(15665,7,'page_61962499'),(15666,7,'page_73405194'),(15667,7,'page_80273168'),(15668,7,'profile_view_description'),(15670,7,'questions_question_0fe69011e890106732951b49d131490c_description'),(15671,7,'questions_question_0fe69011e890106732951b49d131490c_label'),(15672,7,'questions_question_0fe69011e890106732951b49d131490c_value_1'),(15673,7,'questions_question_0fe69011e890106732951b49d131490c_value_2'),(15674,7,'questions_question_0fe69011e890106732951b49d131490c_value_4'),(15675,7,'questions_question_0fe69011e890106732951b49d131490c_value_8'),(15677,7,'questions_question_1fedc27c0e003f16fb8ab66d29727451_label'),(15678,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_description'),(15679,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_label'),(15680,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_1'),(15681,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_1024'),(15682,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_1048576'),(15683,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_1073741824'),(15684,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_128'),(15685,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_131072'),(15686,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_134217728'),(15687,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_16'),(15688,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_16384'),(15689,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_16777216'),(15690,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_2'),(15691,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_2048'),(15692,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_2097152'),(15693,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_256'),(15694,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_262144'),(15695,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_268435456'),(15696,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_32'),(15697,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_32768'),(15698,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_33554432'),(15699,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_4'),(15700,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_4096'),(15701,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_4194304'),(15702,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_512'),(15703,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_524288'),(15704,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_536870912'),(15705,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_64'),(15706,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_65536'),(15707,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_67108864'),(15708,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_8'),(15709,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_8192'),(15710,7,'questions_question_36e39f917cbcf4fb10aa753186ced60d_value_8388608'),(15713,7,'questions_question_557256153cd10f5f711ac4ee14a7b675_value_1'),(15714,7,'questions_question_557256153cd10f5f711ac4ee14a7b675_value_2'),(15715,7,'questions_question_557256153cd10f5f711ac4ee14a7b675_value_4'),(15719,7,'questions_question_5eab269060488cd69d49fbe31c9cb0a1_label'),(15720,7,'questions_question_5eab269060488cd69d49fbe31c9cb0a1_value_1'),(15721,7,'questions_question_5eab269060488cd69d49fbe31c9cb0a1_value_2'),(15722,7,'questions_question_5eab269060488cd69d49fbe31c9cb0a1_value_4'),(15723,7,'questions_question_5eab269060488cd69d49fbe31c9cb0a1_value_8'),(15724,7,'questions_question_6363dd2f3b50a8a775a5030b0da6bd65_value_1'),(15725,7,'questions_question_6363dd2f3b50a8a775a5030b0da6bd65_value_16'),(15726,7,'questions_question_6363dd2f3b50a8a775a5030b0da6bd65_value_2'),(15727,7,'questions_question_6363dd2f3b50a8a775a5030b0da6bd65_value_4'),(15728,7,'questions_question_6363dd2f3b50a8a775a5030b0da6bd65_value_8'),(15732,7,'questions_question_96c41d906ac020757eae6a0ba339e398_label'),(15733,7,'questions_question_96c41d906ac020757eae6a0ba339e398_value_1'),(15734,7,'questions_question_96c41d906ac020757eae6a0ba339e398_value_2'),(15735,7,'questions_question_96c41d906ac020757eae6a0ba339e398_value_4'),(15736,7,'questions_question_96c41d906ac020757eae6a0ba339e398_value_8'),(15737,7,'questions_question_a023dd4e406571f6e805e52e5dd9b9ef_description'),(15738,7,'questions_question_a023dd4e406571f6e805e52e5dd9b9ef_label'),(15739,7,'questions_question_a023dd4e406571f6e805e52e5dd9b9ef_value_1'),(15740,7,'questions_question_a023dd4e406571f6e805e52e5dd9b9ef_value_2'),(15741,7,'questions_question_a023dd4e406571f6e805e52e5dd9b9ef_value_4'),(15742,7,'questions_question_a023dd4e406571f6e805e52e5dd9b9ef_value_8'),(15743,7,'questions_question_a5115de7f38988e748370a59ba0b311d_description'),(15744,7,'questions_question_a5115de7f38988e748370a59ba0b311d_label'),(15745,7,'questions_question_b9b93e695cbc4475751d3fa98a417832_description'),(15746,7,'questions_question_b9b93e695cbc4475751d3fa98a417832_label'),(15747,7,'questions_question_b9b93e695cbc4475751d3fa98a417832_value_1'),(15748,7,'questions_question_b9b93e695cbc4475751d3fa98a417832_value_2'),(15749,7,'questions_question_b9b93e695cbc4475751d3fa98a417832_value_4'),(15750,7,'questions_question_birthday_label_presentation_age'),(15754,7,'questions_question_d88bae51bcd9f1a0c303164d567ea297_label'),(15756,7,'questions_question_da61580794d0318812eab8c09b147cd6_label'),(15757,7,'questions_question_da61580794d0318812eab8c09b147cd6_value_1'),(15758,7,'questions_question_da61580794d0318812eab8c09b147cd6_value_2'),(15759,7,'questions_question_da61580794d0318812eab8c09b147cd6_value_4'),(15760,7,'questions_question_da61580794d0318812eab8c09b147cd6_value_8'),(15761,7,'questions_question_e559834ab837e866e80aea0d1b453fc2_value_1'),(15762,7,'questions_question_e559834ab837e866e80aea0d1b453fc2_value_2'),(15763,7,'questions_question_e559834ab837e866e80aea0d1b453fc2_value_4'),(15764,7,'questions_question_e559834ab837e866e80aea0d1b453fc2_value_8'),(15766,7,'questions_question_match_739a83996f701966485290779f4ff2b5_label'),(15768,7,'questions_question_match_age_label'),(15770,7,'questions_question_sex_value_4'),(15771,7,'questions_section_about_my_match_label'),(15772,7,'test'),(15773,7,'users_list_birthdays_meta_description'),(15774,7,'users_list_latest_meta_description'),(15775,7,'users_list_online_meta_description'),(15776,7,'users_list_user_search_meta_description'),(15777,253,'about'),(15778,253,'admin_nav_adding_message'),(15779,253,'admin_nav_bottom_section_label'),(15780,253,'admin_nav_default_menu_name'),(15781,253,'admin_nav_default_page_content'),(15782,253,'admin_nav_default_page_title'),(15783,253,'admin_nav_hidden_section_label'),(15784,253,'admin_nav_item_content_field'),(15785,253,'admin_nav_item_label_field'),(15786,253,'admin_nav_item_title_field'),(15787,253,'admin_nav_new_item_label'),(15788,253,'admin_nav_settings_fb_title'),(15789,253,'admin_nav_top_section_label'),(15790,253,'admin_widgets_hidden_section_label'),(15791,253,'admin_widgets_main_section_label'),(15796,253,'page_default_heading'),(15797,253,'page_default_title'),(15798,253,'sign_out'),(15799,253,'view_profile'),(15800,253,'widgets_admin_dashboard_heading'),(15801,253,'widgets_admin_index_heading'),(15802,253,'widgets_admin_profile_heading'),(16101,7,'page_61525903'),(16103,253,'right_sidebar_guest_heading'),(16383,1,'notification_themes_to_update'),(16384,253,'page_is_not_available'),(16391,1,'error_empty_credentials_provided'),(16392,1,'error_empty_host_provided'),(16393,1,'error_ftp_function_is_not_available'),(16394,1,'error_invalid_credentials_provided'),(16396,1,'input_settings_attch_ext_list_desc'),(16397,1,'input_settings_attch_ext_list_label'),(16398,1,'input_settings_attch_max_upload_size_label'),(16399,1,'manage_plugin_cant_add_duplicate_key_error'),(16403,1,'possible_values_disable_message'),(16404,1,'questions_account_type_was_updated'),(16405,1,'questions_add_account_type_title'),(16406,1,'questions_add_edit_type_title'),(16407,1,'questions_add_profile_question_title'),(16408,1,'questions_add_question_button'),(16409,1,'questions_add_section_button'),(18464,1,'questions_matched_question_values'),(18465,1,'questions_possible_values_label'),(16412,1,'questions_edit_account_type_title'),(16413,1,'questions_edit_profile_question_title'),(16415,1,'questions_page_description'),(16416,1,'questions_save_account_type'),(16417,1,'question_menu_account_types'),(16418,1,'question_menu_properties'),(16419,1,'themes_cant_delete_active_theme'),(16420,1,'themes_cant_delete_default_theme'),(16421,1,'themes_choose_delete_button_label'),(16422,1,'themes_choose_delete_confirm_msg'),(16423,1,'themes_delete_success_message'),(16424,1,'user_input_settings_attachments'),(16427,7,'authorization_action_promotion'),(16428,7,'authorization_limited_permissions'),(16476,7,'base+questions_add_account_type'),(16477,7,'base_document_auth_failed_heading'),(16478,7,'btn_label_send'),(16479,7,'comment_load_more_label'),(16480,7,'complete_profile'),(16481,7,'complete_profile_info'),(16482,7,'complete_your_profile_page_heading'),(16483,7,'complite_profile'),(16484,7,'continue_button'),(16485,7,'empty_comment_error_msg'),(16486,7,'feed_activity_avatar_string_like_own'),(16487,7,'feed_activity_avatar_string_own'),(16594,7,'no_items'),(16595,7,'or'),(16672,7,'questions_account_type_1b4bbdb5419b2352aa276a7f58a585fa'),(16677,7,'questions_add_account_type'),(16679,7,'questions_edit_description_label_title'),(16680,7,'questions_edit_question_label_title'),(16681,7,'questions_edit_question_value_title'),(16828,7,'questions_question_0fbf517c6c3eb5c266894b51bdd07700_value_1'),(16829,7,'questions_question_0fbf517c6c3eb5c266894b51bdd07700_value_2'),(16830,7,'questions_question_0fbf517c6c3eb5c266894b51bdd07700_value_4'),(16831,7,'questions_question_0fbf517c6c3eb5c266894b51bdd07700_value_8'),(16994,7,'questions_question_25397eeec0dd1715655bbd5e2488bb50_value_1'),(16995,7,'questions_question_25397eeec0dd1715655bbd5e2488bb50_value_128'),(16996,7,'questions_question_25397eeec0dd1715655bbd5e2488bb50_value_16'),(16997,7,'questions_question_25397eeec0dd1715655bbd5e2488bb50_value_2'),(16998,7,'questions_question_25397eeec0dd1715655bbd5e2488bb50_value_32'),(16999,7,'questions_question_25397eeec0dd1715655bbd5e2488bb50_value_4'),(17000,7,'questions_question_25397eeec0dd1715655bbd5e2488bb50_value_64'),(17001,7,'questions_question_25397eeec0dd1715655bbd5e2488bb50_value_8'),(17071,7,'questions_question_399c943d354454ee1f3280603d4cf01e_value_1'),(17072,7,'questions_question_399c943d354454ee1f3280603d4cf01e_value_2'),(17073,7,'questions_question_399c943d354454ee1f3280603d4cf01e_value_4'),(17074,7,'questions_question_399c943d354454ee1f3280603d4cf01e_value_8'),(17151,7,'questions_question_48078777a47f521a8d2db15e8c43aeb4_value_1'),(17152,7,'questions_question_48078777a47f521a8d2db15e8c43aeb4_value_16'),(17153,7,'questions_question_48078777a47f521a8d2db15e8c43aeb4_value_2'),(17154,7,'questions_question_48078777a47f521a8d2db15e8c43aeb4_value_4'),(17155,7,'questions_question_48078777a47f521a8d2db15e8c43aeb4_value_8'),(17473,7,'questions_question_71ca7b196a4926b36ceb0e5f2f79f83f_value_1'),(17474,7,'questions_question_71ca7b196a4926b36ceb0e5f2f79f83f_value_2'),(17475,7,'questions_question_71ca7b196a4926b36ceb0e5f2f79f83f_value_32'),(17476,7,'questions_question_71ca7b196a4926b36ceb0e5f2f79f83f_value_4'),(17477,7,'questions_question_71ca7b196a4926b36ceb0e5f2f79f83f_value_64'),(17526,7,'questions_question_757d226391f04cf0ccb26cfa570026e0_value_1'),(17527,7,'questions_question_757d226391f04cf0ccb26cfa570026e0_value_2'),(17528,7,'questions_question_757d226391f04cf0ccb26cfa570026e0_value_4'),(17951,7,'questions_question_b9b93e695cbc4475751d3fa98a417832_value_16'),(17952,7,'questions_question_b9b93e695cbc4475751d3fa98a417832_value_32'),(17953,7,'questions_question_b9b93e695cbc4475751d3fa98a417832_value_8'),(18025,7,'questions_question_c4b93fbdd34a1892021fe179bff33834_value_1'),(18026,7,'questions_question_c4b93fbdd34a1892021fe179bff33834_value_16'),(18027,7,'questions_question_c4b93fbdd34a1892021fe179bff33834_value_2'),(18028,7,'questions_question_c4b93fbdd34a1892021fe179bff33834_value_4'),(18029,7,'questions_question_c4b93fbdd34a1892021fe179bff33834_value_8'),(18043,7,'questions_question_c897c809bc8a4f004f27a1e4637a99e6_description'),(18044,7,'questions_question_c897c809bc8a4f004f27a1e4637a99e6_label'),(18179,7,'questions_question_d570618c786b27617afb52192274c4e9_value_1'),(18180,7,'questions_question_d570618c786b27617afb52192274c4e9_value_128'),(18181,7,'questions_question_d570618c786b27617afb52192274c4e9_value_16'),(18182,7,'questions_question_d570618c786b27617afb52192274c4e9_value_2'),(18183,7,'questions_question_d570618c786b27617afb52192274c4e9_value_256'),(18184,7,'questions_question_d570618c786b27617afb52192274c4e9_value_32'),(18185,7,'questions_question_d570618c786b27617afb52192274c4e9_value_4'),(18186,7,'questions_question_d570618c786b27617afb52192274c4e9_value_512'),(18187,7,'questions_question_d570618c786b27617afb52192274c4e9_value_64'),(18188,7,'questions_question_d570618c786b27617afb52192274c4e9_value_8'),(18329,7,'questions_question_f17d6da3dec6687a509721adee152573_description'),(18330,7,'questions_question_f17d6da3dec6687a509721adee152573_label'),(18429,7,'questions_question__value_16'),(18430,7,'questions_question__value_8'),(18441,7,'questions_section_4e9e55a664473acae206ec14c58aa45e_label'),(18459,7,'required_profile_questions'),(18466,1,'question_possible_values_label'),(18467,7,'avatar_back_profile_edit'),(18468,7,'form_validate_common_error_message'),(18469,7,'questions_admin_add_new_values'),(18470,7,'questions_question_password_description'),(18471,7,'submit_attachment_not_loaded'),(18473,253,'admin_nav_item_type_field'),(18474,253,'admin_nav_item_type_local'),(18475,253,'admin_nav_item_url_field'),(18477,1,'pages_page_field_content_desc'),(18478,1,'questions_cant_delete_last_account_type'),(18479,1,'themes_admin_list_cap_title'),(18480,7,'comments_see_more_label'),(18481,7,'questions_question_field_098faf82d91306b42c070711da8bf177_description'),(18482,7,'questions_question_field_098faf82d91306b42c070711da8bf177_label'),(18483,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_1'),(18484,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_1024'),(18485,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_1048576'),(18486,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_128'),(18487,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_131072'),(18488,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_134217728'),(18489,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_16'),(18490,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_16384'),(18491,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_16777216'),(18492,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_2'),(18493,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_2048'),(18494,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_2097152'),(18495,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_256'),(18496,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_262144'),(18497,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_32'),(18498,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_32768'),(18499,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_33554432'),(18500,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_4'),(18501,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_4096'),(18502,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_4194304'),(18503,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_512'),(18504,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_524288'),(18505,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_64'),(18506,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_65536'),(18507,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_67108864'),(18508,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_8'),(18509,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_8192'),(18510,7,'questions_question_field_098faf82d91306b42c070711da8bf177_value_8388608'),(18511,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_description'),(18512,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_label'),(18513,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_1'),(18514,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_1024'),(18515,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_1048576'),(18516,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_1073741824'),(18517,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_128'),(18518,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_131072'),(18519,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_134217728'),(18520,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_16'),(18521,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_16384'),(18522,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_16777216'),(18523,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_2'),(18524,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_2048'),(18525,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_2097152'),(18526,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_256'),(18527,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_262144'),(18528,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_268435456'),(18529,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_32'),(18530,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_32768'),(18531,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_33554432'),(18532,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_4'),(18533,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_4096'),(18534,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_4194304'),(18535,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_512'),(18536,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_524288'),(18537,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_536870912'),(18538,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_64'),(18539,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_65536'),(18540,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_67108864'),(18541,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_8'),(18542,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_8192'),(18543,7,'questions_question_field_d4492405b06aa44625c25fce4f077e47_value_8388608'),(18544,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_description'),(18545,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_label'),(18546,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_1'),(18547,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_1024'),(18548,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_1048576'),(18549,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_128'),(18550,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_131072'),(18551,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_134217728'),(18552,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_16'),(18553,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_16384'),(18554,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_16777216'),(18555,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_2'),(18556,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_2048'),(18557,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_2097152'),(18558,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_256'),(18559,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_262144'),(18560,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_268435456'),(18561,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_32'),(18562,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_32768'),(18563,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_33554432'),(18564,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_4'),(18565,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_4096'),(18566,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_4194304'),(18567,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_512'),(18568,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_524288'),(18569,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_64'),(18570,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_65536'),(18571,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_67108864'),(18572,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_8'),(18573,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_8192'),(18574,7,'questions_question_field_f601014ed0ceba2d4e25606de1992eba_value_8388608'),(18577,1,'manage_plugins_add_size_error_message'),(18578,1,'manage_themes_add_empty_field_error_message'),(18579,1,'questions_delete_section_confirmation_with_move_questions'),(18580,1,'set_suspend_message_label'),(18581,7,'auth_group_label'),(18582,7,'avatar_changed'),(18583,7,'avatar_choose_from_library'),(18584,7,'avatar_drop_single_image'),(18585,7,'avatar_has_been_approved'),(18586,7,'avatar_image_too_small'),(18587,7,'avatar_pending_approval'),(18588,7,'avatar_update_string'),(18589,7,'back'),(18590,7,'check_all_to'),(18591,7,'comment_added_string'),(18592,7,'comment_content_label'),(18593,7,'content_avatars_label'),(18594,7,'content_avatar_label'),(18595,7,'content_comments_label'),(18596,7,'content_comment_label'),(18597,7,'content_profiles_label'),(18598,7,'content_profile_label'),(18599,7,'crop_avatar_failed'),(18600,7,'drag_image_or_browse'),(18601,7,'drop_image_here'),(18602,7,'flagged_time'),(18603,7,'input_settings_avatar_max_upload_size_label'),(18604,7,'moderation_action'),(18605,7,'moderation_delete_confirmation'),(18606,7,'moderation_delete_multiple_confirmation'),(18607,7,'moderation_feedback_delete'),(18608,7,'moderation_feedback_delete_multiple'),(18609,7,'moderation_feedback_unflag'),(18610,7,'moderation_feedback_unflag_multiple'),(18611,7,'moderation_flags_item_string'),(18612,7,'moderation_no_items'),(18613,7,'moderation_no_items_warning'),(18614,7,'moderation_panel'),(18615,7,'moderation_reason'),(18616,7,'moderation_reporter'),(18617,7,'moderation_tools'),(18618,7,'pending_approval'),(18619,7,'set_suspend_message_label'),(18661,7,'suspend_floatbox_title'),(18621,7,'suspend_notification_html'),(18622,7,'suspend_notification_subject'),(18623,7,'suspend_notification_text'),(18624,7,'unflag'),(18625,7,'welcome_letter_template_html'),(18626,7,'welcome_letter_template_text'),(18630,1,'add_password'),(18654,1,'change_password'),(18659,7,'join_index_new_to_site'),(18662,7,'authorization_role_grey'),(18663,7,'sing_in_to_flag'),(18664,7,'ws_error_video'),(18665,7,'invalid_file_type_acceptable_file_types_jpg_png_gif'),(18666,7,'ws_video_text_label'),(18667,1,'smtp_test_connection_failed'),(19746,1,'questions_infinite_possible_values_description'),(19747,1,'questions_infinite_possible_values_label'),(19748,1,'questions_values_should_not_be_empty'),(19941,7,'questions_question_presentation_fselect_label');

UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_language_prefix`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_language_prefix`;
CREATE TABLE `%%TBL-PREFIX%%base_language_prefix` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prefix` (`prefix`)
) ENGINE=MyISAM AUTO_INCREMENT=254 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_language_prefix`
--

LOCK TABLES `%%TBL-PREFIX%%base_language_prefix` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_language_prefix` VALUES (1,'admin','Admin'),(6,'peep_custom','Custom'),(7,'base','BASE'),(11,'nav','Navigation');
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_language_value`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_language_value`;
CREATE TABLE `%%TBL-PREFIX%%base_language_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `languageId` int(11) NOT NULL DEFAULT '0',
  `keyId` int(11) NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `keyId` (`keyId`,`languageId`)
) ENGINE=MyISAM AUTO_INCREMENT=62098 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_language_value`
--

LOCK TABLES `%%TBL-PREFIX%%base_language_value` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_language_value` VALUES (53,1,1603,'Choose language to edit'),(54,1,1604,' <a href=\"languages/mod\">other languages?</a>'),(55,1,1605,'Add New Text'),(56,1,1606,'Start'),(57,1,1607,'Search results for: <i><b>{$keyword}</i></b>'),(58,1,1608,'Search'),(59,1,1609,'Language code'),(60,1,1610,'Translation In: {$label}'),(61,1,1611,'Delete'),(62,1,1612,'Custom keys'),(63,1,1613,'Save'),(64,1,1614,'Section'),(65,1,1615,'Site Wide Plugins'),(66,1,1616,'Missing text'),(67,1,1617,'Move language to top to be site default language'),(68,1,1618,'Inactive language not appear for usres until you activate it'),(69,1,1619,'There should be at least one active language.'),(70,1,1620,'Empty'),(71,1,1621,'Missing keys'),(72,1,1622,'Active languages'),(73,1,1623,'Language'),(74,1,1624,'Edit'),(75,1,1625,'New lang pack from this lang'),(76,1,1626,'Deactivate'),(77,1,1627,'Activate'),(78,1,1628,'Delete'),(132,1,1682,'Here you can upload the file that contains a language for one plugin ( <b>.XML</b> file) or several languages for plugins ( <b>.ZIP</b> file).'),(133,1,1683,'Add New Language (Language Pack)'),(134,1,1684,'Select what you would like to import from this language package.'),(135,1,1685,'Check all languages for all plugins'),(136,1,1686,'Import selected'),(137,1,1687,'Export languages'),(138,1,1688,'You can export/backup any languages for any plugins you have installed on your site.'),(139,1,1689,'Export'),(140,1,1690,'Select items you would like  to export from all languages and plugins'),(141,1,1691,'Export selected'),(142,1,1692,'Show:'),(143,1,1693,'Edit Language'),(144,1,1694,'Language Packs'),(145,1,1695,'Key'),(146,1,1696,'Text in {$label} ( {$tag} )'),(147,1,1697,'Add'),(148,1,1698,'Language File:'),(149,1,1699,'Inactive Languages'),(150,1,1700,'New language pack'),(151,1,1701,'Language'),(152,1,1702,'Add new text'),(153,1,1703,'Example: <b>Arabic</b>'),(154,1,1704,'Are you sure?'),(155,1,1705,'Lang Code'),(156,1,1706,'Example: <b>ar-EG</b>'),(158,1,1708,'Add'),(159,1,1709,'No values with <i><b>{$keyword}</i></b> keyword'),(160,1,1710,'No values'),(161,1,1711,'Start'),(162,1,1712,'default'),(163,1,1713,'Sorry, the key already exists'),(191,1,1741,'February'),(192,1,1742,'August'),(193,1,1743,'September'),(195,1,1745,'All'),(196,1,1746,'Archive'),(197,1,1747,'Are you sure?'),(202,1,1752,'Comments'),(204,1,1754,'By'),(205,1,1755,'More'),(218,1,1768,'Report'),(219,1,1769,'Edit'),(220,1,1770,'Delete'),(223,1,1773,'Approve'),(224,1,1774,'Disapprove'),(305,1,1855,'Avatar is a graphic picture/photo of a reduced size displayed for your profile.'),(306,1,1856,'Your Avatar'),(307,1,1857,'Upload new avatar'),(308,1,1858,'Crop Avatar'),(309,1,1859,'Choose an area of your avatar for cropping with the help of mouse cursor. The cropping result will be displayed on the right. Once you are satisfied with the result click the \"Apply crop\" button.'),(310,1,1860,'Your avatar picture'),(311,1,1861,'Apply crop'),(59819,1,16389,'Default Role'),(313,1,1863,'Preview'),(336,1,1886,'User Groups'),(337,1,1887,'Language'),(339,1,1889,'Menus'),(340,1,1890,'Activate'),(341,1,1891,'Save'),(1038,1,2582,'{$count} emails were sent'),(343,1,1893,'This theme doesn\'t have any customization settings.'),(344,1,1894,'The image is not valid'),(345,1,1895,'Profile Pic'),(346,1,1896,'File type not allowed'),(347,1,1897,'Choose Theme'),(348,1,1898,'Password should have less than 15 characters'),(349,1,1899,'Password should have more than 6 characters'),(350,1,1900,'Please enter a valid password'),(351,1,1901,'Sorry, this email is already exists'),(352,1,1902,'Please, enter a valid email address'),(353,1,1903,'Please, enter a valid username'),(354,1,1904,'Available Themes'),(355,1,1905,'Choose Theme'),(356,1,1906,'Sorry, this username already exists'),(368,1,1918,'Delete'),(369,1,1919,'Today'),(370,1,1920,'Save'),(371,1,1921,'Update successful'),(372,1,1922,'{$site_name} Admin Board'),(373,1,1923,'{$site_name} Admin'),(376,1,1926,'{$title}'),(377,1,1927,'My Profile'),(378,1,1928,'Main'),(380,1,1930,'{$site_name} | Meet people and contact them'),(381,1,1931,'{$site_name}'),(382,1,1932,'Advanced dating and social meet network'),(383,1,1933,'peepdev, peepmatches, social network software, open community software, open dating community software, free community software, find dates software, peepmatches software, read dating stories, women looking for men'),(384,1,1934,'Edit Theme'),(385,1,1935,'Settings'),(386,1,1936,'CMS Manager'),(387,1,1937,'Themes'),(388,1,1938,'Users'),(389,1,1939,'Plugins'),(390,1,1940,'Login'),(392,1,1942,'Current Theme Settings'),(393,1,1943,'Your Custom Css Design Here'),(394,1,1944,'Save'),(398,1,1948,'Browse by Tags'),(399,1,1949,'Moderators'),(400,1,1950,'Site Privacy Settings'),(401,1,1951,'Photo'),(402,1,1952,'Forum'),(403,1,1953,'Read only css file'),(404,1,1954,'Image has been deleted'),(405,1,1955,'Image has not been uploaded'),(406,1,1956,'Image has been uploaded'),(407,1,1957,'Upload'),(408,1,1958,'Delete'),(409,1,1959,'URL'),(410,1,1960,'Image Preview'),(411,1,1961,'Images List'),(412,1,1962,'Upload Image'),(413,1,1963,'Account Type'),(620,1,2170,'Tagline'),(415,1,1965,'Change theme photos, logos, backgrounds,etc.'),(416,1,1966,'Author URL'),(417,1,1967,'Author'),(418,1,1968,'Pages:'),(420,1,1970,'Admin'),(421,1,1971,'few seconds ago'),(422,1,1972,'1 minute ago'),(423,1,1973,'{$minutes} minutes ago'),(424,1,1974,'People'),(426,1,1976,'Compatibility'),(427,1,1977,'Add New'),(428,1,1978,'Version'),(932,1,2476,'Description'),(10345,1,3807,'Permissions Settings'),(442,1,1992,'Add'),(443,1,1993,'Comments'),(444,1,1994,'Upload'),(445,1,1995,'Available formats for Avatar uploading are <span class=\"peep_txt_value\">JPG</span>/<span class=\"peep_txt_value\">GIF</span>/<span class=\"peep_txt_value\">PNG</span>'),(448,1,1998,'Change avatar'),(449,1,1999,'No section'),(450,1,2000,'Add new account type'),(458,1,2008,'Continue'),(459,1,2009,'Register'),(460,1,2010,'Music'),(461,1,2011,'About Me'),(462,1,2012,'Interests'),(463,1,2013,'Photo'),(464,1,2014,'Video'),(465,1,2015,'Error occurred while processing your request'),(466,1,2016,'Error occurred while posting comment'),(467,1,2017,'No comments'),(468,1,2018,'Delete'),(469,1,2019,'a hour ago'),(470,1,2020,'{$hours} hours ago'),(471,1,2021,'Yesterday'),(472,1,2022,'at'),(475,1,2025,'Sep'),(476,1,2026,'Jan'),(477,1,2027,'Feb'),(478,1,2028,'Mar'),(479,1,2029,'Apr'),(480,1,2030,'May'),(481,1,2031,'Jun'),(482,1,2032,'Jul'),(483,1,2033,'Aug'),(484,1,2034,'Oct'),(485,1,2035,'Nov'),(486,1,2036,'Dec'),(487,1,2037,'Graphics'),(488,1,2038,'Custom CSS'),(489,1,2039,'Quick Edit'),(491,1,2041,'Password Again'),(492,1,2042,'Password'),(493,1,2043,'Email'),(494,1,2044,'Profile Fields'),(497,1,2047,'Username'),(850,1,2397,'General User'),(501,1,2051,'Permissions Settings'),(508,1,2058,'Arrange'),(509,1,2059,'Change Avatar'),(510,1,2060,'Edit my profile'),(511,1,2061,'Page not found'),(512,1,2062,'Sorry, the page that you want doesn\'t exist.'),(515,1,2065,'View all'),(516,1,2066,'December'),(520,1,2070,'Recent'),(521,1,2071,'Featured'),(522,1,2072,'Save'),(523,1,2073,'Reset'),(524,1,2074,'Custom text/HTML'),(525,1,2075,'Home'),(526,1,2076,'Available blocks'),(527,1,2077,'Home'),(528,1,2078,'User Home Blocks'),(529,1,2079,'User Profile Blocks'),(530,1,2080,'Register an account'),(531,1,2081,'Manage Plugins'),(532,1,2082,'Guest'),(1067,1,2611,'You have been successfully unsubscribed.\r\n<br />\r\nThank you.'),(535,1,2085,'Edit'),(536,1,2086,'Delete'),(538,1,2088,'Content (text/HTML)'),(543,1,2093,'Convert new line to <br />'),(546,1,2096,'Video'),(547,1,2097,'Add Video'),(548,1,2098,'View Video'),(549,1,2099,'Photo'),(550,1,2100,'View photo'),(551,1,2101,'Upload photo'),(552,1,2102,'Comment photo'),(553,1,2103,'The content owner can delete comments'),(554,1,2104,'Basic permissions'),(555,1,2105,'Comment on members profiles'),(556,1,2106,'The commenter can delete comments'),(557,1,2107,'View site'),(558,1,2108,'block settings'),(559,1,2109,'Title'),(560,1,2110,'Show title'),(561,1,2111,'Icon'),(562,1,2112,'Wrap in box'),(563,1,2113,'not editable'),(564,1,2114,'Drag and drop available blocks to page sides'),(565,1,2115,'<span class=\"peep_mild_red\">Red items</span> are \"frozen\". Frozen items\' positions are fixed and can not be moved or edited by user. They always occupy the upper positions in containers.'),(566,1,2116,'You can let users customize this page components. However they will not be able to change page layout.'),(567,1,2117,'Are you sure you want to delete this block?'),(568,1,2118,'Allow users to customize this page'),(572,1,2122,'Settings'),(580,1,2130,'Admin'),(575,1,2125,'Number of comments to show'),(576,1,2126,'Display mode'),(577,1,2127,'With paging'),(578,1,2128,'Full list'),(581,1,2131,'Oops, no profiles'),(582,1,2132,'Online'),(584,1,2134,'Arrange'),(585,1,2135,'Birthday'),(591,1,2141,'Adminboard'),(592,1,2142,'Browse Peoples'),(593,1,2143,'Main Settings'),(594,1,2144,'About'),(595,1,2145,'RSS'),(596,1,2146,'Item count'),(597,1,2147,'URL'),(598,1,2148,'Enter valid URL'),(707,1,2257,'Are you sure you want to delete this field?'),(605,1,2155,'Meet people and make new contacts for dating and chating with them'),(611,1,2161,'Main settings'),(612,1,2162,'Basic'),(46644,1,9873,'User Settings'),(614,1,2164,'Choose account type'),(615,1,2165,'Should contain only letters and digits'),(616,1,2166,'Should be valid'),(617,1,2167,'Be apart with us'),(618,1,2168,'Site Installation'),(619,1,2169,'Site Name'),(621,1,2171,'Short description of your site.'),(622,1,2172,'Description'),(623,1,2173,'Describe your site.'),(624,1,2174,'Time Settings'),(625,1,2175,'TimeZone'),(626,1,2176,'Use relative date/time'),(627,1,2177,'\"Yesterday, 5:31\" instead of \"June 1 \'09, 5:31\"'),(628,1,2178,'Gender'),(629,1,2179,'&nbsp;'),(630,1,2180,'Male'),(631,1,2181,'Female'),(632,1,2182,'Account type'),(633,1,2183,'Yes'),(634,1,2184,'No'),(635,1,2185,'Settings have been updated'),(636,1,2186,'No content'),(637,1,2187,'Titles only'),(638,1,2188,'Basic'),(10350,1,3812,'Recently active'),(646,1,2196,'User Groups'),(650,1,2200,'No value'),(657,1,2207,'Blogs'),(658,1,2208,'Add comments'),(664,1,2214,'Add some info about yourself here'),(665,1,2215,'About Me'),(666,1,2216,'Description updated'),(667,1,2217,'Browse People'),(668,1,2218,'No people found'),(669,1,2219,'Check all'),(670,1,2220,'Selected:'),(681,1,2231,'Joined'),(682,1,2232,'User'),(683,1,2233,'Select'),(684,1,2234,'Write here...'),(685,1,2235,'{$count} user(s) deleted'),(686,1,2236,'Are you sure you want to delete selected users?'),(706,1,2256,'Field has been deleted'),(16894,1,4514,'Home'),(1011,1,2555,'Send'),(719,1,2269,'Your Email Or username'),(720,1,2270,'Rate'),(721,1,2271,'Section has been deleted'),(883,1,2427,'Basic'),(775,1,2324,'Account type has been added'),(1262,1,2791,'Registration:'),(758,1,2307,'Edit'),(759,1,2308,'Delete'),(1261,1,2790,'Remove rights for \'Admin\' from this moderator before you can remove them completely.'),(779,1,2328,'Profile Fields'),(763,1,2312,'Enter your email'),(764,1,2313,'Your email address'),(765,1,2314,'Account type has been deleted'),(766,1,2315,'New password for your account'),(20357,1,6052,'Reset code for new password'),(780,1,2329,'Add Field'),(781,1,2330,'Edit Account Types'),(816,1,2365,'Settings'),(59818,1,16388,'Add new values: <span class=\"peep_small peep_highlight\">Separate by comma</span>'),(784,1,2333,'You are not allowed to write comment'),(785,1,2334,'Text'),(58783,1,15500,'qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq'),(58782,1,15499,'qwe'),(788,1,2337,'Extended text'),(789,1,2338,'Yes/No'),(790,1,2339,'Multiple choice'),(791,1,2340,'Date'),(792,1,2341,'URL'),(793,1,2342,'Password'),(992,1,2536,'Add new account type'),(1116,1,2654,'Verification email successfully sent'),(1117,1,2655,'Suspend'),(1118,1,2656,'Suspend'),(1119,1,2657,'Account suspended'),(1121,1,2659,'Account suspended'),(1122,1,2660,'<center>Sorry your account is suspended. Please contact our team to solve this issue.</center>'),(817,1,2366,'If you have many profile questions it may be helpful to view sections as tabs on profile view page.'),(806,1,2355,'Blogs'),(807,1,2356,'Links'),(1263,1,2792,'All'),(818,1,2367,'View sections as tabs'),(819,1,2368,'Add a blog post'),(821,1,2370,'View'),(825,1,2374,'Save'),(826,1,2375,'The data updated'),(827,1,2376,'Edit Information'),(843,1,2390,'Edit section name'),(834,1,2381,'Home'),(835,1,2382,'Plugins'),(836,1,2383,'Active Plugins'),(837,1,2384,'Inactive Plugins'),(838,1,2385,'Settings'),(839,1,2386,'Deactivate'),(840,1,2387,'Activate'),(841,1,2388,'Remove'),(842,1,2389,'Setup'),(844,1,2391,'Edit field value'),(845,1,2392,'Edit field name'),(846,1,2393,'Edit field description'),(847,1,2394,'All account types'),(848,1,2395,'Edit account type name'),(849,1,2396,'\"{$plugin}\" plugin successfully installed'),(859,1,2406,'Standard'),(1041,1,2585,'To unsubscribe from this mailing list please click here: {$link}'),(1040,1,2584,'<br />\r\n<br />\r\nTo unsubscribe from this mailing list please <a href=\"{$link}\" >click here</a>'),(1037,1,2581,'Rated'),(1104,1,2642,'Real name'),(1010,1,2554,'Verify Email Adress'),(880,1,2425,'Invalid username or email'),(881,1,2426,'Invalid password'),(885,1,2429,'Field'),(886,1,2430,'Short description if necessary'),(887,1,2431,'Account verified...'),(892,1,2436,'{$site_name}©'),(924,1,2468,'New Language pack added'),(925,1,2469,'Profile Fields'),(1264,1,2793,'By Invitation'),(928,1,2472,'Language'),(929,1,2473,'Showing fields for account type'),(930,1,2474,'New section'),(931,1,2475,'Field'),(933,1,2477,'For account type'),(934,1,2478,'Select account type for the field <i>only</i> if it doesn\'t fit other types.</b> E.g.: <b><i>Gender</i></b> corresponds to <b><i>Single</i></b> but doesn\'t fit <b><i>Couple</i></b>.'),(935,1,2479,'Section'),(936,1,2480,'Answer type'),(58802,1,15519,'Privacy Policy'),(942,1,2486,'You can not add more than 30 value.'),(943,1,2487,'Columns count'),(944,1,2488,'Required'),(945,1,2489,'The form will not submit if the user doesn\'t enter valid answer for this question'),(946,1,2490,'On <b>Registration</b>'),(947,1,2491,'The field will be in registration page'),(948,1,2492,'On <b>Editing information page</b>'),(949,1,2493,'The field will be editable'),(950,1,2494,'On <b>Visible on profile</b>'),(951,1,2495,'The field will be visible for people on member profile page'),(952,1,2496,'The field used for Search'),(953,1,2497,'On <b>Search</b>'),(954,1,2498,'Save and Add New'),(1039,1,2583,'Edit Usergroup'),(957,1,2501,'The field added'),(960,1,2504,'The field updated'),(961,1,2505,'OR'),(61902,1,18472,'External'),(61903,1,18473,'Page type'),(962,1,2506,'Are you sure?'),(967,1,2511,'Extra meta tags for your page\'s header section.'),(969,1,2513,'Field description'),(971,1,2515,'Existing values'),(972,1,2516,'Drag\'n\'drop to places'),(973,1,2517,'Add new field values'),(974,1,2518,'The field was not updated'),(975,1,2519,'The field was not updated'),(978,1,2522,'Add values (one per line)'),(979,1,2523,''),(1207,1,2736,'Page content'),(981,1,2525,'Add'),(982,1,2526,'Pages'),(987,1,2531,'No value'),(985,1,2529,'The field does not exist'),(989,1,2533,'Add {$count} field value(s)'),(997,1,2541,'Section has been added'),(996,1,2540,'Account type has been added'),(998,1,2542,'Here you can change information added by users about themselves. You can change, rearrange, and add new profile fields.'),(999,1,2543,'<span class=\"peep_highlight\">Drag\'n\'drop</span> fields and sections to change their order.'),(1000,1,2544,'Add New Profile field'),(1001,1,2545,'Profile Field Sections'),(1002,1,2546,'If you have a lot of profile fields you may want to divide them into sections. You might have the following sections \"<b><i>Basic info</i></b>\", \"<b><i>Contacts</i></b>\", \"<b><i>Interests</i></b>\", so on and so forth.'),(1003,1,2547,'New section'),(1005,1,2549,'Are you sure you want to delete this section?'),(1012,1,2556,'Dear {$username},<br />\r\n<br />\r\nWelcome to {$site_name}! Just one step to be a part in our community <a href=\"{$url}\">you should verify your email adress by clicking this link</a><br />\r\n<br />\r\nOr copy this verification code ( {$code} ) and past it into  <a href=\"{$verification_page_url}\">This page</a><br />\r\n<br />\r\nRegards,<br />\r\n{$site_name} Team'),(1013,1,2557,'Dear {$username},\r\n\r\nWelcome to {$site_name}! Just one step to be a part in our community. you should confirm your email adress through this link: {$url}\r\n\r\nOr copy and paste this code at this page {$verification_page_url} : \r\n{$code}\r\n\r\nRegards,\r\n{$site_name} Team'),(1014,1,2558,'Email address confirmed'),(1015,1,2559,'Add'),(59529,1,15769,''),(1017,1,2561,'New {$count} messages'),(1018,1,2562,'Language'),(1020,1,2564,'<p>\r\nVerify your email address.<br />Click \"Send\" button to receive a letter with the verification code to your email again.\r\n</p>\r\n<p>\r\n<b>If you don\'t receive email</b> please add <i>{$site_email}</i> to your contacts.\r\n</p>'),(1021,1,2565,'Sorry, email address not virified.<br/> May:<br/> \r\n<br/> \r\n1) verification link you have used is incorrect. Please copy the correct URL and insert it into the address bar.\r\n<br/><br/>\r\n2) Confirmation URL expired as it hasn\'t been used within 5 days. Please send confirmation request again.'),(1024,1,2568,'Edit'),(1025,1,2569,'Add'),(1026,1,2570,'Ignore unsubscribe preference'),(1027,1,2571,'HTML'),(1028,1,2572,'Text'),(1029,1,2573,'Email body'),(1030,1,2574,'Subject'),(1031,1,2575,'Email format'),(1032,1,2576,'User selection'),(1033,1,2577,'<b>Total:</b> <i>{$count}</i> active members'),(1034,1,2578,'Preview'),(1035,1,2579,'Compose Email'),(1036,1,2580,'Start Mailing'),(1044,1,2588,'Usergroup Name:'),(1045,1,2589,'New usergroup added'),(1051,1,2595,'Please, select at least one usergroup'),(1049,1,2593,'Unsubscribe'),(1050,1,2594,'Usergroup(s) deleted'),(1055,1,2599,'Usergroup'),(1056,1,2600,'of Users'),(1057,1,2601,'default'),(1058,1,2602,'Check all | Selected:'),(1059,1,2603,'Delete'),(1060,1,2604,'Are you sure?'),(1061,1,2605,'Go to <a href=\"{$url}\">permissions page</a> to set which features are allowed for specific user groups.'),(1065,1,2609,'Free'),(1068,1,2612,'Unsubscription failed. \r\n<br />\r\nPlease make sure you entered unsubscribe link correctly.'),(1069,1,2613,'Newsletter'),(1074,1,2615,'Profile pic'),(1075,1,2616,'Input above code here...'),(1079,1,2620,'You can use the following variables:'),(6072,1,2850,'User Customization'),(1085,1,2626,'Display Settings'),(1086,1,2627,'Avatar Settings'),(1087,1,2628,'User settings updated'),(1088,1,2629,'Avatar size'),(1089,1,2630,'Big avatar size'),(1091,1,2632,'Show profile name as:'),(1092,1,2633,'Normally you would need to choose between <b><i>Username</i></b> (traditional) and a real name (as on Facebook). This is a site-wide setting.'),(1093,1,2634,'Shown in all listings. You will also need to change your theme CSS.'),(1094,1,2635,'Shown on profile page'),(1123,1,2661,'Account re-activated'),(1124,1,2662,'Re-activate'),(1214,1,2743,'Reset'),(1206,1,2735,'Visible For'),(6099,1,2877,'No people found'),(1163,1,2698,'Want meet'),(59528,1,2708,''),(1296,1,2823,'Comment video'),(1203,1,2732,'Menu Name'),(1287,1,2815,'<center>Nothing to show</center>'),(1129,1,2667,'User Home'),(1130,1,2668,'User Profile Settings'),(1202,1,2731,'Visible for'),(1201,1,2730,'Open in a new window'),(1132,1,2670,'Registration successful'),(1133,1,2671,'<center>This Account is Suspended</center>'),(61890,1,18460,''),(1135,1,2673,'{$username}'),(1136,1,2674,'{$username}'),(1137,1,2675,'My profile'),(1138,1,2676,'My profile'),(1139,1,2677,'Are you sure you want to reset your customizations?'),(1142,1,2680,'Are you sure you want to delete this image?'),(1170,1,2702,'Male'),(1171,1,2703,'Female'),(1176,1,2707,'Here for'),(59527,1,2560,''),(1198,1,2727,'Menu name'),(1199,1,2728,'URL'),(1200,1,2729,'Please login to comment'),(1194,1,2723,'Meet new people'),(1195,1,2724,'Marriage'),(1196,1,2725,'Dating'),(1197,1,2726,'Chat'),(1208,1,2737,'Guests'),(1209,1,2738,'Registered Members'),(1210,1,2739,'Menu Name'),(1211,1,2740,'Page Content'),(1212,1,2741,'Page URL Address'),(1218,1,2747,'Image Preview'),(1295,1,2822,'Newsletters'),(1255,1,2784,'Permissions successfully updated'),(6082,1,2860,'Add New'),(1254,1,2783,'Permissions Settings'),(1256,1,2785,'User not found'),(1257,1,2786,'Moderator has been added'),(1260,1,2789,'User removed from moderators'),(1259,1,2788,'{$username} is already a moderator'),(1105,1,2643,'First name and Last name'),(1205,1,2734,'URL'),(1204,1,2733,'Page Title'),(1265,1,2794,'Who can invite:'),(1266,1,2795,'All users'),(1267,1,2796,'Admin only'),(1269,1,2798,'Approve Users'),(1270,1,2799,'Moderators will approve all members manually <b>before</b> they can use full site'),(1271,1,2800,'Guests view the site'),(1272,1,2801,'Yes'),(1273,1,2802,'No'),(1275,1,2804,'Private site with password:'),(1276,1,2805,'If <b>\'No\'</b> guests redirect to login page'),(1277,1,2806,'Save'),(1297,1,2824,'The content owner can delete comments'),(1279,1,2807,'Member'),(1281,1,2809,'Add New Moderator'),(1282,1,2810,'Username'),(10328,1,3790,'Number of users who reported this content'),(1284,1,2812,'Make Moderator'),(1219,1,2748,'My Profile'),(4993,1,2834,'Go to <a href=\"{$url}\">usergroups management page</a> to manage usergroups.'),(4984,1,2825,'Are you sure you want to suspend selected user(s)?'),(4986,1,2827,'The content owner can delete comments'),(4988,1,2829,'People'),(4989,1,2830,'\"{$plugin}\" plugin activated'),(4990,1,2831,'\"{$plugin}\" plugin deactivated'),(4994,1,2835,'You can\'t view this page'),(6065,1,2844,'Required'),(6066,1,2845,'Visitors Comments'),(6067,1,2846,'Avatar'),(6068,1,2847,'Email verification'),(6064,1,2843,'Sorry you don\'t have enough permissions'),(6090,1,2868,'123'),(59526,1,2699,''),(59525,1,15767,''),(59524,1,15765,''),(59523,1,7529,''),(6098,1,2876,'Values updated'),(6102,1,2880,'Site Email'),(6103,1,2881,'Email address from which your users will receive notifications and newsletters.'),(6106,1,2884,'Recent Active'),(6117,1,2895,'January'),(6121,1,2899,'User marked as featured'),(6122,1,2900,'User removed from featured'),(6123,1,2901,'Mark as featured'),(6124,1,2902,'Remove from featured'),(6125,1,2903,'Friends'),(6126,1,2904,'Add Friend'),(6128,1,2906,'Please choose image file'),(7434,1,3294,'Hi,\r\n\r\nWe invite you to join our website - {$site_name}.\r\nPlease join here: {$url}\r\n\r\nAdministration\r\n{$site_url}'),(7431,1,3291,'Invitations successfully sent'),(7430,1,3290,'You should enter at least one email address'),(7429,1,3289,'Please enter max 50 emails'),(7428,1,3288,'Invite'),(7427,1,3287,'Enter list of emails (max {$limit}, one email per line)'),(7426,1,3286,'Invite new members'),(7425,1,3285,'Invite new members'),(7424,1,3284,'Give visitors a warning that your site contains adult contents.'),(7432,1,3292,'Invitation to {$site_name}'),(7433,1,3293,'<p>\r\nHi,\r\n</p>\r\n<p>\r\nWe invite you to join our website - {$site_name}.<br />\r\nPlease register <a href=\"{$url}\">here</a>\r\n</p>\r\nAdministration<br />\r\n{$site_url}'),(7423,1,3283,'Enable'),(9887,1,3349,'OR'),(59522,1,15603,''),(7400,1,3260,'No Thanks'),(7399,1,3259,'Settings updated'),(7396,1,3256,'Adult warning settings'),(7398,1,3258,'Adult warning settings'),(7401,1,3261,'Adult warning splash'),(7393,1,3253,'Enter'),(7392,1,3252,'<h1 class=\"peep_splash_offline_warning\">Over 18 warning.</h1>\r\nTo use {$site_name} you should be over 18 y.o , Because it contains adult contents .'),(7391,1,3251,'Where visitors go if choose to leave'),(7390,1,3250,'\'No Thanks\' URL'),(7389,1,3249,'Custom button label to enter the site'),(7388,1,3248,'\'Enter\' button label'),(7387,1,3247,'Warning page content.'),(7386,1,3246,'Text/HTML'),(7384,1,3244,'»»'),(7383,1,3243,'»'),(7382,1,3242,'«'),(7381,1,3241,'««'),(7380,1,3240,'Invalid password'),(7379,1,3239,'Redirecting you...'),(7378,1,3238,'Enter'),(7377,1,3237,'Sorry, site is closed for public.'),(7376,1,3236,'Enter Password'),(7375,1,3235,'Settings saved'),(10366,1,3828,'Users'),(9883,1,3345,'Moderator Panel'),(9884,1,3346,'Approve members'),(7414,1,3274,'site registration disabled'),(7372,1,3232,'OR'),(7371,1,3231,'Join By'),(7370,1,3230,'Create new {$site_name} account'),(9891,1,3353,'User approved'),(7397,1,3257,'Adult warning splash'),(9886,1,3348,'Approve members'),(7335,1,3116,'years old.'),(58781,1,15498,'qwe'),(7327,1,3108,'November'),(7326,1,3107,'October'),(7324,1,3105,'June'),(7325,1,3106,'July'),(7323,1,3104,'May'),(7321,1,3102,'April'),(7320,1,3101,'March'),(7319,1,3100,'SMTP test success. You can start sending emails using this connection.'),(7318,1,3099,'Test connection'),(7315,1,3096,'Test SMTP connection'),(7317,1,3098,'Make sure you test your SMTP connection before you start sending emails using it. Otherwise, your site may stop sending emails at all.'),(7316,1,3097,'Off'),(7311,1,3092,'SMTP Settings'),(7309,1,3090,'SMTP connection security type'),(7308,1,3089,'Secure connection'),(7307,1,3088,'Password'),(7306,1,3087,'Username'),(7305,1,3086,'Host:Port'),(7304,1,3085,'Please do not enable if you don\'t know what you are doing. Your site may stop sending emails!'),(7303,1,3084,'Enable'),(7302,1,3083,'SMTP'),(7301,1,3082,'SMTP settings updated'),(7300,1,3081,'Are you sure you want to delete this user?'),(7298,1,3079,'Account was removed<br />\r\nGo back to <a href=\"{$site_url}\">site</a>'),(7297,1,3078,'Delete confirmation'),(7296,1,3077,'Delete'),(43372,1,9399,'visible to guests only'),(7287,1,3068,'qqqq'),(7289,1,3070,'qqq'),(7291,1,3072,'qqq'),(7285,1,3066,'aaa'),(10396,1,3858,'Installed Plugins'),(7275,1,3056,'Delete user content'),(7273,1,3054,'Settings have been updated'),(7272,1,3053,'day/month/year'),(7271,1,3052,'month/day/year'),(7270,1,3051,'Date format'),(7268,1,3049,'Please, enter verification code.'),(7267,1,3048,'Submit'),(7269,1,3050,'Invalid verification code'),(7265,1,3046,'Code'),(7264,1,3045,'Note that if you delete your account you can not undo this process'),(7260,1,3041,'Delete My Account'),(7259,1,3040,'Edit'),(7258,1,3039,'Delete my account'),(7257,1,3038,'Delete'),(7255,1,3036,'sfdaf'),(7253,1,3034,'2q3r3'),(7256,1,3037,'Cancel'),(7251,1,3032,'sdfsdfsdf'),(7249,1,3030,'8aaxaxa'),(7246,1,3027,'sdfsdf'),(7244,1,3025,'sdf'),(7242,1,3023,'sdffsdf'),(7237,1,3018,'whoohaa'),(7248,1,3029,'8aaxaxa'),(7235,1,3016,'whoohaa'),(7240,1,3021,'sdfsd'),(7231,1,3012,'Guests are not allowed to rate'),(7230,1,3011,'Contact Importer'),(7216,1,2997,'Join'),(7142,1,2923,'Change Password'),(7143,1,2924,'Old password'),(7144,1,2925,'New password'),(7145,1,2926,'Repeat password'),(7146,1,2927,'Password changed'),(7147,1,2928,'Password not changed'),(7151,1,2932,'<a href=\"{$userUrl}\">{$user}</a> changed their profile photo.'),(7153,1,2934,'<a href=\"{$userUrl}\">{$user}</a> has been a member at {$site_name}'),(7154,1,2935,'<a href=\"{$userUrl}\"><img src=\"{$avatarUrl}\" alt=\"{$user}\"></a>'),(7155,1,2936,'<a href=\"{$userUrl}\">{$user}</a> edited their profile information'),(7177,1,2958,'Are you sure you want to reset your profile customization?'),(7212,1,2993,'Re-activate'),(7213,1,2994,'Selected users have been re-activated'),(7214,1,2995,'Selected user(s) re-activated'),(46459,1,9690,'Nothing to show'),(46460,1,9691,'Subscribe to our newsletter'),(46462,1,9693,'display'),(10589,1,4051,'Please enter password'),(10588,1,4050,'Day'),(10587,1,4049,'Month'),(10585,1,4047,'Are you sure you want to delete comment?'),(10586,1,4048,'Year'),(10584,1,4046,'You can\'t rate your own content'),(10581,1,4043,'You are not allowed to search people'),(10582,1,4044,'Search people'),(10577,1,4039,'to'),(10578,1,4040,'Age'),(10576,1,4038,'from'),(10573,1,4035,'Back to search'),(10572,1,4034,'Search'),(10570,1,4032,'Main Search'),(10554,1,4016,'Thursday'),(10555,1,4017,'Friday'),(10556,1,4018,'Saturday'),(10557,1,4019,'Please enter valid license key'),(10558,1,4020,'Search'),(10559,1,4021,'Search by Name'),(10560,1,4022,'Search'),(10552,1,4014,'Tuesday'),(10553,1,4015,'Wednesday'),(10550,1,4012,'Sunday'),(10551,1,4013,'Monday'),(10548,1,4010,'Are you sure you want to upgrade <i>\'{$name}\'</i> plugin from version <b>{$oldVersion}</b> to <b>{$newVersion}</b>? The plugin is commercial, to upgrade it please enter license key and press \'Upgrade\' button.'),(10544,1,4006,'Upgrade'),(10543,1,4005,'Back to plugin list'),(10542,1,4004,'License Key'),(10525,1,3987,'Invalid FTP login provided'),(10526,1,3988,'Payment provider does not support site active currency (<b>{$currency}</b>)'),(10494,1,3956,'Pay securely with'),(10491,1,3953,'No'),(10490,1,3952,'Yes'),(10489,1,3951,'Are you sure you want to upgrade <i>\'{$name}\'</i> plugin from version <b>{$oldVersion}</b> to <b>{$newVersion}</b>?'),(10488,1,3950,'Plugin upgrade'),(10483,1,3945,'No payment gateways available'),(10473,1,3935,'Preview'),(10474,1,3936,'Fullsize'),(10475,1,3937,'Align'),(10476,1,3938,'None'),(10477,1,3939,'Left'),(10478,1,3940,'Center'),(10479,1,3941,'Right'),(10480,1,3942,'Insert into post'),(10481,1,3943,'Upgrade'),(10471,1,3933,'URL'),(10472,1,3934,'remove'),(10470,1,3932,'hide'),(10469,1,3931,'show'),(10468,1,3930,'Gallery'),(10467,1,3929,'From URL'),(10466,1,3928,'Choose File'),(10465,1,3927,'File size limit is'),(10462,1,3924,'Image URL'),(10463,1,3925,'Insert'),(10464,1,3926,'Acceptable file types:'),(10461,1,3923,'Rich text user input'),(10460,1,3922,'Maximum file size'),(10459,1,3921,'Allow picture upload'),(10458,1,3920,'Add new plugin'),(10456,1,3918,'Upload'),(10440,1,3902,'\"{$plugin}\" deleted'),(10430,1,3892,'Remember'),(10425,1,3887,'Host'),(10426,1,3888,'Forgot Password'),(14322,1,4126,'Birthdays'),(10416,1,3878,'Connection failed, please check host and port details'),(10413,1,3875,'Port'),(10411,1,3873,'Enter'),(10412,1,3874,'FTP access needed to complete the operation'),(10409,1,3871,'Login'),(10410,1,3872,'Password'),(10408,1,3870,'Upload'),(10407,1,3869,'Enter Details'),(10406,1,3868,'FTP Login Details'),(10405,1,3867,'\"{$plugin}\" plugin successfully uninstalled'),(10404,1,3866,'Delete'),(10401,1,3863,'Available Plugins'),(10403,1,3865,'Are you sure you want completely delete plugin `{$pluginName}`'),(10402,1,3864,'Plugin not found'),(10400,1,3862,'Currency'),(10399,1,3861,'Earnings'),(10398,1,3860,'Add New'),(10397,1,3859,'Available Plugins'),(10392,1,3854,'The information on changing and confirming your new password sent to your email'),(10389,1,3851,'Home'),(10390,1,3852,'Theme changed'),(10383,1,3845,'Back'),(10381,1,3843,'Submit'),(10382,1,3844,'Add Item'),(10373,1,3835,'User Groups'),(10374,1,3836,'User Groups'),(10375,1,3837,'Moderators'),(10372,1,3834,'Site Privacy'),(10371,1,3833,'Back to user groups'),(10370,1,3832,'User group: {$role}'),(10368,1,3830,'Reported content'),(10369,1,3831,'User groups management'),(10367,1,3829,'For approval'),(10358,1,3820,'Last Login:'),(10359,1,3821,'Online now'),(10361,1,3823,'search result : \"<b>{$for}</b>\"'),(10357,1,3819,'unapproved'),(10356,1,3818,'unverified'),(10355,1,3817,'suspended'),(10354,1,3816,'Status'),(10353,1,3815,'Unapproved'),(10352,1,3814,'Unverified'),(10351,1,3813,'Suspended'),(10346,1,3808,'Site Offline'),(20387,1,6082,'123'),(10312,1,3774,'report'),(10309,1,3771,'Criminal'),(10310,1,3772,'Illegal'),(10311,1,3773,'Why you want report this ?'),(10166,1,3628,'Zimbabwe'),(10165,1,3627,'Zambia'),(10164,1,3626,'South Africa'),(10163,1,3625,'Yemen'),(10162,1,3624,'Samoa'),(10160,1,3622,'Vanuatu'),(10161,1,3623,'Wallis And Futuna'),(10158,1,3620,'Virgin Islands, U.s.'),(10159,1,3621,'Viet Nam'),(10157,1,3619,'Virgin Islands, British'),(10156,1,3618,'Venezuela'),(10155,1,3617,'Saint Vincent And The Grenadines'),(10154,1,3616,'Holy See (vatican City State)'),(10153,1,3615,'Uzbekistan'),(10152,1,3614,'United States'),(10151,1,3613,'Uruguay'),(10147,1,3609,'United Republic Of Tanzania'),(10148,1,3610,'Uganda'),(10149,1,3611,'Ukraine'),(10150,1,3612,'United States Minor Outlying Islands'),(10146,1,3608,'Taiwan'),(10143,1,3605,'Tunisia'),(10144,1,3606,'Turkey'),(10145,1,3607,'Tuvalu'),(10142,1,3604,'Trinidad And Tobago'),(10141,1,3603,'Tonga'),(10140,1,3602,'Timor-leste'),(10139,1,3601,'Turkmenistan'),(10138,1,3600,'Tokelau'),(10137,1,3599,'Tajikistan'),(10133,1,3595,'Turks And Caicos Islands'),(10134,1,3596,'Chad'),(10135,1,3597,'Togo'),(10136,1,3598,'Thailand'),(10132,1,3594,'Syrian Arab Republic'),(10131,1,3593,'Seychelles'),(10130,1,3592,'Swaziland'),(10129,1,3591,'Sweden'),(10128,1,3590,'Slovenia'),(10127,1,3589,'Slovakia'),(10126,1,3588,'Suriname'),(10123,1,3585,'Saint Pierre And Miquelon'),(10124,1,3586,'Serbia'),(10125,1,3587,'Sao Tome And Principe'),(10121,1,3583,'San Marino'),(10122,1,3584,'Somalia'),(10120,1,3582,'El Salvador'),(10116,1,3578,'Singapore'),(10117,1,3579,'South Georgia And The South Sandwich Islands'),(10118,1,3580,'Solomon Islands'),(10119,1,3581,'Sierra Leone'),(10115,1,3577,'Senegal'),(43379,1,9406,'You are not allowed to view members profiles'),(10114,1,3576,'Sudan'),(10113,1,3575,'Serbia And Montenegro'),(10112,1,3574,'Saudi Arabia'),(10111,1,3573,'Rwanda'),(10110,1,3572,'Russian Federation'),(10109,1,3571,'Romania'),(10108,1,3570,'Reunion'),(10107,1,3569,'Qatar'),(10106,1,3568,'French Polynesia'),(10105,1,3567,'Palestinian Territory, Occupied'),(10104,1,3566,'Paraguay'),(10103,1,3565,'Portugal'),(10102,1,3564,'Democratic People\'s Republic Of Korea'),(10101,1,3563,'Puerto Rico'),(10100,1,3562,'Poland'),(10099,1,3561,'Papua New Guinea'),(10098,1,3560,'Palau'),(10097,1,3559,'Philippines'),(10096,1,3558,'Peru'),(10095,1,3557,'Panama'),(10094,1,3556,'Pakistan'),(10093,1,3555,'Oman'),(10092,1,3554,'New Zealand'),(10091,1,3553,'Nauru'),(10090,1,3552,'Nepal'),(10089,1,3551,'Norway'),(10087,1,3549,'Niue'),(10088,1,3550,'Netherlands'),(10203,1,3665,'There is another page with same address'),(10086,1,3548,'Nicaragua'),(10085,1,3547,'Nigeria'),(10084,1,3546,'Norfolk Island'),(10082,1,3544,'New Caledonia'),(10083,1,3545,'Niger'),(10081,1,3543,'Namibia'),(10315,1,3777,'You\'ve already reported this'),(10080,1,3542,'Mayotte'),(10078,1,3540,'Malawi'),(10079,1,3541,'Malaysia'),(10077,1,3539,'Mauritius'),(10330,1,3792,'My Dashboard'),(10076,1,3538,'Martinique'),(10075,1,3537,'Montserrat'),(10074,1,3536,'Mauritania'),(10073,1,3535,'Mozambique'),(10329,1,3791,'Report deleted'),(10072,1,3534,'Northern Mariana Islands'),(10068,1,3530,'Malta'),(10069,1,3531,'Myanmar'),(10070,1,3532,'Montenegro'),(10071,1,3533,'Mongolia'),(10067,1,3529,'Mali'),(10065,1,3527,'Marshall Islands'),(10066,1,3528,'The Former Yugoslav Republic Of Macedonia'),(10064,1,3526,'Mexico'),(10063,1,3525,'Maldives'),(10062,1,3524,'Madagascar'),(10061,1,3523,'Republic Of Moldova'),(10060,1,3522,'Monaco'),(10059,1,3521,'Morocco'),(10058,1,3520,'Saint Martin'),(10057,1,3519,'Macao'),(10054,1,3516,'Lithuania'),(10055,1,3517,'Luxembourg'),(10056,1,3518,'Latvia'),(10053,1,3515,'Lesotho'),(10052,1,3514,'Sri Lanka'),(10049,1,3511,'Libyan Arab Jamahiriya'),(10051,1,3513,'Liechtenstein'),(10050,1,3512,'Saint Lucia'),(10048,1,3510,'Liberia'),(10047,1,3509,'Lebanon'),(10046,1,3508,'Lao People\'s Democratic Republic'),(10045,1,3507,'Kuwait'),(10044,1,3506,'Republic Of Korea'),(10043,1,3505,'Saint Kitts And Nevis'),(10042,1,3504,'Kiribati'),(10041,1,3503,'Cambodia'),(10040,1,3502,'Kyrgyzstan'),(10039,1,3501,'Kenya'),(10038,1,3500,'Kazakhstan'),(10037,1,3499,'Japan'),(10036,1,3498,'Jordan'),(10035,1,3497,'Jersey'),(10336,1,3798,'Forum'),(10034,1,3496,'Jamaica'),(10033,1,3495,'Italy'),(10032,1,3494,'Israel'),(10031,1,3493,'Iceland'),(10030,1,3492,'Iraq'),(10029,1,3491,'Islamic Republic Of Iran'),(10026,1,3488,'India'),(10027,1,3489,'British Indian Ocean Territory'),(10028,1,3490,'Ireland'),(10025,1,3487,'Isle Of Man'),(10024,1,3486,'Indonesia'),(10023,1,3485,'Hungary'),(43373,1,9400,'visible to members only'),(10022,1,3484,'Haiti'),(10021,1,3483,'Croatia'),(10020,1,3482,'Honduras'),(10019,1,3481,'Hong Kong'),(10018,1,3480,'Guyana'),(10017,1,3479,'Guam'),(10016,1,3478,'French Guiana'),(10015,1,3477,'Guatemala'),(10014,1,3476,'Greenland'),(10013,1,3475,'Grenada'),(20388,1,6083,'12'),(20389,1,6084,'321'),(10012,1,3474,'Greece'),(10011,1,3473,'Equatorial Guinea'),(10010,1,3472,'Guinea-bissau'),(10006,1,3468,'Gibraltar'),(10007,1,3469,'Guinea'),(10009,1,3471,'Gambia'),(10008,1,3470,'Guadeloupe'),(10005,1,3467,'Ghana'),(10002,1,3464,'United Kingdom'),(10004,1,3466,'Guernsey'),(10003,1,3465,'Georgia'),(9998,1,3460,'France'),(9999,1,3461,'Faroe Islands'),(10000,1,3462,'Federated States Of Micronesia'),(10001,1,3463,'Gabon'),(9997,1,3459,'Falkland Islands (malvinas)'),(20386,1,6081,'21312'),(9996,1,3458,'Fiji'),(9995,1,3457,'Finland'),(9994,1,3456,'Ethiopia'),(9993,1,3455,'Estonia'),(9992,1,3454,'Spain'),(9991,1,3453,'Eritrea'),(9990,1,3452,'Egypt'),(9989,1,3451,'Ecuador'),(10273,1,3735,'Search by name'),(9988,1,3450,'Algeria'),(9987,1,3449,'Dominican Republic'),(9986,1,3448,'Denmark'),(9985,1,3447,'Dominica'),(9979,1,3441,'Cuba'),(9980,1,3442,'Cayman Islands'),(9981,1,3443,'Cyprus'),(9982,1,3444,'Czech Republic'),(9983,1,3445,'Germany'),(9984,1,3446,'Djibouti'),(9978,1,3440,'Costa Rica'),(9976,1,3438,'Comoros'),(9977,1,3439,'Cape Verde'),(9974,1,3436,'Cook Islands'),(9975,1,3437,'Colombia'),(9972,1,3434,'The Democratic Republic Of The Congo'),(9973,1,3435,'Congo'),(9971,1,3433,'Cameroon'),(9970,1,3432,'Cote D\'ivoire'),(9969,1,3431,'China'),(9968,1,3430,'Chile'),(9967,1,3429,'Switzerland'),(9966,1,3428,'Canada'),(9964,1,3426,'Botswana'),(9965,1,3427,'Central African Republic'),(9963,1,3425,'Bouvet Island'),(9962,1,3424,'Bhutan'),(9961,1,3423,'Brunei Darussalam'),(9960,1,3422,'Barbados'),(9959,1,3421,'Brazil'),(9958,1,3420,'Bolivia'),(9957,1,3419,'Bermuda'),(9956,1,3418,'Belize'),(9955,1,3417,'Belarus'),(9954,1,3416,'Bosnia And Herzegovina'),(9953,1,3415,'Bahamas'),(9952,1,3414,'Bahrain'),(9951,1,3413,'Bulgaria'),(9949,1,3411,'Burkina Faso'),(9950,1,3412,'Bangladesh'),(9948,1,3410,'Benin'),(9947,1,3409,'Belgium'),(9946,1,3408,'Burundi'),(9945,1,3407,'Azerbaijan'),(9944,1,3406,'Austria'),(9943,1,3405,'Australia'),(9941,1,3403,'French Southern Territories'),(9942,1,3404,'Antigua And Barbuda'),(9940,1,3402,'Antarctica'),(9939,1,3401,'American Samoa'),(9938,1,3400,'Armenia'),(9937,1,3399,'Argentina'),(9936,1,3398,'United Arab Emirates'),(9935,1,3397,'Netherlands Antilles'),(9934,1,3396,'Andorra'),(43374,1,9401,'View profiles'),(9933,1,3395,'Albania'),(9932,1,3394,'Anguilla'),(10308,1,3770,'SPAM'),(9931,1,3393,'Angola'),(9930,1,3392,'Afghanistan'),(9929,1,3391,'Aruba'),(9921,1,3383,'Offline mode changed'),(9919,1,3381,'Offline mode message'),(9920,1,3382,'HTML allowed'),(9918,1,3380,'<p>If you mangaging your site or doing some developing work and do not want your users access the site during the work.</p>\r\n<b>Site admins can login through: <a href=\"{$site_url}login\">{$site_url}login</a></b>'),(9917,1,3379,'Set your site to offline mode.'),(43435,1,9462,'Base'),(9911,1,3373,'Settings'),(9912,1,3374,'<h1 class=\"peep_splash_offline_warning\">We are Offline Now.</h1>\r\nWe are doing some maintenance work for the site. We\'ll be back soon.'),(9910,1,3372,'Site Offline Mode'),(9909,1,3371,'Site Offline Mode'),(10314,1,3776,'Report sent'),(9894,1,3356,'<p>\r\nDear {$user_name},\r\n</p>\r\n<p>\r\nWe are glad to let you know that your account on <a href=\"{$site_url}\">{$site_name}</a> has been approved. Now you can sign in here: <a href=\"{$site_url}\">{$site_url}</a>\r\n</p>\r\n<p>\r\nWe hope you enjoy our site.\r\n</p>\r\n<p>\r\nThank you,<br />\r\nAdministration<br />\r\n<a href=\"{$site_url}\">{$site_name}</a>\r\n</p>'),(17041,1,4661,'Software upgrade'),(17042,1,4662,'Are you sure you want to upgrade the software from version <b>{$oldVersion}</b> to <b>{$newVersion}</b>?{$info}'),(9893,1,3355,'Dear {$user_name},\r\n\r\nWe are glad to let you know thatyour account on {$site_name} has been approved. Now you can sign in here: {$site_url}\r\n\r\nWe hope you enjoy our site to the fullest.\r\n\r\nThank you,\r\nAdministration\r\n{$site_name}\r\n{$site_url}'),(9892,1,3354,'Your account has been approved'),(17097,1,4717,'No'),(9889,1,3351,'Approve'),(9890,1,3352,'Updated'),(9846,1,3308,'User Grous'),(9847,1,3309,'Change Usergroup'),(9885,1,3347,'<center>Your account sent to our admins for approval</center>'),(14299,1,4103,'<a href=\"{$userUrl}\">{$userName}</a> commented on <a href=\"{$profileUrl}\">your profile</a>.'),(14297,1,4101,'Cancel'),(14298,1,4102,'Someone comments on my profile'),(14296,1,4100,'ok'),(14283,1,4087,'Are you sure you want to uninstall the plugin?'),(14286,1,4090,'Welcome back to {$site_name}'),(14287,1,4091,'or'),(14274,1,4078,'Password is too short (min 6 symbols)'),(14324,1,4128,'Verify'),(14327,1,4131,'You need to verify this email address.'),(14325,1,4129,'Send verification email'),(14326,1,4130,'Email already verified'),(14328,1,4132,'Verify email'),(14329,1,4133,'Hello,\r\n\r\nSomeone set this email address as default email address of {$site_name} ({$site_url}) website.\r\n\r\nTo complete this process you need to verify this email address by opening the following URL: {$url}\r\n\r\nOr you can open this URL: {$verification_page_url} and paste there the following code: {$code}\r\n\r\nIf you didn\'t did that ignore this message and your email will not be used.\r\n\r\nThank you,\r\nAdministration\r\n{$site_name}\r\n{$site_url}'),(14330,1,4134,'<p>\r\nHello,\r\n</p>\r\n<p>\r\nSomeone set this email address as the official email address of <a href=\"{$site_url}\">{$site_name}</a> website.\r\n</p>\r\n<p>\r\nTo complete this process you need to verify this email address by opening the following URL: <a href=\"{$url}\">{$url}</a>\r\n</p>\r\n<p>\r\nOr you can open <a href=\"{$verification_page_url}\">this URL</a> and paste there the following code: <b>{$code}</b>\r\n</p>\r\n<p>\r\nIf you didn\'t did that ignore this message and your email will not be used.\r\n</p>\r\n<p>\r\nThank you,<br />\r\nAdministration<br />\r\n{$site_name}<br />\r\n{$site_url}\r\n</p>'),(16978,1,4598,'Deleted'),(16975,1,4595,'Plugin uninstall error'),(14336,1,4140,'Tomorrow'),(14341,1,4145,'Plugin uninstall request'),(14342,1,4146,'Are you sure you want to uninstall \'{$name}\' plugin?'),(14345,1,4149,'Captcha validator error!'),(43378,1,9405,'Exclusive fields'),(20385,1,6080,'12321'),(20384,1,6079,'213'),(20378,1,6073,'Post jobs'),(19881,1,5576,'Next time you\'ll be able to start sending newsletter not earlier than in <b>{$hours}</b> hours.'),(20151,1,5846,'Hello {$username}, enter your new password below:'),(20152,1,5847,'Submit'),(20153,1,5848,'New password'),(20154,1,5849,'Repeat password'),(20156,1,5851,'Values in \'password\' and \'repeat password\' fields should be equal'),(20157,1,5852,'Password length should be between {$min} and {$max} symbols'),(20158,1,5853,'Your password successfully changed'),(16243,1,4164,'Date validator error!'),(16244,1,4165,'Value is incorrect or greater than server limit.'),(16245,1,4166,'Int validator error!'),(16246,1,4167,'Alphanumeric validator error!'),(16247,1,4168,'Url validator error!'),(16248,1,4169,'Email validator error!'),(16249,1,4170,'RegExp validator error!'),(16250,1,4171,'String validator error!'),(16900,1,4520,'Plugin upgrades available: <b>{$count}</b>. <a href=\"{$link}\">Check</a>'),(16898,1,4518,'Logout'),(16899,1,4519,'New software upgrade available. <a href=\"{$link}\">Upgrade now </a>'),(16896,1,4516,'My Profile'),(43396,1,9423,'Submit'),(16895,1,4515,'Preferences'),(16897,1,4517,'Mailbox'),(20141,1,5836,'You have already received reset code today. Please try again in 24 hours.'),(20138,1,5833,'There is no user with this email address'),(20137,1,5832,'Type the email address you used at registration.'),(20101,1,5796,'Membership'),(19427,1,5124,'When turned on users will have to verify email addresses before accessing the site.'),(19428,1,5125,'Email Settings'),(19429,1,5126,'Confirm email'),(20150,1,5845,'To reset password you just need to enter your email address, get our reset code and use it to change your old password.'),(20149,1,5844,'Create new password'),(20146,1,5841,'Reset password request'),(20147,1,5842,'Please enter valid reset code'),(20148,1,5843,'Reset your password'),(20145,1,5840,'Submit'),(20142,1,5837,'Forgot password'),(20143,1,5838,'Enter your reset code'),(20144,1,5839,'Type in the reset code sent to you. Then you can enter a new password.'),(19242,1,4939,'Plugin successfully upgraded'),(19069,1,4766,'My profile'),(19070,1,4767,'Earnings'),(19073,1,4770,'Earnings'),(19072,1,4769,'Sorry, this username is restricted'),(19075,1,4772,'Restricted Usernames'),(19076,1,4773,'Restricted Usernames'),(19077,1,4774,'Add restricted username'),(19078,1,4775,'Username'),(19079,1,4776,'Add'),(19080,1,4777,'Restricted Usernames List'),(19086,1,4783,'Username successfully added'),(19088,1,4785,'The source code of \'<i>{$name}</i>\' plugin was changed. Need to upgrade plugin DB.'),(19089,1,4786,'Upgrade'),(19096,1,4793,'Payment gateway not found or is inactive'),(19113,1,4810,'Information about order was not found'),(19114,1,4811,'Failed to initialize order'),(19135,1,4832,'Plugin'),(19145,1,4842,'Time'),(19146,1,4843,'Amount'),(19157,1,4854,'Details'),(19158,1,4855,'Gateway'),(19159,1,4856,'Transaction ID'),(19160,1,4857,'Statistics'),(19171,1,4868,'Year Range'),(19172,1,4869,'(Year Range)'),(19173,1,4870,'unavailable'),(19174,1,4871,'Your order has been canceled'),(19175,1,4872,'An error occured during your order processing'),(19176,1,4873,'Your order has been completed successfully'),(19177,1,4874,'Your order has been verified'),(19178,1,4875,'Your order is being processed'),(19209,1,4906,'Pay with {$gateway}'),(19210,1,4907,'Your order status'),(20355,1,6050,'Dear {$username},\r\n<br />\r\nYou requested to reset your password. Follow this link ({$resetUrl}) to change your password.\r\n<br />\r\nIf the link doesn\'t work, please enter the code manually here ({$requestUrl}). Code: {$code}\r\n<br />\r\nIf you didn\'t request password reset, please ignore this email.\r\n<br /><br />\r\nThank you,\r\n{$site_name}<br />'),(20353,1,6048,'The content owner can delete comments'),(20350,1,6045,'<b>We recommend against making username field editable, because changing username will result in changing the profile URL.</b>'),(20356,1,6051,'Dear {$username},\r\n\r\nYou requested to reset your password. Follow this link ({$resetUrl}) to change your password.\r\n\r\nIf the link doesn\'t work, please enter the code manually here ({$requestUrl}). Code: {$code}\r\n\r\nIf you didn\'t request password reset, please ignore this email.\r\n\r\nThank you,\r\n{$site_name}'),(20351,1,6046,'Market'),(20352,1,6047,'Comment posted jobs'),(20325,1,6020,'Installation'),(20326,1,6021,'Troubleshooting'),(20327,1,6022,'Plugin Development'),(20328,1,6023,'Theme Design'),(20329,1,6024,'Consulting'),(20703,1,6158,'The content owner can delete comments'),(20704,1,6159,'View Links'),(20700,1,6155,'Links'),(20701,1,6156,'Write comments'),(20702,1,6157,'Add a link'),(43269,1,9296,'Admin'),(43268,1,9295,'Search people'),(43267,1,9294,'Delete comment by content owner'),(43258,1,9285,'Add new usergroup'),(43259,1,9286,'User group permissions'),(62018,1,18588,'changed their profile pic'),(43270,1,9297,'Edit types'),(43264,1,9291,'Add comments'),(43266,1,9293,'{$count} values'),(43466,1,9493,'Submit'),(43348,1,9375,'Field'),(43350,1,9377,'Type'),(43354,1,9381,'Theme Info'),(43355,1,9382,'Values'),(43359,1,9386,'Here you can view existing pages and menu items on your site. These are your own custom pages and those activated by plugins. <span class=\"peep_highlight\">Drag\'n\'drop</span> items across menus to change their location.'),(43360,1,9387,'Main Menu'),(43361,1,9388,'Require'),(43362,1,9389,'Footer Menu'),(43363,1,9390,'Hidden Pages'),(43364,1,9391,'Register'),(43365,1,9392,'Here are pages that actually exist but are not shown on the site. <span class=\"peep_highlight\">Drag\'n\'drop</span> here those items that you want to hide.'),(43366,1,9393,'Edit'),(43368,1,9395,'View'),(43369,1,9396,'Item'),(43370,1,9397,'visible to everyone'),(43371,1,9398,'Search'),(43445,1,9472,'Peepmatches <b>{$version}</b>'),(43442,1,9469,'Last Login'),(43440,1,9467,'online now'),(23349,1,6301,'Spaces are not allowed'),(23323,1,6275,'First you should remove this user from site moderators'),(51589,1,11573,'Can\'t unzip downloaded archive'),(25807,1,6823,'Plugin is uploaded and putted in available plugins list'),(46497,1,9726,'12312'),(37391,1,9251,'Tag Search'),(25765,1,6781,'Send Message'),(25764,1,6780,'Read incoming message'),(25763,1,6779,'Mailbox'),(25698,1,6714,'You should select user(s) to continue'),(25680,1,6696,'edited their profile information'),(25681,1,6697,'has been a part in our site!'),(36714,1,8574,'Send gift'),(36713,1,8573,'Virtual Gifts'),(25654,1,6678,'Total: <b>{$count}</b> users'),(25653,1,6677,'Unfortunately you reset code is invalid or expired. Please follow the <a href=\"{$url}\">link</a> and try to reset it again.'),(25651,1,6675,'Expired reset code'),(25650,1,6674,'Disable JavaScript'),(25649,1,6673,'Enable JavaScript'),(25647,1,6671,'changed their profile pic'),(37169,1,9029,'Newsfeed'),(37170,1,9030,'Allow comments'),(46498,1,9727,'123123'),(46499,1,9728,'12312312'),(36804,1,8664,'User Credits'),(46463,1,9694,'required'),(36056,1,7916,'Add event'),(36057,1,7917,'View event'),(36058,1,7918,'Comment events'),(46458,1,9689,'Settings not changed'),(46457,1,9688,'Settings changed'),(46456,1,9687,'Newsletter'),(25547,1,6571,'Forbidden'),(25546,1,6570,'Page not found'),(25545,1,6569,'You don\'t have permission to access the page.'),(25544,1,6568,'Forbidden'),(37128,1,8988,'View topic'),(37129,1,8989,'Create/Edit topic'),(35552,1,7429,'No people found'),(25492,1,6516,'{$username} <span class=\"peep_small\">{$status}</span>'),(46452,1,9683,'Settings'),(37133,1,8993,'Show on avatar'),(37134,1,8994,'Yes'),(37135,1,8995,'No'),(37136,1,8996,'Display usergroup on avatar'),(37127,1,8987,'Forum'),(25474,1,6498,'Submit'),(25473,1,6497,'#count# users selected'),(37137,1,8997,'Label color'),(37139,1,8999,'Usergroup updated'),(25465,1,6489,'No people found'),(25463,1,6487,'Default avatar deleted'),(25453,1,6477,'Change'),(25454,1,6478,'Cancel'),(30740,1,7218,'Can\'t setup plugin with empty key'),(25462,1,6486,'Are sure you want to delete default avatar image?'),(25449,1,6473,'Change to override theme default avatar image'),(51593,1,11577,'To what URL should this link go:'),(58755,1,15472,'Back to search'),(58754,1,15471,'Pull data from other services'),(58753,1,15470,'Can\'t extract theme archive'),(59521,1,15755,''),(59520,1,15412,''),(59519,1,15753,''),(59518,1,15752,''),(59517,1,15751,''),(59516,1,15410,''),(59515,1,2871,''),(59514,1,2869,''),(59513,1,6242,''),(59512,1,6241,''),(59511,1,15731,''),(59510,1,2870,''),(58747,1,15464,'Please upload valid theme archive file'),(58748,1,15465,'Add New Theme'),(58749,1,15466,'Add new theme'),(58750,1,15467,'Add new theme'),(58751,1,15468,'Theme successfully added'),(58752,1,15469,'Can\'t upload theme, theme directory `{$dir}` already exists'),(25382,1,6406,'People'),(37130,1,8990,'Subscribe to forum topics'),(25354,1,6378,'View all ({$count})'),(25353,1,6377,'Theme custom CSS edited'),(25352,1,6376,'Invalid FTP details! Provided user doesn\'t have permissions to overwrite files.'),(25342,1,6366,'Default avatar image'),(25239,1,6306,'Mark email verified'),(25241,1,6308,'Selected user(s) emails are verified'),(27933,1,6890,'You don\'t have available plugins here.'),(47077,1,10306,'custom page'),(37349,1,9209,'<span class=\"peep_small\">Total <b>{$avgScore}</b></span> <span class=\"peep_outline\">{$ratesCount}</span> rates'),(37348,1,9208,'Your rate:'),(37313,1,9173,'Daily login'),(37314,1,9174,'User join'),(37315,1,9175,'Search people'),(37316,1,9176,'Comment on people profiles'),(46496,1,9725,'112'),(46464,1,9695,'hide'),(37273,1,9133,'User Groups'),(46455,1,9686,'Save'),(37269,1,9129,'Actions'),(37246,1,9106,'Checkout'),(43390,1,9417,'Please enter tag'),(43375,1,9402,'Legend'),(43376,1,9403,'or'),(43377,1,9404,'Account type'),(51312,1,11296,'Please enter comment message'),(51328,1,11312,'Basic'),(51327,1,11311,'Profile field settings'),(51326,1,11310,'View all fields'),(30826,1,7304,'Language pack imported'),(30824,1,7302,'Empty or invalid language pack. Please select valid one and try again.'),(30741,1,7219,'Errors occurred while installing plugin \"{$plugin}\". Please uninstall it and try again.'),(51595,1,11579,'No favicon. Check it to enable.'),(35651,1,7528,'Join Date'),(59509,1,15730,''),(35968,1,7828,'Peepmatches cron script is not active. Please add it to cron jobs list ({$path}).'),(51590,1,11574,'Text to display:'),(36055,1,7915,'Events'),(62005,1,18575,'Suspend Users'),(62006,1,18576,'Avatar file size limit'),(62007,1,18577,'The file size is greater than server limit: {$limit} Mb.'),(62008,1,18578,'Invalid theme archive'),(62009,1,18579,'Are you sure you want to delete this section?\r\n\r\nAll profile fields relate to this section will be moved to the \"{$sectionName}\" section.'),(47059,1,10288,'Insert HTML'),(47060,1,10289,'{$site_name} registration'),(47024,1,10253,'View more'),(47037,1,10266,'Bold'),(47038,1,10267,'Italic'),(47039,1,10268,'Underline'),(47040,1,10269,'Insert Ordered List'),(47041,1,10270,'Insert Unordered List'),(47042,1,10271,'Insert Link'),(47043,1,10272,'Insert Image'),(47044,1,10273,'Insert Video'),(47045,1,10274,'Insert HTML'),(47046,1,10275,'Show/Hide HTML Source View'),(47053,1,10282,'Add'),(47054,1,10283,'Insert'),(47055,1,10284,'Please fill `label` and `url` fields to insert the link'),(47056,1,10285,'Insert video'),(47058,1,10287,'Enter video code please'),(54914,1,11631,'Can\'t upload archive by FTP'),(47536,1,10742,'test'),(47537,1,10743,'mmm'),(47535,1,10741,'custom page'),(47534,1,10740,'custom page'),(59508,1,15729,''),(47531,1,10737,'custom page'),(47532,1,10738,'Test installation on XAMP'),(47533,1,10739,'mmm'),(47528,1,10734,'custom page'),(59507,1,6887,''),(47524,1,10730,'test'),(47525,1,10731,'custom page'),(59506,1,6886,''),(47527,1,10733,'mmm'),(47523,1,10729,'test'),(59505,1,15718,''),(47521,1,10727,'dsfsdfs\r\nsdfsfsdfsd\r\nsdfsdfsdf'),(47520,1,10726,'Make \'Photo Upload\' a required field on Join Page.'),(59504,1,15717,''),(59503,1,15716,''),(47519,1,10725,'Make \'Photo Upload\' a required field'),(59502,1,15712,''),(59501,1,15711,''),(59500,1,6885,''),(59499,1,6884,''),(59498,1,6883,''),(59497,1,6882,''),(59496,1,6881,''),(59495,1,6880,''),(59494,1,6879,''),(59493,1,6878,''),(59492,1,15676,''),(59491,1,15669,''),(59490,1,16101,'123'),(59489,1,16100,''),(59488,1,16099,''),(59487,1,16098,''),(59486,1,16097,''),(59485,1,16096,''),(59484,1,16095,''),(59483,1,16094,''),(47495,1,6861,'Hour'),(47496,1,6862,'Minute'),(59482,1,16093,''),(59481,1,16092,''),(59480,1,16091,''),(59479,1,16090,''),(59478,1,16089,''),(59477,1,16088,''),(59476,1,16087,''),(59475,1,16086,''),(59474,1,16085,''),(59473,1,16084,''),(59472,1,16083,''),(59471,1,16082,''),(59470,1,16081,''),(59469,1,16080,''),(59468,1,16079,''),(59467,1,16078,''),(59466,1,16077,''),(47464,1,10692,'Groups'),(47463,1,10691,'The author can delete wall comments'),(47462,1,10690,'Create groups'),(47461,1,10689,'Create/Edit topic'),(47460,1,10688,'Write on comment wall'),(47449,1,10678,'jjj'),(47450,1,10679,'<meta>custom meta</meta>'),(47451,1,10680,'jjj'),(47458,1,10687,'Dear {$username},\r\n\r\nYou requested to reset your password. Here\'s your new password: {$password}\r\n\r\nFeel free to log in to your account at {$site_url} and change your password again if necessary.\r\n\r\nThank you,\r\n{$site_name}'),(47447,1,10676,'Please provide valid image in ico format'),(47456,1,10685,'User Settings'),(47440,1,10669,'Check if you don\'t want to allow people to add rich media (like photo and video) in comments throughout the site.'),(47457,1,10686,'Dear {$username},<br />\r\n<br />\r\nYou requested to reset your password. Here\'s your new password: <b>{$password}</b><br />\r\n<br />\r\nFeel free to log in to your account at <a href=\"{$site_url}\">{$site_url}</a> and change your password again if necessary.<br />\r\n<br />\r\nThank you,<br />\r\n{$site_name}'),(47397,1,10626,'Show people that i am online at the site'),(47435,1,10664,'Disable custom HTML'),(47394,1,10623,'List domains from which you allow to embed videos or put iframes. These should be sites that provide embed code for videos or valid content which you entrust. One domain per line.'),(47436,1,10665,'Check if you don\'t want to allow users write HTML code in posts (like blogs and forums). Recommended for security reasons.'),(47437,1,10666,'Disable rich media'),(47438,1,10667,'Check if you don\'t want to allow people to add rich media (like photo and video) in posts (like blogs and forums).'),(47390,1,10619,'Content input'),(47389,1,10618,'General'),(47385,1,10614,'Visitors comments on profiles'),(47384,1,10613,'Video embed code:'),(47383,1,10612,'Your html code:'),(47382,1,10611,'User input settings'),(47381,1,10610,'Allowed resources list'),(62090,1,18660,'Invalid range'),(47380,1,10609,'Maximum upload file size'),(47378,1,10607,'Rich media settings'),(47376,1,10605,'Content comments'),(47439,1,10668,'Disable rich media'),(47373,1,10602,'Text content'),(47372,1,10601,'User input settings'),(47352,1,10581,'Member edit information'),(47351,1,10580,'New user registration'),(47349,1,10578,'More'),(47079,1,10308,'custom page'),(62010,1,18580,'Please provide a reason for suspend.'),(62011,1,18581,'People'),(62012,1,18582,'Avatar has been changed'),(62013,1,18583,'or choose from your library'),(62014,1,18584,'You can drop only 1 image to this area'),(62015,1,18585,'Avatar has been approved'),(62016,1,18586,'This photo is too small to be set as avatar. <br />Minimum size is {$width}px x {$height}px'),(62017,1,18587,'Avatar is pending approval'),(46655,1,9884,'Website icon in .ico format (16x16px)'),(46654,1,9883,'Favicon'),(46652,1,9881,'Custom tail code'),(46653,1,9882,'Code added to HTML document right before closing &lt;/BODY&gt; tag (custom Javascript).'),(46651,1,9880,'Code added to HTML document head area (custom Javascript/CSS or META info).'),(46649,1,9878,'Settings saved'),(46650,1,9879,'Custom head code'),(46648,1,9877,'An error occurred while saving data, please try again'),(46646,1,9875,'Global page settings'),(46645,1,9874,'Page settings'),(46643,1,9872,'User Settings'),(46642,1,9871,'Error downloading software upgrade'),(46641,1,9870,'View my profile'),(46679,1,9908,'values'),(46680,1,9909,'Edit Language'),(46681,1,9910,'RTL'),(46682,1,9911,'Check it if you need \"right to left\" text direction'),(46683,1,9912,'Language edited, please wait...'),(46684,1,9913,'Language edit failed, please try again'),(46336,1,9567,'Use military time'),(46337,1,9568,'Usergroups'),(46461,1,9692,'Uncheck this box to stop receiving newsletters'),(46309,1,9540,'I agree with <a target=\'blank\' href=\'{$site_url}terms-of-use\' >terms of use</a>'),(59815,1,16385,'Order'),(46310,1,9541,'Terms of use'),(46308,1,9539,'Terms of use'),(46311,1,9542,'Terms of use'),(46303,1,9534,'Let users upload avatar on registration'),(46301,1,9532,'Require users to agree to terms of use on registration'),(46300,1,9531,'Registration'),(46299,1,9530,'Enable \'Terms of use\' checkbox'),(46313,1,9544,'Terms of use'),(46297,1,9528,'Avatar upload'),(46291,1,9522,'Clone language'),(46275,1,9506,'werwrw'),(46276,1,9507,'wer'),(46277,1,9508,'wr'),(46278,1,9509,'wer'),(46279,1,9510,'ew'),(46280,1,9511,'r'),(46281,1,9512,'w'),(46263,1,9494,'Recent Joined Members'),(46260,1,9411,'People Online Now'),(46264,1,9495,'Count'),(50855,1,10839,'sdfsdfsdfsdf1333'),(54917,1,11634,'test'),(54918,1,11635,'(Server limit {$value} Mb)'),(51594,1,11578,'Open in new window'),(51334,1,11318,'<span class=\"peep_highlight\">Drag\'n\'drop</span> items to the page to add features or custom components.\n            These are items activated by <a href=\"{$pluginsUrl}\">plugins</a>. If you want an element to stay on all pages put it to the sidebar.'),(51333,1,11317,'Items visibility:\r\n            <span class=\"peep_mild_green peep_small\" style=\"border: 1px solid rgb(204, 204, 204); padding: 1px 3px;\">users only</span>\r\n            <span class=\"peep_mild_red peep_small\" style=\"border: 1px solid rgb(204, 204, 204); padding: 1px 3px;\">guests only</span>\r\n            <span class=\"peep_small\" style=\"border: 1px solid rgb(204, 204, 204); padding: 1px 3px;\">everyone</span>'),(51329,1,11313,'Theme CSS'),(51330,1,11314,'View all pages/menus'),(50963,1,10947,'sf'),(50962,1,10946,'fa'),(50960,1,10944,'asf'),(50961,1,10945,'as'),(50958,1,10942,'asf'),(50959,1,10943,'as'),(50955,1,10939,'safdas'),(50950,1,10934,'File upload fail!'),(50957,1,10941,'asfasf'),(50956,1,10940,'asfas'),(50949,1,10933,'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop.'),(50948,1,10932,'Failed to write file to disk'),(50945,1,10929,'The uploaded file was only partially uploaded.'),(50946,1,10930,'No file was uploaded.'),(50947,1,10931,'Missing a temporary folder.'),(50943,1,10927,'attachment'),(50944,1,10928,'The uploaded file exceeds the max upload filesize.'),(50942,1,10926,'Add video'),(50941,1,10925,'Attach photo'),(50939,1,10923,'Add'),(50940,1,10924,'Attach video'),(50851,1,10835,'Found: <b>{$count}</b> users'),(50852,1,10836,'Search by'),(58644,1,15361,'User groups'),(58645,1,15362,'Value is incorrect or greater than server limit.'),(58646,1,15363,'Selected user(s) emails are marked unverified'),(58647,1,15364,'Move topics into hidden sections'),(58648,1,15365,'Gold'),(58649,1,15366,'Silver'),(58650,1,15367,'Silver'),(58651,1,15368,'Billing gateway products binding'),(58652,1,15369,'Product'),(58653,1,15370,'Gateway product ID'),(58654,1,15371,'Block confirmation'),(58655,1,15372,'Cancel'),(58656,1,15373,'Confirm'),(58657,1,15374,'ppp'),(58658,1,15375,'base+local_page_content_page_61525903'),(58659,1,15376,'ppp'),(58660,1,15377,'base+local_page_meta_tags_page_61525903'),(58661,1,15378,'ppp'),(58662,1,15379,'base+local_page_title_page_61525903'),(58663,1,15380,'Follow this <a href=\"{$url}\">link</a> to disable offline mode'),(58664,1,15381,'Mark email unverified'),(58665,1,15382,'Please confirm you do not want to receive newsletter from this site.'),(58666,1,15383,'ppp'),(59465,1,16076,'Ignore'),(58668,1,15385,'test11'),(58669,1,15386,'test1'),(58670,1,15387,'test test test'),(58671,1,15388,'test'),(58672,1,15389,'test'),(58673,1,15390,'test1'),(58674,1,15391,'test1'),(58675,1,15392,'eee'),(58676,1,15393,'eee'),(58677,1,15394,'aaaa'),(58678,1,15395,'d'),(58679,1,15396,'ddd'),(58680,1,15397,'aaa'),(58681,1,15398,'d'),(58682,1,15399,'aaa2'),(58683,1,15400,'c'),(58684,1,15401,'a'),(58685,1,15402,'sss'),(58686,1,15403,'d'),(58687,1,15404,'33'),(58688,1,15405,'d'),(58689,1,15406,'s'),(58690,1,15407,'a'),(58691,1,15408,'44'),(58692,1,15409,'d'),(59464,1,16075,''),(58694,1,15411,'I can help others with'),(59463,1,16074,''),(58696,1,15413,'1'),(58697,1,15414,'test2'),(58698,1,15415,'test2'),(58699,1,15416,'Block'),(58700,1,15417,'Are you sure you want to block this user? <br /><br /> This will cancel and prevent all interaction with you.'),(58701,1,15418,'This user blocked you'),(58702,1,15419,'Profile blocked'),(58703,1,15420,'Profile unblocked'),(58704,1,15421,'Unblock'),(58756,1,15473,'Invalid plugin archive'),(58758,1,15475,'Can\'t update plugin'),(58759,1,15476,'Plugin is up to date'),(58760,1,15477,'Can\'t extract plugin archive files'),(58761,1,15478,'Invalid or empty upgrade pack archive'),(58762,1,15479,'Plugin not found'),(58763,1,15480,'Reset code already sent. Please try again in 10 minutes.'),(58764,1,15481,'Selected user(s) were approved'),(58765,1,15482,'Selected user(s) were disapproved'),(58766,1,15483,'The max reasonable avatar size is {$max}px'),(58767,1,15484,'Avatar image<br /> crop size'),(58768,1,15485,'The max reasonable big avatar size is {$max}px'),(58769,1,15486,'Big avatar image<br /> crop size'),(58770,1,15487,'Peepmatches can\'t connect to upgrade server. Seems you need to enable `url_fopen` in your php settings.'),(58771,1,15488,'Change Image'),(58772,1,15489,'Choose image'),(58773,1,15490,'Approve'),(58774,1,15491,'Disapprove'),(58775,1,15492,'commented on {$user}\'s new avatar'),(58776,1,15493,'liked {$user}\'s new avatar'),(58777,1,15494,'commented on {$user}\'s registered to site'),(58778,1,15495,'liked that {$user} registered site'),(58779,1,15496,'Avatars folder is missing or not writable.'),(58780,1,15497,'The file extention is not allowed.'),(58784,1,15501,'qqqqqqqqqqqqqqqqqqqqqqq'),(58785,1,15502,'3333333333'),(58786,1,15503,'fdbfbfd'),(58787,1,15504,'bfbdf'),(58788,1,15505,'rtttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttttt'),(58789,1,15506,'eyrtyetrwau'),(58790,1,15507,'eewiura'),(58791,1,15508,'ddd'),(58792,1,15509,'ddd'),(58871,1,15588,'Setup the cron job manually. <a href=\"{$helpUrl}\">Learn more</a>'),(58796,1,15513,'Range'),(58872,1,15589,'Invalid plugin key - `{$key}` provided.'),(58798,1,15515,'Quick Links'),(58799,1,15516,'Sort by'),(58800,1,15517,'Tags'),(58801,1,15518,'Account has been removed'),(58803,1,15520,'Privacy Policy'),(59462,1,16073,''),(58805,1,15522,'site privacy policy here...'),(58806,1,15523,'Admin Board'),(58807,1,15524,'Manage Pages'),(58808,1,15525,'Manage Plugins'),(58809,1,15526,'Current Theme Settings'),(58810,1,15527,'Manage Peoples'),(58811,1,15528,'User avatar change'),(58812,1,15529,'Language activated.'),(58813,1,15530,'Language deactivated.'),(58814,1,15531,'Language deleted.'),(58815,1,15532,'Language cloning failed'),(58816,1,15533,'Invalid Language Code.'),(58817,1,15534,'Username is already in the restricted list'),(58818,1,15535,'Username has been removed from restricted list'),(58819,1,15536,'admin'),(58820,1,15537,'Allow profile wall posts'),(58821,1,15538,'Invisible'),(58822,1,15539,'Welcome to {$site_name}!<br /><br />\r\nHere you can add contacts and meet new people for<div class=\"base_welcome_icos\"><div class=\"base_welcome_ico1\">Dating</div><div class=\"base_welcome_ico2\">Chating</div><div class=\"base_welcome_ico2\">Share Stories</div></div><a class=\"base_welcome_last\" href=\"{$site_url}createaccount\">Register and discover more</a> '),(58823,1,15540,'This is an admin you can\'t delete site admins.'),(58824,1,15541,'Invitations'),(58825,1,15542,'Register'),(58826,1,15543,'Delete comment'),(58827,1,15544,'Delete member'),(58828,1,15545,'Nothing to show'),(58829,1,15546,'Deleted member'),(58830,1,15547,'You cannot report your content'),(59461,1,16072,''),(59460,1,16071,''),(58833,1,15550,'mmm'),(58834,1,15551,'grey'),(59459,1,16070,''),(59458,1,16069,''),(58837,1,15554,'grey'),(58838,1,15555,'grey'),(58839,1,15556,'mmm'),(58840,1,15557,'Invalid URL'),(58841,1,15558,'grey'),(58842,1,15559,'test local'),(58843,1,15560,'htru'),(58844,1,15561,'mmm'),(58845,1,15562,'General'),(58846,1,15563,'Premium'),(58847,1,15564,'Match age desc'),(58848,1,15565,'match_age'),(58849,1,15566,'Education'),(58851,1,15568,'This text can\'t be longer than {$max_symbols_count} symbols'),(58852,1,15569,'Account has been removed'),(58853,1,15570,'Instant Chat'),(58873,1,15590,'You can\'t delete an account type while it still has exclusive profile fields.'),(58874,1,15591,'You can\'t delete default account type.'),(58875,1,15592,'Add Comment...'),(58876,1,15593,'grey'),(58877,1,15594,'Admin Tools'),(58878,1,15595,'Private'),(58879,1,15596,'gggggggggggggggg'),(58880,1,15597,'ggggggggggg'),(58881,1,15598,'sadas'),(58882,1,15599,'asdas'),(58883,1,15600,'asdasd'),(58884,1,15601,'fffffffffff'),(58885,1,15602,'fffffffffff'),(59457,1,16068,''),(58887,1,15604,'Address'),(58888,1,15605,'Age'),(58889,1,15606,'Birthdate'),(58890,1,15607,'Single choice (radiobutton)'),(58891,1,15608,'Dropdown Menu (Max 30 values)'),(58892,1,15609,'Location'),(58893,1,15610,'Enter tags...'),(58894,1,15611,'Visible for user groups'),(58895,1,15612,'View for?'),(58896,1,15613,'Back to theme list'),(58897,1,15614,'License Key'),(58898,1,15615,'Are you sure you want to upgrade <i>\'{$name}\'</i> theme from version <b>{$oldVersion}</b> to <b>{$newVersion}</b>? The theme is commercial, to upgrade it please enter license key and press \'Upgrade\' button.'),(58899,1,15616,'Warning'),(58900,1,15617,'Are you sure you want to upgrade <i>\'{$name}\'</i> theme from version <b>{$oldVersion}</b> to <b>{$newVersion}</b>?'),(58901,1,15618,'Theme not found'),(58902,1,15619,'Can\'t upgrade theme'),(58903,1,15620,'Theme upgrade'),(58904,1,15621,'Theme successfully upgraded'),(58905,1,15622,'Theme is up to date'),(59456,1,16067,''),(58907,1,15624,'Are you sure you want to delete this field?\r\n\r\nATTENTION: It will deleted from members profiles also.\r\n\r\nThis field also has a dependent field(s) {$questions}. IT WILL BE REMOVED WITH ALL PROFILE INFO AS WELL.'),(59816,1,16386,'Translation'),(59817,1,16387,'Add'),(58910,1,15627,'Please enter valid license key'),(58911,1,15628,'Invalid or empty upgrade pack archive'),(58912,1,15629,'Theme upgrade not available now'),(58913,1,15630,'Theme not found'),(59455,1,16066,''),(59454,1,16065,'Accept'),(59453,1,16064,''),(59452,1,15623,''),(58919,1,15636,'Ekip أ¼yesi'),(58920,1,15637,'ewrwe'),(59451,1,16063,''),(59450,1,16062,''),(59449,1,16061,''),(58924,1,15641,'Staff'),(59448,1,16060,''),(59447,1,16059,''),(58927,1,15644,'Desktop version'),(59446,1,16058,''),(58929,1,15646,'Main'),(58930,1,15647,'asdasdasdas\r\ndasdasdsadsa\r\ndsadasdsa'),(58931,1,15648,'tttt'),(58932,1,15649,'dsfsdfdsfdsfdsfdsfdsf'),(58933,1,15650,'test'),(58934,1,15651,'test2'),(59445,1,16057,''),(58936,1,15653,'tttt'),(58937,1,15654,'zsfsdf'),(58938,1,15655,'Test'),(58939,1,15656,'test2'),(58940,1,15657,'asdas'),(58941,1,15658,'ttttt'),(58942,1,15659,'test'),(58943,1,15660,'Test'),(58944,1,15661,'test2'),(58946,1,15663,'test'),(58947,1,15664,'ssd'),(58948,1,15665,'greyyyyyyyyyy'),(58949,1,15666,'testtttt google'),(58950,1,15667,'greyyyy'),(58951,1,15668,'{$username} | {$site_name}.'),(59444,1,16056,''),(58953,1,15670,'test'),(58954,1,15671,'Sport activities'),(58955,1,15672,'Football'),(58956,1,15673,'Tennis'),(58957,1,15674,'Basketball'),(58958,1,15675,'Volleyball'),(59443,1,16055,''),(58960,1,15677,'test1'),(58961,1,15678,'Hair'),(58962,1,15679,'Your hair color'),(59442,1,16054,''),(59441,1,16053,''),(58996,1,15713,'blond'),(58997,1,15714,'brown'),(58998,1,15715,'red'),(59440,1,16052,''),(59439,1,16051,''),(59438,1,16050,''),(59002,1,15719,'Hair'),(59003,1,15720,'black'),(59004,1,15721,'white'),(59005,1,15722,'red'),(59006,1,15723,'blue'),(59007,1,15724,'zsczxc'),(59008,1,15725,'zxc'),(59009,1,15726,'zxczx'),(59010,1,15727,'zxczx'),(59011,1,15728,'zxczx'),(59437,1,16049,''),(59436,1,16048,''),(59435,1,16047,''),(59015,1,15732,'Match Sport activities'),(59016,1,15733,'Football'),(59017,1,15734,'Tennis'),(59018,1,15735,'Basketball'),(59019,1,15736,'Volleyball'),(59020,1,15737,'Your sport activities'),(59021,1,15738,'Sport Activities'),(59022,1,15739,'Football'),(59023,1,15740,'Basketball'),(59024,1,15741,'Volleyball'),(59025,1,15742,'Tennis'),(59026,1,15743,'test1'),(59027,1,15744,'test1'),(59028,1,15745,'dddd'),(59029,1,15746,'dddd'),(59030,1,15747,'1'),(59031,1,15748,'2'),(59032,1,15749,'3'),(59033,1,15750,'Age'),(59434,1,16046,''),(59433,1,16045,''),(59432,1,16044,''),(59037,1,15754,'test'),(59431,1,16043,''),(59039,1,15756,'match hair'),(59040,1,15757,'black'),(59041,1,15758,'white'),(59042,1,15759,'red'),(59043,1,15760,'blue'),(59044,1,15761,'black'),(59045,1,15762,'white'),(59046,1,15763,'red'),(59047,1,15764,'blue'),(59430,1,16042,''),(59049,1,15766,'My match\'s eee'),(59429,1,16041,''),(59051,1,15768,'Match Age'),(59428,1,16040,''),(59054,1,15771,'About my match'),(59055,1,15772,'test'),(59056,1,15773,'People birthdays.'),(59057,1,15774,'The list of our participants: get to know your mates.'),(59058,1,15775,'Look who\'s online now.'),(59059,1,15776,'Search for People.'),(59060,1,15777,'About'),(59061,1,15778,'Adding...'),(59062,1,15779,'Footer menu'),(59063,1,15780,'New Menu item'),(59064,1,15781,'Content'),(59065,1,15782,'Page Title'),(59066,1,15783,'Hidden'),(59067,1,15784,'HTML Content'),(59068,1,15785,'Menu label'),(59069,1,15786,'Title'),(59070,1,15787,'New page'),(59071,1,15788,'Settings'),(59072,1,15789,'Top menu'),(59073,1,15790,'Hidden'),(59074,1,15791,'Content'),(59075,1,15792,'Menus'),(59076,1,15793,'Dashboard'),(59077,1,15794,'Index'),(59078,1,15795,'Profile'),(59079,1,15796,'{$site_name}'),(59080,1,15797,'{$site_name}'),(59081,1,15798,'Logout'),(59082,1,15799,'View My Profile'),(59427,1,16039,''),(59316,1,16033,'Terms of Use'),(59317,1,16034,'Terms of Use'),(59318,1,16035,'Your terms of use here...'),(59319,1,16036,'Home'),(59320,1,16037,'This is not available on mobile yet. Try <a href=\"{$url}\">desktop version</a>.'),(59321,1,16038,'Nothing to show'),(59530,1,2238,''),(59531,1,2872,''),(59533,1,16103,'Welcome back to {$site_name}'),(59813,1,16383,'New themes upgrades: <b>{$count}</b>. <a href=\"{$link}\">View</a>'),(59814,1,16384,'This page can not be opened in mobile version, please visit <a href=\"{$url}\">desktop version</a> of this site'),(59820,1,16390,'Invalid FTP details.'),(59821,1,16391,'Invalid FTP details! Empty username or password.'),(59822,1,16392,'Invalid FTP details! Empty host.'),(59823,1,16393,'Can\'t complete operation. Function `ftp_connect` is not available.'),(59824,1,16394,'FTP login error! Failed authentication, please check provided username and password.'),(59825,1,16395,'Settings'),(59826,1,16396,'File types permitted for upload. Enter one type per line, format: xxx'),(59827,1,16397,'Allowed extensions'),(59828,1,16398,'Maximum upload file size'),(59829,1,16399,'Can\'t add plugin. Duplicate plugin key error.'),(59830,1,16400,'&nbsp;'),(59831,1,16401,'Disable site mobile version'),(59832,1,16402,'General Mobile Settings'),(59833,1,16403,'You can\'t change values set in this field'),(59834,1,16404,'Account type has been updated'),(59835,1,16405,'Add Account Type'),(59836,1,16406,'Edit Account Type'),(59837,1,16407,'Add New Field'),(59838,1,16408,'Add Field'),(59839,1,16409,'Add Section'),(61894,1,18464,'This field is matched with <a href=\"{$url}\" class=\"parent_question_link\" parentId=\"{$parentId}\">{$label}</a>'),(59842,1,16412,'Edit Account Type'),(59843,1,16413,'Edit Profile Field'),(61891,1,18461,''),(61892,1,18462,'Add new values'),(61893,1,18463,'WAIT! Are you sure you want to delete this account type?\r\n\r\nRemember: Users with this account:\r\n1) will lose profile information associated to this account type;\r\n2) will not be able to use the site until they sign in, choose a different account type, and enter necessary information.'),(59845,1,16415,'Here you can change information added by users about themselves.<br>\r\nYou can change, rearrange, and add new profile fields.'),(59846,1,16416,'Save'),(59847,1,16417,'Account Types'),(59848,1,16418,'Settings'),(59849,1,16419,'You can`t delete active theme'),(59850,1,16420,'You can\'t delete default theme'),(59851,1,16421,'Delete'),(59852,1,16422,'Are you sure you want to delete `#theme#` theme?'),(59853,1,16423,'Theme deleted'),(59854,1,16424,'File attachments'),(59855,1,16425,'Settings'),(59856,1,16426,''),(59857,1,16427,'Please {$alternatives} to {$action}.'),(59858,1,16428,'Not Allowed'),(59859,1,16429,''),(59860,1,16430,''),(59861,1,16431,''),(59862,1,16432,''),(59863,1,16433,''),(59864,1,16434,''),(59865,1,16435,''),(59866,1,16436,''),(59867,1,16437,''),(59868,1,16438,''),(59869,1,16439,''),(59870,1,16440,''),(59871,1,16441,''),(59872,1,16442,''),(59873,1,16443,''),(59874,1,16444,''),(59875,1,16445,''),(59876,1,16446,''),(59877,1,16447,''),(59878,1,16448,''),(59879,1,16449,''),(59880,1,16450,''),(59881,1,16451,''),(59882,1,16452,''),(59883,1,16453,''),(59884,1,16454,''),(59885,1,16455,''),(59886,1,16456,''),(59887,1,16457,''),(59888,1,16458,''),(59889,1,16459,''),(59890,1,16460,''),(59891,1,16461,''),(59892,1,16462,''),(59893,1,16463,''),(59894,1,16464,''),(59895,1,16465,''),(59896,1,16466,''),(59897,1,16467,''),(59898,1,16468,''),(59899,1,16469,''),(59900,1,16470,''),(59901,1,16471,''),(59902,1,16472,''),(59903,1,16473,''),(59904,1,16474,''),(61895,1,18465,'Possible values'),(59906,1,16476,'Add New'),(59907,1,16477,'Not Allowed'),(59908,1,16478,'Send'),(59909,1,16479,'View more comments'),(59910,1,16480,'Complete information'),(59911,1,16481,'required values.'),(59912,1,16482,'Complete your profile information'),(59913,1,16483,'Complete information'),(59914,1,16484,'Continue'),(59915,1,16485,'Empty comment'),(59916,1,16486,'liked their new profile pic'),(59917,1,16487,'commented on their new profile pic'),(59918,1,16488,''),(59919,1,16489,''),(59920,1,16490,''),(59921,1,16491,''),(59922,1,16492,''),(59923,1,16493,''),(59924,1,16494,''),(59925,1,16495,''),(59926,1,16496,''),(59927,1,16497,''),(59928,1,16498,''),(59929,1,16499,''),(59930,1,16500,''),(59931,1,16501,''),(59932,1,16502,''),(59933,1,16503,''),(59934,1,16504,''),(59935,1,16505,''),(59936,1,16506,''),(59937,1,16507,''),(59938,1,16508,''),(59939,1,16509,''),(59940,1,16510,''),(59941,1,16511,''),(59942,1,16512,''),(59943,1,16513,''),(59944,1,16514,''),(59945,1,16515,''),(59946,1,16516,''),(59947,1,16517,''),(59948,1,16518,''),(59949,1,16519,''),(59950,1,16520,''),(59951,1,16521,''),(59952,1,16522,''),(59953,1,16523,''),(59954,1,16524,''),(59955,1,16525,''),(59956,1,16526,''),(59957,1,16527,''),(59958,1,16528,''),(59959,1,16529,''),(59960,1,16530,''),(59961,1,16531,''),(59962,1,16532,''),(59963,1,16533,''),(59964,1,16534,''),(59965,1,16535,''),(59966,1,16536,''),(59967,1,16537,''),(59968,1,16538,''),(59969,1,16539,''),(59970,1,16540,''),(59971,1,16541,''),(59972,1,16542,''),(59973,1,16543,''),(59974,1,16544,''),(59975,1,16545,''),(59976,1,16546,''),(59977,1,16547,''),(59978,1,16548,''),(59979,1,16549,''),(59980,1,16550,''),(59981,1,16551,''),(59982,1,16552,''),(59983,1,16553,''),(59984,1,16554,''),(59985,1,16555,''),(59986,1,16556,''),(59987,1,16557,''),(59988,1,16558,''),(59989,1,16559,''),(59990,1,16560,''),(59991,1,16561,''),(59992,1,16562,''),(59993,1,16563,''),(59994,1,16564,''),(59995,1,16565,''),(59996,1,16566,''),(59997,1,16567,''),(59998,1,16568,''),(59999,1,16569,''),(60000,1,16570,''),(60001,1,16571,''),(60002,1,16572,''),(60003,1,16573,''),(60004,1,16574,''),(60005,1,16575,''),(60006,1,16576,''),(60007,1,16577,''),(60008,1,16578,''),(60009,1,16579,''),(60010,1,16580,''),(60011,1,16581,''),(60012,1,16582,''),(60013,1,16583,''),(60014,1,16584,''),(60015,1,16585,''),(60016,1,16586,''),(60017,1,16587,''),(60018,1,16588,''),(60019,1,16589,''),(60020,1,16590,''),(60021,1,16591,''),(60022,1,16592,''),(60023,1,16593,''),(60024,1,16594,'Nothing to show'),(60025,1,16595,'or'),(60026,1,16596,''),(60027,1,16597,''),(60028,1,16598,''),(60029,1,16599,''),(60030,1,16600,''),(60031,1,16601,''),(60032,1,16602,''),(60033,1,16603,''),(60034,1,16604,''),(60035,1,16605,''),(60036,1,16606,''),(60037,1,16607,''),(60038,1,16608,''),(60039,1,16609,''),(60040,1,16610,''),(60041,1,16611,''),(60042,1,16612,''),(60043,1,16613,''),(60044,1,16614,''),(60045,1,16615,''),(60046,1,16616,''),(60047,1,16617,''),(60048,1,16618,''),(60049,1,16619,''),(60050,1,16620,''),(60051,1,16621,''),(60052,1,16622,''),(60053,1,16623,''),(60054,1,16624,''),(60055,1,16625,''),(60056,1,16626,''),(60057,1,16627,''),(60058,1,16628,''),(60059,1,16629,''),(60060,1,16630,''),(60061,1,16631,''),(60062,1,16632,''),(60063,1,16633,''),(60064,1,16634,''),(60065,1,16635,''),(60066,1,16636,''),(60067,1,16637,''),(60068,1,16638,''),(60069,1,16639,''),(60070,1,16640,''),(60071,1,16641,''),(60072,1,16642,''),(60073,1,16643,''),(60074,1,16644,''),(60075,1,16645,''),(60076,1,16646,''),(60077,1,16647,''),(60078,1,16648,''),(60079,1,16649,''),(60080,1,16650,''),(60081,1,16651,''),(60082,1,16652,''),(60083,1,16653,''),(60084,1,16654,''),(60085,1,16655,''),(60086,1,16656,''),(60087,1,16657,''),(60088,1,16658,''),(60089,1,16659,''),(60090,1,16660,''),(60091,1,16661,''),(60092,1,16662,''),(60093,1,16663,''),(60094,1,16664,''),(60095,1,16665,''),(60096,1,16666,''),(60097,1,16667,''),(60098,1,16668,''),(60099,1,16669,''),(60100,1,16670,''),(60101,1,16671,''),(60102,1,16672,'asdasd'),(60103,1,16673,''),(60104,1,16674,''),(60105,1,16675,''),(60106,1,16676,''),(60107,1,16677,'Add Another Account type'),(60109,1,16679,'Edit Field Description'),(60110,1,16680,'Edit Field Label'),(60111,1,16681,'Edit Field Value'),(60112,1,16682,''),(60113,1,16683,''),(60114,1,16684,''),(60115,1,16685,''),(60116,1,16686,''),(60117,1,16687,''),(60118,1,16688,''),(60119,1,16689,''),(60120,1,16690,''),(60121,1,16691,''),(60122,1,16692,''),(60123,1,16693,''),(60124,1,16694,''),(60125,1,16695,''),(60126,1,16696,''),(60127,1,16697,''),(60128,1,16698,''),(60129,1,16699,''),(60130,1,16700,''),(60131,1,16701,''),(60132,1,16702,''),(60133,1,16703,''),(60134,1,16704,''),(60135,1,16705,''),(60136,1,16706,''),(60137,1,16707,''),(60138,1,16708,''),(60139,1,16709,''),(60140,1,16710,''),(60141,1,16711,''),(60142,1,16712,''),(60143,1,16713,''),(60144,1,16714,''),(60145,1,16715,''),(60146,1,16716,''),(60147,1,16717,''),(60148,1,16718,''),(60149,1,16719,''),(60150,1,16720,''),(60151,1,16721,''),(60152,1,16722,''),(60153,1,16723,''),(60154,1,16724,''),(60155,1,16725,''),(60156,1,16726,''),(60157,1,16727,''),(60158,1,16728,''),(60159,1,16729,''),(60160,1,16730,''),(60161,1,16731,''),(60162,1,16732,''),(60163,1,16733,''),(60164,1,16734,''),(60165,1,16735,''),(60166,1,16736,''),(60167,1,16737,''),(60168,1,16738,''),(60169,1,16739,''),(60170,1,16740,''),(60171,1,16741,''),(60172,1,16742,''),(60173,1,16743,''),(60174,1,16744,''),(60175,1,16745,''),(60176,1,16746,''),(60177,1,16747,''),(60178,1,16748,''),(60179,1,16749,''),(60180,1,16750,''),(60181,1,16751,''),(60182,1,16752,''),(60183,1,16753,''),(60184,1,16754,''),(60185,1,16755,''),(60186,1,16756,''),(60187,1,16757,''),(60188,1,16758,''),(60189,1,16759,''),(60190,1,16760,''),(60191,1,16761,''),(60192,1,16762,''),(60193,1,16763,''),(60194,1,16764,''),(60195,1,16765,''),(60196,1,16766,''),(60197,1,16767,''),(60198,1,16768,''),(60199,1,16769,''),(60200,1,16770,''),(60201,1,16771,''),(60202,1,16772,''),(60203,1,16773,''),(60204,1,16774,''),(60205,1,16775,''),(60206,1,16776,''),(60207,1,16777,''),(60208,1,16778,''),(60209,1,16779,''),(60210,1,16780,''),(60211,1,16781,''),(60212,1,16782,''),(60213,1,16783,''),(60214,1,16784,''),(60215,1,16785,''),(60216,1,16786,''),(60217,1,16787,''),(60218,1,16788,''),(60219,1,16789,''),(60220,1,16790,''),(60221,1,16791,''),(60222,1,16792,''),(60223,1,16793,''),(60224,1,16794,''),(60225,1,16795,''),(60226,1,16796,''),(60227,1,16797,''),(60228,1,16798,''),(60229,1,16799,''),(60230,1,16800,''),(60231,1,16801,''),(60232,1,16802,''),(60233,1,16803,''),(60234,1,16804,''),(60235,1,16805,''),(60236,1,16806,''),(60237,1,16807,''),(60238,1,16808,''),(60239,1,16809,''),(60240,1,16810,''),(60241,1,16811,''),(60242,1,16812,''),(60243,1,16813,''),(60244,1,16814,''),(60245,1,16815,''),(60246,1,16816,''),(60247,1,16817,''),(60248,1,16818,''),(60249,1,16819,''),(60250,1,16820,''),(60251,1,16821,''),(60252,1,16822,''),(60253,1,16823,''),(60254,1,16824,''),(60255,1,16825,''),(60256,1,16826,''),(60257,1,16827,''),(60258,1,16828,'aaa'),(60259,1,16829,'bbb'),(60260,1,16830,'111'),(60261,1,16831,'nnn'),(60262,1,16832,''),(60263,1,16833,''),(60264,1,16834,''),(60265,1,16835,''),(60266,1,16836,''),(60267,1,16837,''),(60268,1,16838,''),(60269,1,16839,''),(60270,1,16840,''),(60271,1,16841,''),(60272,1,16842,''),(60273,1,16843,''),(60274,1,16844,''),(60275,1,16845,''),(60276,1,16846,''),(60277,1,16847,''),(60278,1,16848,''),(60279,1,16849,''),(60280,1,16850,''),(60281,1,16851,''),(60282,1,16852,''),(60283,1,16853,''),(60284,1,16854,''),(60285,1,16855,''),(60286,1,16856,''),(60287,1,16857,''),(60288,1,16858,''),(60289,1,16859,''),(60290,1,16860,''),(60291,1,16861,''),(60292,1,16862,''),(60293,1,16863,''),(60294,1,16864,''),(60295,1,16865,''),(60296,1,16866,''),(60297,1,16867,''),(60298,1,16868,''),(60299,1,16869,''),(60300,1,16870,''),(60301,1,16871,''),(60302,1,16872,''),(60303,1,16873,''),(60304,1,16874,''),(60305,1,16875,''),(60306,1,16876,''),(60307,1,16877,''),(60308,1,16878,''),(60309,1,16879,''),(60310,1,16880,''),(60311,1,16881,''),(60312,1,16882,''),(60313,1,16883,''),(60314,1,16884,''),(60315,1,16885,''),(60316,1,16886,''),(60317,1,16887,''),(60318,1,16888,''),(60319,1,16889,''),(60320,1,16890,''),(60321,1,16891,''),(60322,1,16892,''),(60323,1,16893,''),(60324,1,16894,''),(60325,1,16895,''),(60326,1,16896,''),(60327,1,16897,''),(60328,1,16898,''),(60329,1,16899,''),(60330,1,16900,''),(60331,1,16901,''),(60332,1,16902,''),(60333,1,16903,''),(60334,1,16904,''),(60335,1,16905,''),(60336,1,16906,''),(60337,1,16907,''),(60338,1,16908,''),(60339,1,16909,''),(60340,1,16910,''),(60341,1,16911,''),(60342,1,16912,''),(60343,1,16913,''),(60344,1,16914,''),(60345,1,16915,''),(60346,1,16916,''),(60347,1,16917,''),(60348,1,16918,''),(60349,1,16919,''),(60350,1,16920,''),(60351,1,16921,''),(60352,1,16922,''),(60353,1,16923,''),(60354,1,16924,''),(60355,1,16925,''),(60356,1,16926,''),(60357,1,16927,''),(60358,1,16928,''),(60359,1,16929,''),(60360,1,16930,''),(60361,1,16931,''),(60362,1,16932,''),(60363,1,16933,''),(60364,1,16934,''),(60365,1,16935,''),(60366,1,16936,''),(60367,1,16937,''),(60368,1,16938,''),(60369,1,16939,''),(60370,1,16940,''),(60371,1,16941,''),(60372,1,16942,''),(60373,1,16943,''),(60374,1,16944,''),(60375,1,16945,''),(60376,1,16946,''),(60377,1,16947,''),(60378,1,16948,''),(60379,1,16949,''),(60380,1,16950,''),(60381,1,16951,''),(60382,1,16952,''),(60383,1,16953,''),(60384,1,16954,''),(60385,1,16955,''),(60386,1,16956,''),(60387,1,16957,''),(60388,1,16958,''),(60389,1,16959,''),(60390,1,16960,''),(60391,1,16961,''),(60392,1,16962,''),(60393,1,16963,''),(60394,1,16964,''),(60395,1,16965,''),(60396,1,16966,''),(60397,1,16967,''),(60398,1,16968,''),(60399,1,16969,''),(60400,1,16970,''),(60401,1,16971,''),(60402,1,16972,''),(60403,1,16973,''),(60404,1,16974,''),(60405,1,16975,''),(60406,1,16976,''),(60407,1,16977,''),(60408,1,16978,''),(60409,1,16979,''),(60410,1,16980,''),(60411,1,16981,''),(60412,1,16982,''),(60413,1,16983,''),(60414,1,16984,''),(60415,1,16985,''),(60416,1,16986,''),(60417,1,16987,''),(60418,1,16988,''),(60419,1,16989,''),(60420,1,16990,''),(60421,1,16991,''),(60422,1,16992,''),(60423,1,16993,''),(60424,1,16994,'1'),(60425,1,16995,'8'),(60426,1,16996,'5'),(60427,1,16997,'2'),(60428,1,16998,'6'),(60429,1,16999,'3'),(60430,1,17000,'7'),(60431,1,17001,'4'),(60432,1,17002,''),(60433,1,17003,''),(60434,1,17004,''),(60435,1,17005,''),(60436,1,17006,''),(60437,1,17007,''),(60438,1,17008,''),(60439,1,17009,''),(60440,1,17010,''),(60441,1,17011,''),(60442,1,17012,''),(60443,1,17013,''),(60444,1,17014,''),(60445,1,17015,''),(60446,1,17016,''),(60447,1,17017,''),(60448,1,17018,''),(60449,1,17019,''),(60450,1,17020,''),(60451,1,17021,''),(60452,1,17022,''),(60453,1,17023,''),(60454,1,17024,''),(60455,1,17025,''),(60456,1,17026,''),(60457,1,17027,''),(60458,1,17028,''),(60459,1,17029,''),(60460,1,17030,''),(60461,1,17031,''),(60462,1,17032,''),(60463,1,17033,''),(60464,1,17034,''),(60465,1,17035,''),(60466,1,17036,''),(60467,1,17037,''),(60468,1,17038,''),(60469,1,17039,''),(60470,1,17040,''),(60471,1,17041,''),(60472,1,17042,''),(60473,1,17043,''),(60474,1,17044,''),(60475,1,17045,''),(60476,1,17046,''),(60477,1,17047,''),(60478,1,17048,''),(60479,1,17049,''),(60480,1,17050,''),(60481,1,17051,''),(60482,1,17052,''),(60483,1,17053,''),(60484,1,17054,''),(60485,1,17055,''),(60486,1,17056,''),(60487,1,17057,''),(60488,1,17058,''),(60489,1,17059,''),(60490,1,17060,''),(60491,1,17061,''),(60492,1,17062,''),(60493,1,17063,''),(60494,1,17064,''),(60495,1,17065,''),(60496,1,17066,''),(60497,1,17067,''),(60498,1,17068,''),(60499,1,17069,''),(60500,1,17070,''),(60501,1,17071,'0'),(60502,1,17072,'2'),(60503,1,17073,'3'),(60504,1,17074,'4'),(60505,1,17075,''),(60506,1,17076,''),(60507,1,17077,''),(60508,1,17078,''),(60509,1,17079,''),(60510,1,17080,''),(60511,1,17081,''),(60512,1,17082,''),(60513,1,17083,''),(60514,1,17084,''),(60515,1,17085,''),(60516,1,17086,''),(60517,1,17087,''),(60518,1,17088,''),(60519,1,17089,''),(60520,1,17090,''),(60521,1,17091,''),(60522,1,17092,''),(60523,1,17093,''),(60524,1,17094,''),(60525,1,17095,''),(60526,1,17096,''),(60527,1,17097,''),(60528,1,17098,''),(60529,1,17099,''),(60530,1,17100,''),(60531,1,17101,''),(60532,1,17102,''),(60533,1,17103,''),(60534,1,17104,''),(60535,1,17105,''),(60536,1,17106,''),(60537,1,17107,''),(60538,1,17108,''),(60539,1,17109,''),(60540,1,17110,''),(60541,1,17111,''),(60542,1,17112,''),(60543,1,17113,''),(60544,1,17114,''),(60545,1,17115,''),(60546,1,17116,''),(60547,1,17117,''),(60548,1,17118,''),(60549,1,17119,''),(60550,1,17120,''),(60551,1,17121,''),(60552,1,17122,''),(60553,1,17123,''),(60554,1,17124,''),(60555,1,17125,''),(60556,1,17126,''),(60557,1,17127,''),(60558,1,17128,''),(60559,1,17129,''),(60560,1,17130,''),(60561,1,17131,''),(60562,1,17132,''),(60563,1,17133,''),(60564,1,17134,''),(60565,1,17135,''),(60566,1,17136,''),(60567,1,17137,''),(60568,1,17138,''),(60569,1,17139,''),(60570,1,17140,''),(60571,1,17141,''),(60572,1,17142,''),(60573,1,17143,''),(60574,1,17144,''),(60575,1,17145,''),(60576,1,17146,''),(60577,1,17147,''),(60578,1,17148,''),(60579,1,17149,''),(60580,1,17150,''),(60581,1,17151,'aaa'),(60582,1,17152,'eee'),(60583,1,17153,'vvv'),(60584,1,17154,'ddd'),(60585,1,17155,'ggg'),(60586,1,17156,''),(60587,1,17157,''),(60588,1,17158,''),(60589,1,17159,''),(60590,1,17160,''),(60591,1,17161,''),(60592,1,17162,''),(60593,1,17163,''),(60594,1,17164,''),(60595,1,17165,''),(60596,1,17166,''),(60597,1,17167,''),(60598,1,17168,''),(60599,1,17169,''),(60600,1,17170,''),(60601,1,17171,''),(60602,1,17172,''),(60603,1,17173,''),(60604,1,17174,''),(60605,1,17175,''),(60606,1,17176,''),(60607,1,17177,''),(60608,1,17178,''),(60609,1,17179,''),(60610,1,17180,''),(60611,1,17181,''),(60612,1,17182,''),(60613,1,17183,''),(60614,1,17184,''),(60615,1,17185,''),(60616,1,17186,''),(60617,1,17187,''),(60618,1,17188,''),(60619,1,17189,''),(60620,1,17190,''),(60621,1,17191,''),(60622,1,17192,''),(60623,1,17193,''),(60624,1,17194,''),(60625,1,17195,''),(60626,1,17196,''),(60627,1,17197,''),(60628,1,17198,''),(60629,1,17199,''),(60630,1,17200,''),(60631,1,17201,''),(60632,1,17202,''),(60633,1,17203,''),(60634,1,17204,''),(60635,1,17205,''),(60636,1,17206,''),(60637,1,17207,''),(60638,1,17208,''),(60639,1,17209,''),(60640,1,17210,''),(60641,1,17211,''),(60642,1,17212,''),(60643,1,17213,''),(60644,1,17214,''),(60645,1,17215,''),(60646,1,17216,''),(60647,1,17217,''),(60648,1,17218,''),(60649,1,17219,''),(60650,1,17220,''),(60651,1,17221,''),(60652,1,17222,''),(60653,1,17223,''),(60654,1,17224,''),(60655,1,17225,''),(60656,1,17226,''),(60657,1,17227,''),(60658,1,17228,''),(60659,1,17229,''),(60660,1,17230,''),(60661,1,17231,''),(60662,1,17232,''),(60663,1,17233,''),(60664,1,17234,''),(60665,1,17235,''),(60666,1,17236,''),(60667,1,17237,''),(60668,1,17238,''),(60669,1,17239,''),(60670,1,17240,''),(60671,1,17241,''),(60672,1,17242,''),(60673,1,17243,''),(60674,1,17244,''),(60675,1,17245,''),(60676,1,17246,''),(60677,1,17247,''),(60678,1,17248,''),(60679,1,17249,''),(60680,1,17250,''),(60681,1,17251,''),(60682,1,17252,''),(60683,1,17253,''),(60684,1,17254,''),(60685,1,17255,''),(60686,1,17256,''),(60687,1,17257,''),(60688,1,17258,''),(60689,1,17259,''),(60690,1,17260,''),(60691,1,17261,''),(60692,1,17262,''),(60693,1,17263,''),(60694,1,17264,''),(60695,1,17265,''),(60696,1,17266,''),(60697,1,17267,''),(60698,1,17268,''),(60699,1,17269,''),(60700,1,17270,''),(60701,1,17271,''),(60702,1,17272,''),(60703,1,17273,''),(60704,1,17274,''),(60705,1,17275,''),(60706,1,17276,''),(60707,1,17277,''),(60708,1,17278,''),(60709,1,17279,''),(60710,1,17280,''),(60711,1,17281,''),(60712,1,17282,''),(60713,1,17283,''),(60714,1,17284,''),(60715,1,17285,''),(60716,1,17286,''),(60717,1,17287,''),(60718,1,17288,''),(60719,1,17289,''),(60720,1,17290,''),(60721,1,17291,''),(60722,1,17292,''),(60723,1,17293,''),(60724,1,17294,''),(60725,1,17295,''),(60726,1,17296,''),(60727,1,17297,''),(60728,1,17298,''),(60729,1,17299,''),(60730,1,17300,''),(60731,1,17301,''),(60732,1,17302,''),(60733,1,17303,''),(60734,1,17304,''),(60735,1,17305,''),(60736,1,17306,''),(60737,1,17307,''),(60738,1,17308,''),(60739,1,17309,''),(60740,1,17310,''),(60741,1,17311,''),(60742,1,17312,''),(60743,1,17313,''),(60744,1,17314,''),(60745,1,17315,''),(60746,1,17316,''),(60747,1,17317,''),(60748,1,17318,''),(60749,1,17319,''),(60750,1,17320,''),(60751,1,17321,''),(60752,1,17322,''),(60753,1,17323,''),(60754,1,17324,''),(60755,1,17325,''),(60756,1,17326,''),(60757,1,17327,''),(60758,1,17328,''),(60759,1,17329,''),(60760,1,17330,''),(60761,1,17331,''),(60762,1,17332,''),(60763,1,17333,''),(60764,1,17334,''),(60765,1,17335,''),(60766,1,17336,''),(60767,1,17337,''),(60768,1,17338,''),(60769,1,17339,''),(60770,1,17340,''),(60771,1,17341,''),(60772,1,17342,''),(60773,1,17343,''),(60774,1,17344,''),(60775,1,17345,''),(60776,1,17346,''),(60777,1,17347,''),(60778,1,17348,''),(60779,1,17349,''),(60780,1,17350,''),(60781,1,17351,''),(60782,1,17352,''),(60783,1,17353,''),(60784,1,17354,''),(60785,1,17355,''),(60786,1,17356,''),(60787,1,17357,''),(60788,1,17358,''),(60789,1,17359,''),(60790,1,17360,''),(60791,1,17361,''),(60792,1,17362,''),(60793,1,17363,''),(60794,1,17364,''),(60795,1,17365,''),(60796,1,17366,''),(60797,1,17367,''),(60798,1,17368,''),(60799,1,17369,''),(60800,1,17370,''),(60801,1,17371,''),(60802,1,17372,''),(60803,1,17373,''),(60804,1,17374,''),(60805,1,17375,''),(60806,1,17376,''),(60807,1,17377,''),(60808,1,17378,''),(60809,1,17379,''),(60810,1,17380,''),(60811,1,17381,''),(60812,1,17382,''),(60813,1,17383,''),(60814,1,17384,''),(60815,1,17385,''),(60816,1,17386,''),(60817,1,17387,''),(60818,1,17388,''),(60819,1,17389,''),(60820,1,17390,''),(60821,1,17391,''),(60822,1,17392,''),(60823,1,17393,''),(60824,1,17394,''),(60825,1,17395,''),(60826,1,17396,''),(60827,1,17397,''),(60828,1,17398,''),(60829,1,17399,''),(60830,1,17400,''),(60831,1,17401,''),(60832,1,17402,''),(60833,1,17403,''),(60834,1,17404,''),(60835,1,17405,''),(60836,1,17406,''),(60837,1,17407,''),(60838,1,17408,''),(60839,1,17409,''),(60840,1,17410,''),(60841,1,17411,''),(60842,1,17412,''),(60843,1,17413,''),(60844,1,17414,''),(60845,1,17415,''),(60846,1,17416,''),(60847,1,17417,''),(60848,1,17418,''),(60849,1,17419,''),(60850,1,17420,''),(60851,1,17421,''),(60852,1,17422,''),(60853,1,17423,''),(60854,1,17424,''),(60855,1,17425,''),(60856,1,17426,''),(60857,1,17427,''),(60858,1,17428,''),(60859,1,17429,''),(60860,1,17430,''),(60861,1,17431,''),(60862,1,17432,''),(60863,1,17433,''),(60864,1,17434,''),(60865,1,17435,''),(60866,1,17436,''),(60867,1,17437,''),(60868,1,17438,''),(60869,1,17439,''),(60870,1,17440,''),(60871,1,17441,''),(60872,1,17442,''),(60873,1,17443,''),(60874,1,17444,''),(60875,1,17445,''),(60876,1,17446,''),(60877,1,17447,''),(60878,1,17448,''),(60879,1,17449,''),(60880,1,17450,''),(60881,1,17451,''),(60882,1,17452,''),(60883,1,17453,''),(60884,1,17454,''),(60885,1,17455,''),(60886,1,17456,''),(60887,1,17457,''),(60888,1,17458,''),(60889,1,17459,''),(60890,1,17460,''),(60891,1,17461,''),(60892,1,17462,''),(60893,1,17463,''),(60894,1,17464,''),(60895,1,17465,''),(60896,1,17466,''),(60897,1,17467,''),(60898,1,17468,''),(60899,1,17469,''),(60900,1,17470,''),(60901,1,17471,''),(60902,1,17472,''),(60903,1,17473,'1'),(60904,1,17474,'2'),(60905,1,17475,'6'),(60906,1,17476,'3'),(60907,1,17477,'7'),(60908,1,17478,''),(60909,1,17479,''),(60910,1,17480,''),(60911,1,17481,''),(60912,1,17482,''),(60913,1,17483,''),(60914,1,17484,''),(60915,1,17485,''),(60916,1,17486,''),(60917,1,17487,''),(60918,1,17488,''),(60919,1,17489,''),(60920,1,17490,''),(60921,1,17491,''),(60922,1,17492,''),(60923,1,17493,''),(60924,1,17494,''),(60925,1,17495,''),(60926,1,17496,''),(60927,1,17497,''),(60928,1,17498,''),(60929,1,17499,''),(60930,1,17500,''),(60931,1,17501,''),(60932,1,17502,''),(60933,1,17503,''),(60934,1,17504,''),(60935,1,17505,''),(60936,1,17506,''),(60937,1,17507,''),(60938,1,17508,''),(60939,1,17509,''),(60940,1,17510,''),(60941,1,17511,''),(60942,1,17512,''),(60943,1,17513,''),(60944,1,17514,''),(60945,1,17515,''),(60946,1,17516,''),(60947,1,17517,''),(60948,1,17518,''),(60949,1,17519,''),(60950,1,17520,''),(60951,1,17521,''),(60952,1,17522,''),(60953,1,17523,''),(60954,1,17524,''),(60955,1,17525,''),(60956,1,17526,'asdasd'),(60957,1,17527,'asdasdasdasd11'),(60958,1,17528,'asdasd1'),(60959,1,17529,''),(60960,1,17530,''),(60961,1,17531,''),(60962,1,17532,''),(60963,1,17533,''),(60964,1,17534,''),(60965,1,17535,''),(60966,1,17536,''),(60967,1,17537,''),(60968,1,17538,''),(60969,1,17539,''),(60970,1,17540,''),(60971,1,17541,''),(60972,1,17542,''),(60973,1,17543,''),(60974,1,17544,''),(60975,1,17545,''),(60976,1,17546,''),(60977,1,17547,''),(60978,1,17548,''),(60979,1,17549,''),(60980,1,17550,''),(60981,1,17551,''),(60982,1,17552,''),(60983,1,17553,''),(60984,1,17554,''),(60985,1,17555,''),(60986,1,17556,''),(60987,1,17557,''),(60988,1,17558,''),(60989,1,17559,''),(60990,1,17560,''),(60991,1,17561,''),(60992,1,17562,''),(60993,1,17563,''),(60994,1,17564,''),(60995,1,17565,''),(60996,1,17566,''),(60997,1,17567,''),(60998,1,17568,''),(60999,1,17569,''),(61000,1,17570,''),(61001,1,17571,''),(61002,1,17572,''),(61003,1,17573,''),(61004,1,17574,''),(61005,1,17575,''),(61006,1,17576,''),(61007,1,17577,''),(61008,1,17578,''),(61009,1,17579,''),(61010,1,17580,''),(61011,1,17581,''),(61012,1,17582,''),(61013,1,17583,''),(61014,1,17584,''),(61015,1,17585,''),(61016,1,17586,''),(61017,1,17587,''),(61018,1,17588,''),(61019,1,17589,''),(61020,1,17590,''),(61021,1,17591,''),(61022,1,17592,''),(61023,1,17593,''),(61024,1,17594,''),(61025,1,17595,''),(61026,1,17596,''),(61027,1,17597,''),(61028,1,17598,''),(61029,1,17599,''),(61030,1,17600,''),(61031,1,17601,''),(61032,1,17602,''),(61033,1,17603,''),(61034,1,17604,''),(61035,1,17605,''),(61036,1,17606,''),(61037,1,17607,''),(61038,1,17608,''),(61039,1,17609,''),(61040,1,17610,''),(61041,1,17611,''),(61042,1,17612,''),(61043,1,17613,''),(61044,1,17614,''),(61045,1,17615,''),(61046,1,17616,''),(61047,1,17617,''),(61048,1,17618,''),(61049,1,17619,''),(61050,1,17620,''),(61051,1,17621,''),(61052,1,17622,''),(61053,1,17623,''),(61054,1,17624,''),(61055,1,17625,''),(61056,1,17626,''),(61057,1,17627,''),(61058,1,17628,''),(61059,1,17629,''),(61060,1,17630,''),(61061,1,17631,''),(61062,1,17632,''),(61063,1,17633,''),(61064,1,17634,''),(61065,1,17635,''),(61066,1,17636,''),(61067,1,17637,''),(61068,1,17638,''),(61069,1,17639,''),(61070,1,17640,''),(61071,1,17641,''),(61072,1,17642,''),(61073,1,17643,''),(61074,1,17644,''),(61075,1,17645,''),(61076,1,17646,''),(61077,1,17647,''),(61078,1,17648,''),(61079,1,17649,''),(61080,1,17650,''),(61081,1,17651,''),(61082,1,17652,''),(61083,1,17653,''),(61084,1,17654,''),(61085,1,17655,''),(61086,1,17656,''),(61087,1,17657,''),(61088,1,17658,''),(61089,1,17659,''),(61090,1,17660,''),(61091,1,17661,''),(61092,1,17662,''),(61093,1,17663,''),(61094,1,17664,''),(61095,1,17665,''),(61096,1,17666,''),(61097,1,17667,''),(61098,1,17668,''),(61099,1,17669,''),(61100,1,17670,''),(61101,1,17671,''),(61102,1,17672,''),(61103,1,17673,''),(61104,1,17674,''),(61105,1,17675,''),(61106,1,17676,''),(61107,1,17677,''),(61108,1,17678,''),(61109,1,17679,''),(61110,1,17680,''),(61111,1,17681,''),(61112,1,17682,''),(61113,1,17683,''),(61114,1,17684,''),(61115,1,17685,''),(61116,1,17686,''),(61117,1,17687,''),(61118,1,17688,''),(61119,1,17689,''),(61120,1,17690,''),(61121,1,17691,''),(61122,1,17692,''),(61123,1,17693,''),(61124,1,17694,''),(61125,1,17695,''),(61126,1,17696,''),(61127,1,17697,''),(61128,1,17698,''),(61129,1,17699,''),(61130,1,17700,''),(61131,1,17701,''),(61132,1,17702,''),(61133,1,17703,''),(61134,1,17704,''),(61135,1,17705,''),(61136,1,17706,''),(61137,1,17707,''),(61138,1,17708,''),(61139,1,17709,''),(61140,1,17710,''),(61141,1,17711,''),(61142,1,17712,''),(61143,1,17713,''),(61144,1,17714,''),(61145,1,17715,''),(61146,1,17716,''),(61147,1,17717,''),(61148,1,17718,''),(61149,1,17719,''),(61150,1,17720,''),(61151,1,17721,''),(61152,1,17722,''),(61153,1,17723,''),(61154,1,17724,''),(61155,1,17725,''),(61156,1,17726,''),(61157,1,17727,''),(61158,1,17728,''),(61159,1,17729,''),(61160,1,17730,''),(61161,1,17731,''),(61162,1,17732,''),(61163,1,17733,''),(61164,1,17734,''),(61165,1,17735,''),(61166,1,17736,''),(61167,1,17737,''),(61168,1,17738,''),(61169,1,17739,''),(61170,1,17740,''),(61171,1,17741,''),(61172,1,17742,''),(61173,1,17743,''),(61174,1,17744,''),(61175,1,17745,''),(61176,1,17746,''),(61177,1,17747,''),(61178,1,17748,''),(61179,1,17749,''),(61180,1,17750,''),(61181,1,17751,''),(61182,1,17752,''),(61183,1,17753,''),(61184,1,17754,''),(61185,1,17755,''),(61186,1,17756,''),(61187,1,17757,''),(61188,1,17758,''),(61189,1,17759,''),(61190,1,17760,''),(61191,1,17761,''),(61192,1,17762,''),(61193,1,17763,''),(61194,1,17764,''),(61195,1,17765,''),(61196,1,17766,''),(61197,1,17767,''),(61198,1,17768,''),(61199,1,17769,''),(61200,1,17770,''),(61201,1,17771,''),(61202,1,17772,''),(61203,1,17773,''),(61204,1,17774,''),(61205,1,17775,''),(61206,1,17776,''),(61207,1,17777,''),(61208,1,17778,''),(61209,1,17779,''),(61210,1,17780,''),(61211,1,17781,''),(61212,1,17782,''),(61213,1,17783,''),(61214,1,17784,''),(61215,1,17785,''),(61216,1,17786,''),(61217,1,17787,''),(61218,1,17788,''),(61219,1,17789,''),(61220,1,17790,''),(61221,1,17791,''),(61222,1,17792,''),(61223,1,17793,''),(61224,1,17794,''),(61225,1,17795,''),(61226,1,17796,''),(61227,1,17797,''),(61228,1,17798,''),(61229,1,17799,''),(61230,1,17800,''),(61231,1,17801,''),(61232,1,17802,''),(61233,1,17803,''),(61234,1,17804,''),(61235,1,17805,''),(61236,1,17806,''),(61237,1,17807,''),(61238,1,17808,''),(61239,1,17809,''),(61240,1,17810,''),(61241,1,17811,''),(61242,1,17812,''),(61243,1,17813,''),(61244,1,17814,''),(61245,1,17815,''),(61246,1,17816,''),(61247,1,17817,''),(61248,1,17818,''),(61249,1,17819,''),(61250,1,17820,''),(61251,1,17821,''),(61252,1,17822,''),(61253,1,17823,''),(61254,1,17824,''),(61255,1,17825,''),(61256,1,17826,''),(61257,1,17827,''),(61258,1,17828,''),(61259,1,17829,''),(61260,1,17830,''),(61261,1,17831,''),(61262,1,17832,''),(61263,1,17833,''),(61264,1,17834,''),(61265,1,17835,''),(61266,1,17836,''),(61267,1,17837,''),(61268,1,17838,''),(61269,1,17839,''),(61270,1,17840,''),(61271,1,17841,''),(61272,1,17842,''),(61273,1,17843,''),(61274,1,17844,''),(61275,1,17845,''),(61276,1,17846,''),(61277,1,17847,''),(61278,1,17848,''),(61279,1,17849,''),(61280,1,17850,''),(61281,1,17851,''),(61282,1,17852,''),(61283,1,17853,''),(61284,1,17854,''),(61285,1,17855,''),(61286,1,17856,''),(61287,1,17857,''),(61288,1,17858,''),(61289,1,17859,''),(61290,1,17860,''),(61291,1,17861,''),(61292,1,17862,''),(61293,1,17863,''),(61294,1,17864,''),(61295,1,17865,''),(61296,1,17866,''),(61297,1,17867,''),(61298,1,17868,''),(61299,1,17869,''),(61300,1,17870,''),(61301,1,17871,''),(61302,1,17872,''),(61303,1,17873,''),(61304,1,17874,''),(61305,1,17875,''),(61306,1,17876,''),(61307,1,17877,''),(61308,1,17878,''),(61309,1,17879,''),(61310,1,17880,''),(61311,1,17881,''),(61312,1,17882,''),(61313,1,17883,''),(61314,1,17884,''),(61315,1,17885,''),(61316,1,17886,''),(61317,1,17887,''),(61318,1,17888,''),(61319,1,17889,''),(61320,1,17890,''),(61321,1,17891,''),(61322,1,17892,''),(61323,1,17893,''),(61324,1,17894,''),(61325,1,17895,''),(61326,1,17896,''),(61327,1,17897,''),(61328,1,17898,''),(61329,1,17899,''),(61330,1,17900,''),(61331,1,17901,''),(61332,1,17902,''),(61333,1,17903,''),(61334,1,17904,''),(61335,1,17905,''),(61336,1,17906,''),(61337,1,17907,''),(61338,1,17908,''),(61339,1,17909,''),(61340,1,17910,''),(61341,1,17911,''),(61342,1,17912,''),(61343,1,17913,''),(61344,1,17914,''),(61345,1,17915,''),(61346,1,17916,''),(61347,1,17917,''),(61348,1,17918,''),(61349,1,17919,''),(61350,1,17920,''),(61351,1,17921,''),(61352,1,17922,''),(61353,1,17923,''),(61354,1,17924,''),(61355,1,17925,''),(61356,1,17926,''),(61357,1,17927,''),(61358,1,17928,''),(61359,1,17929,''),(61360,1,17930,''),(61361,1,17931,''),(61362,1,17932,''),(61363,1,17933,''),(61364,1,17934,''),(61365,1,17935,''),(61366,1,17936,''),(61367,1,17937,''),(61368,1,17938,''),(61369,1,17939,''),(61370,1,17940,''),(61371,1,17941,''),(61372,1,17942,''),(61373,1,17943,''),(61374,1,17944,''),(61375,1,17945,''),(61376,1,17946,''),(61377,1,17947,''),(61378,1,17948,''),(61379,1,17949,''),(61380,1,17950,''),(61381,1,17951,'444'),(61382,1,17952,'6'),(61383,1,17953,'4'),(61384,1,17954,''),(61385,1,17955,''),(61386,1,17956,''),(61387,1,17957,''),(61388,1,17958,''),(61389,1,17959,''),(61390,1,17960,''),(61391,1,17961,''),(61392,1,17962,''),(61393,1,17963,''),(61394,1,17964,''),(61395,1,17965,''),(61396,1,17966,''),(61397,1,17967,''),(61398,1,17968,''),(61399,1,17969,''),(61400,1,17970,''),(61401,1,17971,''),(61402,1,17972,''),(61403,1,17973,''),(61404,1,17974,''),(61405,1,17975,''),(61406,1,17976,''),(61407,1,17977,''),(61408,1,17978,''),(61409,1,17979,''),(61410,1,17980,''),(61411,1,17981,''),(61412,1,17982,''),(61413,1,17983,''),(61414,1,17984,''),(61415,1,17985,''),(61416,1,17986,''),(61417,1,17987,''),(61418,1,17988,''),(61419,1,17989,''),(61420,1,17990,''),(61421,1,17991,''),(61422,1,17992,''),(61423,1,17993,''),(61424,1,17994,''),(61425,1,17995,''),(61426,1,17996,''),(61427,1,17997,''),(61428,1,17998,''),(61429,1,17999,''),(61430,1,18000,''),(61431,1,18001,''),(61432,1,18002,''),(61433,1,18003,''),(61434,1,18004,''),(61435,1,18005,''),(61436,1,18006,''),(61437,1,18007,''),(61438,1,18008,''),(61439,1,18009,''),(61440,1,18010,''),(61441,1,18011,''),(61442,1,18012,''),(61443,1,18013,''),(61444,1,18014,''),(61445,1,18015,''),(61446,1,18016,''),(61447,1,18017,''),(61448,1,18018,''),(61449,1,18019,''),(61450,1,18020,''),(61451,1,18021,''),(61452,1,18022,''),(61453,1,18023,''),(61454,1,18024,''),(61455,1,18025,'aa'),(61456,1,18026,'ee'),(61457,1,18027,'dd'),(61458,1,18028,'ff'),(61459,1,18029,'gg'),(61460,1,18030,''),(61461,1,18031,''),(61462,1,18032,''),(61463,1,18033,''),(61464,1,18034,''),(61465,1,18035,''),(61466,1,18036,''),(61467,1,18037,''),(61468,1,18038,''),(61469,1,18039,''),(61470,1,18040,''),(61471,1,18041,''),(61472,1,18042,''),(61473,1,18043,'asdsad'),(61474,1,18044,'asdasd'),(61475,1,18045,''),(61476,1,18046,''),(61477,1,18047,''),(61478,1,18048,''),(61479,1,18049,''),(61480,1,18050,''),(61481,1,18051,''),(61482,1,18052,''),(61483,1,18053,''),(61484,1,18054,''),(61485,1,18055,''),(61486,1,18056,''),(61487,1,18057,''),(61488,1,18058,''),(61489,1,18059,''),(61490,1,18060,''),(61491,1,18061,''),(61492,1,18062,''),(61493,1,18063,''),(61494,1,18064,''),(61495,1,18065,''),(61496,1,18066,''),(61497,1,18067,''),(61498,1,18068,''),(61499,1,18069,''),(61500,1,18070,''),(61501,1,18071,''),(61502,1,18072,''),(61503,1,18073,''),(61504,1,18074,''),(61505,1,18075,''),(61506,1,18076,''),(61507,1,18077,''),(61508,1,18078,''),(61509,1,18079,''),(61510,1,18080,''),(61511,1,18081,''),(61512,1,18082,''),(61513,1,18083,''),(61514,1,18084,''),(61515,1,18085,''),(61516,1,18086,''),(61517,1,18087,''),(61518,1,18088,''),(61519,1,18089,''),(61520,1,18090,''),(61521,1,18091,''),(61522,1,18092,''),(61523,1,18093,''),(61524,1,18094,''),(61525,1,18095,''),(61526,1,18096,''),(61527,1,18097,''),(61528,1,18098,''),(61529,1,18099,''),(61530,1,18100,''),(61531,1,18101,''),(61532,1,18102,''),(61533,1,18103,''),(61534,1,18104,''),(61535,1,18105,''),(61536,1,18106,''),(61537,1,18107,''),(61538,1,18108,''),(61539,1,18109,''),(61540,1,18110,''),(61541,1,18111,''),(61542,1,18112,''),(61543,1,18113,''),(61544,1,18114,''),(61545,1,18115,''),(61546,1,18116,''),(61547,1,18117,''),(61548,1,18118,''),(61549,1,18119,''),(61550,1,18120,''),(61551,1,18121,''),(61552,1,18122,''),(61553,1,18123,''),(61554,1,18124,''),(61555,1,18125,''),(61556,1,18126,''),(61557,1,18127,''),(61558,1,18128,''),(61559,1,18129,''),(61560,1,18130,''),(61561,1,18131,''),(61562,1,18132,''),(61563,1,18133,''),(61564,1,18134,''),(61565,1,18135,''),(61566,1,18136,''),(61567,1,18137,''),(61568,1,18138,''),(61569,1,18139,''),(61570,1,18140,''),(61571,1,18141,''),(61572,1,18142,''),(61573,1,18143,''),(61574,1,18144,''),(61575,1,18145,''),(61576,1,18146,''),(61577,1,18147,''),(61578,1,18148,''),(61579,1,18149,''),(61580,1,18150,''),(61581,1,18151,''),(61582,1,18152,''),(61583,1,18153,''),(61584,1,18154,''),(61585,1,18155,''),(61586,1,18156,''),(61587,1,18157,''),(61588,1,18158,''),(61589,1,18159,''),(61590,1,18160,''),(61591,1,18161,''),(61592,1,18162,''),(61593,1,18163,''),(61594,1,18164,''),(61595,1,18165,''),(61596,1,18166,''),(61597,1,18167,''),(61598,1,18168,''),(61599,1,18169,''),(61600,1,18170,''),(61601,1,18171,''),(61602,1,18172,''),(61603,1,18173,''),(61604,1,18174,''),(61605,1,18175,''),(61606,1,18176,''),(61607,1,18177,''),(61608,1,18178,''),(61609,1,18179,'q'),(61610,1,18180,'i'),(61611,1,18181,'t'),(61612,1,18182,'w'),(61613,1,18183,'o'),(61614,1,18184,'y'),(61615,1,18185,'e'),(61616,1,18186,'p'),(61617,1,18187,'u'),(61618,1,18188,'r'),(61619,1,18189,''),(61620,1,18190,''),(61621,1,18191,''),(61622,1,18192,''),(61623,1,18193,''),(61624,1,18194,''),(61625,1,18195,''),(61626,1,18196,''),(61627,1,18197,''),(61628,1,18198,''),(61629,1,18199,''),(61630,1,18200,''),(61631,1,18201,''),(61632,1,18202,''),(61633,1,18203,''),(61634,1,18204,''),(61635,1,18205,''),(61636,1,18206,''),(61637,1,18207,''),(61638,1,18208,''),(61639,1,18209,''),(61640,1,18210,''),(61641,1,18211,''),(61642,1,18212,''),(61643,1,18213,''),(61644,1,18214,''),(61645,1,18215,''),(61646,1,18216,''),(61647,1,18217,''),(61648,1,18218,''),(61649,1,18219,''),(61650,1,18220,''),(61651,1,18221,''),(61652,1,18222,''),(61653,1,18223,''),(61654,1,18224,''),(61655,1,18225,''),(61656,1,18226,''),(61657,1,18227,''),(61658,1,18228,''),(61659,1,18229,''),(61660,1,18230,''),(61661,1,18231,''),(61662,1,18232,''),(61663,1,18233,''),(61664,1,18234,''),(61665,1,18235,''),(61666,1,18236,''),(61667,1,18237,''),(61668,1,18238,''),(61669,1,18239,''),(61670,1,18240,''),(61671,1,18241,''),(61672,1,18242,''),(61673,1,18243,''),(61674,1,18244,''),(61675,1,18245,''),(61676,1,18246,''),(61677,1,18247,''),(61678,1,18248,''),(61679,1,18249,''),(61680,1,18250,''),(61681,1,18251,''),(61682,1,18252,''),(61683,1,18253,''),(61684,1,18254,''),(61685,1,18255,''),(61686,1,18256,''),(61687,1,18257,''),(61688,1,18258,''),(61689,1,18259,''),(61690,1,18260,''),(61691,1,18261,''),(61692,1,18262,''),(61693,1,18263,''),(61694,1,18264,''),(61695,1,18265,''),(61696,1,18266,''),(61697,1,18267,''),(61698,1,18268,''),(61699,1,18269,''),(61700,1,18270,''),(61701,1,18271,''),(61702,1,18272,''),(61703,1,18273,''),(61704,1,18274,''),(61705,1,18275,''),(61706,1,18276,''),(61707,1,18277,''),(61708,1,18278,''),(61709,1,18279,''),(61710,1,18280,''),(61711,1,18281,''),(61712,1,18282,''),(61713,1,18283,''),(61714,1,18284,''),(61715,1,18285,''),(61716,1,18286,''),(61717,1,18287,''),(61718,1,18288,''),(61719,1,18289,''),(61720,1,18290,''),(61721,1,18291,''),(61722,1,18292,''),(61723,1,18293,''),(61724,1,18294,''),(61725,1,18295,''),(61726,1,18296,''),(61727,1,18297,''),(61728,1,18298,''),(61729,1,18299,''),(61730,1,18300,''),(61731,1,18301,''),(61732,1,18302,''),(61733,1,18303,''),(61734,1,18304,''),(61735,1,18305,''),(61736,1,18306,''),(61737,1,18307,''),(61738,1,18308,''),(61739,1,18309,''),(61740,1,18310,''),(61741,1,18311,''),(61742,1,18312,''),(61743,1,18313,''),(61744,1,18314,''),(61745,1,18315,''),(61746,1,18316,''),(61747,1,18317,''),(61748,1,18318,''),(61749,1,18319,''),(61750,1,18320,''),(61751,1,18321,''),(61752,1,18322,''),(61753,1,18323,''),(61754,1,18324,''),(61755,1,18325,''),(61756,1,18326,''),(61757,1,18327,''),(61758,1,18328,''),(61759,1,18329,'test'),(61760,1,18330,'test'),(61761,1,18331,''),(61762,1,18332,''),(61763,1,18333,''),(61764,1,18334,''),(61765,1,18335,''),(61766,1,18336,''),(61767,1,18337,''),(61768,1,18338,''),(61769,1,18339,''),(61770,1,18340,''),(61771,1,18341,''),(61772,1,18342,''),(61773,1,18343,''),(61774,1,18344,''),(61775,1,18345,''),(61776,1,18346,''),(61777,1,18347,''),(61778,1,18348,''),(61779,1,18349,''),(61780,1,18350,''),(61781,1,18351,''),(61782,1,18352,''),(61783,1,18353,''),(61784,1,18354,''),(61785,1,18355,''),(61786,1,18356,''),(61787,1,18357,''),(61788,1,18358,''),(61789,1,18359,''),(61790,1,18360,''),(61791,1,18361,''),(61792,1,18362,''),(61793,1,18363,''),(61794,1,18364,''),(61795,1,18365,''),(61796,1,18366,''),(61797,1,18367,''),(61798,1,18368,''),(61799,1,18369,''),(61800,1,18370,''),(61801,1,18371,''),(61802,1,18372,''),(61803,1,18373,''),(61804,1,18374,''),(61805,1,18375,''),(61806,1,18376,''),(61807,1,18377,''),(61808,1,18378,''),(61809,1,18379,''),(61810,1,18380,''),(61811,1,18381,''),(61812,1,18382,''),(61813,1,18383,''),(61814,1,18384,''),(61815,1,18385,''),(61816,1,18386,''),(61817,1,18387,''),(61818,1,18388,''),(61819,1,18389,''),(61820,1,18390,''),(61821,1,18391,''),(61822,1,18392,''),(61823,1,18393,''),(61824,1,18394,''),(61825,1,18395,''),(61826,1,18396,''),(61827,1,18397,''),(61828,1,18398,''),(61829,1,18399,''),(61830,1,18400,''),(61831,1,18401,''),(61832,1,18402,''),(61833,1,18403,''),(61834,1,18404,''),(61835,1,18405,''),(61836,1,18406,''),(61837,1,18407,''),(61838,1,18408,''),(61839,1,18409,''),(61840,1,18410,''),(61841,1,18411,''),(61842,1,18412,''),(61843,1,18413,''),(61844,1,18414,''),(61845,1,18415,''),(61846,1,18416,''),(61847,1,18417,''),(61848,1,18418,''),(61849,1,18419,''),(61850,1,18420,''),(61851,1,18421,''),(61852,1,18422,''),(61853,1,18423,''),(61854,1,18424,''),(61855,1,18425,''),(61856,1,18426,''),(61857,1,18427,''),(61858,1,18428,''),(61859,1,18429,'5'),(61860,1,18430,'4'),(61861,1,18431,''),(61862,1,18432,''),(61863,1,18433,''),(61864,1,18434,''),(61865,1,18435,''),(61866,1,18436,''),(61867,1,18437,''),(61868,1,18438,''),(61869,1,18439,''),(61870,1,18440,''),(61871,1,18441,'aaa'),(61872,1,18442,''),(61873,1,18443,''),(61874,1,18444,''),(61875,1,18445,''),(61876,1,18446,''),(61877,1,18447,''),(61878,1,18448,''),(61879,1,18449,''),(61880,1,18450,''),(61881,1,18451,''),(61882,1,18452,''),(61883,1,18453,''),(61884,1,18454,''),(61885,1,18455,''),(61886,1,18456,''),(61887,1,18457,''),(61888,1,18458,''),(61889,1,18459,'Complete requiered fields'),(61896,1,18466,'Possible Values'),(61897,1,18467,'Back to Profile'),(61898,1,18468,'Correct the form'),(61899,1,18469,'Add new values'),(61900,1,18470,'&nbsp;'),(61901,1,18471,'Photo is still uploading'),(61904,1,18474,'Local'),(61905,1,18475,'Url'),(61906,1,18476,'<div class=\"base_welcome_h\">We changed the old Dating sites concept</div><div class=\"base_welcome_sub\">See how {$site_name} make Dating more social</div>'),(61907,1,18477,'HTML SUPPORTED'),(61908,1,18478,'You can\'t delete this account type. At least 1 account type must be on the site.'),(61909,1,18479,'Adminboard style'),(61910,1,18480,'See more'),(61911,1,18481,'aaaaaaaaaaaaaa'),(61912,1,18482,'asdasd1111111111111111'),(61913,1,18483,'1'),(61914,1,18484,'11'),(61915,1,18485,'21'),(61916,1,18486,'8'),(61917,1,18487,'18'),(61918,1,18488,'28'),(61919,1,18489,'5'),(61920,1,18490,'15'),(61921,1,18491,'25'),(61922,1,18492,'2'),(61923,1,18493,'12'),(61924,1,18494,'22'),(61925,1,18495,'9'),(61926,1,18496,'19'),(61927,1,18497,'6'),(61928,1,18498,'16'),(61929,1,18499,'26'),(61930,1,18500,'3'),(61931,1,18501,'13'),(61932,1,18502,'23'),(61933,1,18503,'10'),(61934,1,18504,'20'),(61935,1,18505,'7'),(61936,1,18506,'17'),(61937,1,18507,'27'),(61938,1,18508,'4'),(61939,1,18509,'14'),(61940,1,18510,'24'),(61941,1,18511,'aaaaaaaaa'),(61942,1,18512,'asdsadasd'),(61943,1,18513,'1'),(61944,1,18514,'11'),(61945,1,18515,'21'),(61946,1,18516,'31'),(61947,1,18517,'8'),(61948,1,18518,'18'),(61949,1,18519,'28'),(61950,1,18520,'5'),(61951,1,18521,'15'),(61952,1,18522,'25'),(61953,1,18523,'2'),(61954,1,18524,'12'),(61955,1,18525,'22'),(61956,1,18526,'9'),(61957,1,18527,'19'),(61958,1,18528,'29'),(61959,1,18529,'6'),(61960,1,18530,'16'),(61961,1,18531,'26'),(61962,1,18532,'3'),(61963,1,18533,'13'),(61964,1,18534,'23'),(61965,1,18535,'10'),(61966,1,18536,'20'),(61967,1,18537,'30'),(61968,1,18538,'7'),(61969,1,18539,'17'),(61970,1,18540,'27'),(61971,1,18541,'4'),(61972,1,18542,'14'),(61973,1,18543,'24'),(61974,1,18544,'bbbbb bbbbb'),(61975,1,18545,'bbbbb'),(61976,1,18546,'1'),(61977,1,18547,'11'),(61978,1,18548,'21'),(61979,1,18549,'8'),(61980,1,18550,'18'),(61981,1,18551,'28'),(61982,1,18552,'5'),(61983,1,18553,'15'),(61984,1,18554,'25'),(61985,1,18555,'2'),(61986,1,18556,'12'),(61987,1,18557,'22'),(61988,1,18558,'9'),(61989,1,18559,'19'),(61990,1,18560,'29'),(61991,1,18561,'6'),(61992,1,18562,'16'),(61993,1,18563,'26'),(61994,1,18564,'3'),(61995,1,18565,'13'),(61996,1,18566,'23'),(61997,1,18567,'10'),(61998,1,18568,'20'),(61999,1,18569,'7'),(62000,1,18570,'17'),(62001,1,18571,'27'),(62002,1,18572,'4'),(62003,1,18573,'14'),(62004,1,18574,'24'),(62019,1,18589,'Back'),(62020,1,18590,'Check all'),(62021,1,18591,'commented on {$content}'),(62022,1,18592,'comment on {$content}'),(62023,1,18593,'Profile Pics'),(62024,1,18594,'Profile pic'),(62025,1,18595,'Comments'),(62026,1,18596,'Comment'),(62027,1,18597,'People'),(62028,1,18598,'Profile'),(62029,1,18599,'pic cropping failed.'),(62030,1,18600,'Drop image here or click to browse'),(62031,1,18601,'Drop image here'),(62032,1,18602,'Reported In: {$time}'),(62033,1,18603,'Maximum upload avatar size'),(62034,1,18604,'Action'),(62035,1,18605,'Are you sure you want to delete this {$content}?'),(62036,1,18606,'Are you sure you want to delete {$count} {$content}?'),(62037,1,18607,'{$content} has been deleted'),(62038,1,18608,'{$count} {$content} have been deleted'),(62039,1,18609,'{$content} has been unreported'),(62040,1,18610,'{$count} {$content} have been unreported'),(62041,1,18611,'<a href=\"{$userUrl}\"><b>{$userName}\'s</b></a> {$content} has been reported'),(62042,1,18612,'Nothing to show'),(62043,1,18613,'No items selected'),(62044,1,18614,'Admin tools'),(62045,1,18615,'Reason'),(62046,1,18616,'Reporter'),(62047,1,18617,'Reports management'),(62048,1,18618,'Pending Approval'),(62049,1,18619,'Suspending Reason.'),(62091,1,18661,'Suspend {$displayName}'),(62051,1,18621,'Dear {$realName},<br><br>\r\nSorry to telling you that your account on {$site_name} has been suspended because:<br>\r\n{$suspendReason}<br><br>\r\nThank you,<br>\r\n{$site_name} team'),(62052,1,18622,'Sorry your account has been suspended because {$suspendReason}'),(62053,1,18623,'Dear {$realName},\r\nSorry to telling you that your account on {$site_name} has been suspended because:\r\n{$suspendReason}\r\nThank you,\r\n{$site_name} team'),(62054,1,18624,'Unreport'),(62055,1,18625,'Welcome to <a href=\"{$site_url}\">{$site_name}</a>! Thanks for registering your account. Now you can use our site'),(62056,1,18626,'Welcome to {$site_name}! Thanks for registering your account. Now you can use our site'),(62061,1,18631,''),(62062,1,18632,''),(62063,1,18633,''),(62064,1,18634,''),(62065,1,18635,''),(62066,1,18636,''),(62067,1,18637,''),(62068,1,18638,''),(62069,1,18639,''),(62070,1,18640,''),(62071,1,18641,''),(62072,1,18642,''),(62073,1,18643,''),(62074,1,18644,''),(62075,1,18645,''),(62076,1,18646,''),(62077,1,18647,''),(62078,1,18648,''),(62079,1,18649,''),(62080,1,18650,''),(62081,1,18651,''),(62082,1,18652,''),(62083,1,18653,''),(62084,1,18654,'Change password'),(62099,1,18630,'Password here'),(62085,1,18655,''),(62086,1,18656,''),(62087,1,18657,''),(62088,1,18658,''),(62089,1,18659,'New to {$site_name}?'),(62092,1,18662,'Grey'),(62093,1,18663,'Login to report thingst'),(62094,1,18664,'This is not a video or video not found'),(62095,1,18665,'Invalid file type. Acceptable file types: JPG/PNG/GIF'),(62096,1,18666,'Add video link...'),(62097,1,18667,'SMTP connection failed.'),(63000,1,19746,'Add unlimited number of values.'),(63001,1,19747,'Possible values'),(63002,1,19748,'The value should not be empty'),(63003,1,19941,'Dropdown Menu (Unlimited Values)');
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_log`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_log`;
CREATE TABLE `%%TBL-PREFIX%%base_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `type` varchar(100) NOT NULL,
  `key` varchar(100) DEFAULT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_log`
--

LOCK TABLES `%%TBL-PREFIX%%base_log` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_login_cookie`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_login_cookie`;
CREATE TABLE `%%TBL-PREFIX%%base_login_cookie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `cookie` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `cookie` (`cookie`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_login_cookie`
--

LOCK TABLES `%%TBL-PREFIX%%base_login_cookie` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_mail`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_mail`;
CREATE TABLE `%%TBL-PREFIX%%base_mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipientEmail` varchar(100) NOT NULL,
  `senderEmail` varchar(100) NOT NULL,
  `senderName` varchar(100) NOT NULL,
  `subject` text NOT NULL,
  `textContent` text NOT NULL,
  `htmlContent` text,
  `sentTime` int(11) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '1',
  `senderSuffix` int(11) NOT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';

--
-- Records of `%%TBL-PREFIX%%base_mail`
--

LOCK TABLES `%%TBL-PREFIX%%base_mail` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_mass_mailing_ignore_user`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_mass_mailing_ignore_user`;
CREATE TABLE `%%TBL-PREFIX%%base_mass_mailing_ignore_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_mass_mailing_ignore_user`
--

LOCK TABLES `%%TBL-PREFIX%%base_mass_mailing_ignore_user` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_media_panel_file`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_media_panel_file`;
CREATE TABLE `%%TBL-PREFIX%%base_media_panel_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin` varchar(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  `userId` int(11) NOT NULL,
  `data` text NOT NULL,
  `stamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_media_panel_file`
--

LOCK TABLES `%%TBL-PREFIX%%base_media_panel_file` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_menu_item`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_menu_item`;
CREATE TABLE `%%TBL-PREFIX%%base_menu_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(110) NOT NULL DEFAULT '',
  `key` varchar(150) NOT NULL DEFAULT '',
  `documentKey` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(70) NOT NULL DEFAULT '',
  `order` int(11) DEFAULT NULL,
  `routePath` varchar(255) DEFAULT NULL,
  `externalUrl` varchar(255) DEFAULT NULL,
  `newWindow` tinyint(1) DEFAULT '0',
  `visibleFor` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`,`prefix`),
  KEY `documentKey` (`documentKey`)
) ENGINE=MyISAM AUTO_INCREMENT=482 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_menu_item`
--

LOCK TABLES `%%TBL-PREFIX%%base_menu_item` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_menu_item` VALUES (23,'base','main_menu_index','','main',2,'base_index',NULL,0,1),(468,'base','page_81959573','page_81959573','bottom',2,NULL,NULL,NULL,3),(24,'base','main_menu_my_profile','','hidden',1,'base_member_profile',NULL,0,2),(100,'admin','sidebar_menu_item_user_dashboard','','admin_pages',2,'admin_pages_user_dashboard',NULL,NULL,3),(41,'base','users_main_menu_item','','main',4,'users',NULL,NULL,3),(298,'admin','sidebar_menu_item_permission','','admin_privacy',1,'admin_permissions',NULL,0,2),(170,'admin','sidebar_menu_item_plugin_mass_mailing','','admin_users',4,'admin_massmailing',NULL,NULL,3),(101,'admin','sidebar_menu_item_user_profile','','admin_pages',3,'admin_pages_user_profile',NULL,NULL,3),(97,'admin','sidebar_menu_dashboard','','admin',1,'admin_default',NULL,NULL,3),(58,'admin','sidebar_menu_item_theme_edit','','admin_appearance',0,'admin_themes_edit',NULL,NULL,3),(73,'admin','sidebar_menu_item_theme_choose','','admin_appearance',1,'admin_themes_choose',NULL,NULL,3),(74,'admin','sidebar_menu_item_pages_manage','','admin_pages',1,'admin_pages_main',NULL,NULL,3),(96,'admin','sidebar_menu_item_settings_language','','admin_settings',3,'admin_settings_language',NULL,NULL,3),(107,'admin','sidebar_menu_item_users','','admin_users',1,'admin_users_browse',NULL,NULL,3),(109,'admin','sidebar_menu_item_main_settings','','admin_settings',1,'admin_settings_main',NULL,NULL,3),(115,'admin','sidebar_menu_item_questions','','admin_users',3,'questions_index',NULL,0,3),(120,'admin','sidebar_menu_item_dev_langs','','admin_dev',1,'admin_developer_tools_language',NULL,NULL,3),(308,'admin','sidebar_menu_plugins_add','','admin_plugins',3,'admin_plugins_add',NULL,0,2),(268,'admin','sidebar_menu_item_maintenance','','admin_pages',7,'admin_pages_maintenance',NULL,0,2),(246,'admin','sidebar_menu_item_splash_screen','','admin_pages',5,'admin_pages_splash_screen',NULL,NULL,2),(307,'admin','sidebar_menu_plugins_available','','admin_plugins',2,'admin_plugins_available',NULL,NULL,2),(299,'admin','sidebar_menu_item_permission_role','','admin_privacy',2,'admin_permissions_roles',NULL,0,2),(300,'admin','sidebar_menu_item_permission_moders','','admin_privacy',3,'admin_permissions_moderators',NULL,0,2),(304,'base','dashboard','','main',1,'base_member_dashboard',NULL,NULL,2),(306,'admin','sidebar_menu_plugins_installed','','admin_plugins',1,'admin_plugins_installed',NULL,NULL,2),(340,'admin','sidebar_menu_item_dashboard_finance','','admin',2,'admin_finance',NULL,0,3),(341,'admin','sidebar_menu_item_restricted_usernames','','admin_users',5,'admin_restrictedusernames',NULL,0,3),(415,'admin','sidebar_menu_item_user_settings','','admin_settings',2,'admin_settings_user',NULL,0,3),(405,'admin','sidebar_menu_item_users_roles','','admin_users',2,'admin_user_roles',NULL,0,3),(411,'base','page-119658','page-119658','bottom',1,NULL,NULL,NULL,3),(467,'admin','sidebar_menu_themes_add','','admin_appearance',3,'admin_themes_add_new',NULL,0,3);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_place`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_place`;
CREATE TABLE `%%TBL-PREFIX%%base_place` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `editableByUser` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_place`
--

LOCK TABLES `%%TBL-PREFIX%%base_place` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_place` VALUES (1,'dashboard',0),(2,'index',0),(3,'profile',0);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_place_entity_scheme`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_place_entity_scheme`;
CREATE TABLE `%%TBL-PREFIX%%base_place_entity_scheme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `placeId` int(11) DEFAULT NULL,
  `schemeId` int(11) DEFAULT NULL,
  `entityId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`entityId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_place_entity_scheme`
--

LOCK TABLES `%%TBL-PREFIX%%base_place_entity_scheme` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_place_scheme`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_place_scheme`;
CREATE TABLE `%%TBL-PREFIX%%base_place_scheme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `placeId` int(11) DEFAULT NULL,
  `schemeId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_place_scheme`
--

LOCK TABLES `%%TBL-PREFIX%%base_place_scheme` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_place_scheme` VALUES (1,1,5),(2,2,2),(3,3,1),(4,4,1);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_plugin`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_plugin`;
CREATE TABLE `%%TBL-PREFIX%%base_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `module` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `developerKey` varchar(255) DEFAULT NULL,
  `isSystem` tinyint(1) NOT NULL,
  `isActive` tinyint(1) NOT NULL,
  `adminSettingsRoute` varchar(255) DEFAULT NULL,
  `uninstallRoute` varchar(255) DEFAULT NULL,
  `build` int(11) NOT NULL DEFAULT '0',
  `update` tinyint(1) NOT NULL DEFAULT '0',
  `licenseKey` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  UNIQUE KEY `module` (`module`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_plugin`
--

LOCK TABLES `%%TBL-PREFIX%%base_plugin` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_plugin` VALUES (1,'Base PEEP plugin','Description','base','base','',1,1,NULL,NULL,1,0,NULL),(3,'Admin Panel','Admin Panel','admin','admin','',1,1,NULL,NULL,1,0,NULL);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_preference`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_preference`;
CREATE TABLE `%%TBL-PREFIX%%base_preference` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `defaultValue` text NOT NULL,
  `sectionName` varchar(100) NOT NULL,
  `sortOrder` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `sortOrder` (`sortOrder`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_preference`
--

LOCK TABLES `%%TBL-PREFIX%%base_preference` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_preference` VALUES (1,'mass_mailing_subscribe','true','general',1),(12,'newsfeed_generate_action_set_timestamp','0','general',10000),(25,'send_wellcome_letter','0','general',99),(28,'profile_details_update_stamp','0','general',1),(31,'mailbox_user_settings_enable_sound','1','general',1),(32,'mailbox_user_settings_show_online_only','0','general',1);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_preference_data`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_preference_data`;
CREATE TABLE `%%TBL-PREFIX%%base_preference_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `userId` int(11) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`key`),
  KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_preference_data`
--

LOCK TABLES `%%TBL-PREFIX%%base_preference_data` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_preference_section`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_preference_section`;
CREATE TABLE `%%TBL-PREFIX%%base_preference_section` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `sortOrder` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_preference_section`
--

LOCK TABLES `%%TBL-PREFIX%%base_preference_section` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_preference_section` VALUES (1,'general',1);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_question`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_question`;
CREATE TABLE `%%TBL-PREFIX%%base_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `sectionName` varchar(32) DEFAULT NULL,
  `accountTypeName` varchar(32) DEFAULT NULL,
  `type` enum('text','select','datetime','boolean','multiselect','fselect') DEFAULT NULL,
  `presentation` enum('text','textarea','select','date','location','checkbox','multicheckbox','radio','url','password','age','birthdate','range','fselect') DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `onJoin` tinyint(1) NOT NULL DEFAULT '0',
  `onEdit` tinyint(1) NOT NULL DEFAULT '0',
  `onSearch` tinyint(1) NOT NULL DEFAULT '0',
  `onView` tinyint(1) NOT NULL DEFAULT '0',
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `removable` tinyint(1) NOT NULL DEFAULT '1',
  `columnCount` int(11) NOT NULL DEFAULT '1',
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  `custom` varchar(2048) DEFAULT '',
  `parent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `sectionId` (`sectionName`)
) ENGINE=MyISAM AUTO_INCREMENT=185 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_question`
--

LOCK TABLES `%%TBL-PREFIX%%base_question` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_question` VALUES (83,'c441a8a9b955647cdf4c81562d39068a','f90cde5913235d172603cc4e7b9726e3',NULL,'text','textarea',1,0,1,0,1,0,1,0,8,'[]',NULL),(81,'password','f90cde5913235d172603cc4e7b9726e3',NULL,'text','password',1,1,0,0,0,1,0,1,2,'[]',NULL),(104,'realname','f90cde5913235d172603cc4e7b9726e3',NULL,'text','text',1,1,1,1,1,0,0,0,3,'[]',NULL),(94,'sex','f90cde5913235d172603cc4e7b9726e3',NULL,'select','select',1,1,1,1,1,0,0,1,5,'[]',NULL),(82,'email','f90cde5913235d172603cc4e7b9726e3',NULL,'text','text',1,1,1,0,0,1,0,1,1,'[]',NULL),(111,'match_sex','f90cde5913235d172603cc4e7b9726e3',NULL,'multiselect','multicheckbox',0,0,1,0,1,0,1,2,6,'[]',NULL),(92,'birthdate','f90cde5913235d172603cc4e7b9726e3',NULL,'datetime','birthdate',1,1,1,1,1,0,0,0,4,'{\"year_range\":{\"from\":1900,\"to\":1998}}',NULL),(80,'username','f90cde5913235d172603cc4e7b9726e3',NULL,'text','text',1,1,1,0,0,1,0,1,0,'[]',NULL),(119,'joinStamp','f90cde5913235d172603cc4e7b9726e3',NULL,'select','date',0,0,0,0,1,1,0,0,8,'{\"year_range\":{\"from\":1930,\"to\":1975}}',NULL);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_question_account_type`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_question_account_type`;
CREATE TABLE `%%TBL-PREFIX%%base_question_account_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  `roleId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`name`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=54 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_question_account_type`
--

LOCK TABLES `%%TBL-PREFIX%%base_question_account_type` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_question_account_type` VALUES (53,'290365aadde35a97f11207ca7e4279cc',0,0);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_question_config`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_question_config`;
CREATE TABLE `%%TBL-PREFIX%%base_question_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `questionPresentation` enum('text','textarea','select','date','location','checkbox','multicheckbox','radio','url','password','age','birthdate') NOT NULL DEFAULT 'text',
  `name` varchar(255) NOT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `presentationClass` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_question_config`
--

LOCK TABLES `%%TBL-PREFIX%%base_question_config` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_question_config` VALUES (1,'date','year_range','','YearRange'),(2,'age','year_range','','YearRange'),(3,'birthdate','year_range','','YearRange');
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_question_data`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_question_data`;
CREATE TABLE `%%TBL-PREFIX%%base_question_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `questionName` varchar(255) NOT NULL DEFAULT '',
  `userId` int(11) NOT NULL DEFAULT '0',
  `textValue` text NOT NULL,
  `intValue` int(11) NOT NULL DEFAULT '0',
  `dateValue` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`questionName`),
  KEY `fieldName` (`questionName`),
  KEY `intValue` (`intValue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_question_data`
--

LOCK TABLES `%%TBL-PREFIX%%base_question_data` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_question_section`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_question_section`;
CREATE TABLE `%%TBL-PREFIX%%base_question_section` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `sortOrder` int(11) NOT NULL DEFAULT '1',
  `isHidden` int(11) NOT NULL DEFAULT '0',
  `isDeletable` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sectionName` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_question_section`
--

LOCK TABLES `%%TBL-PREFIX%%base_question_section` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_question_section` VALUES (34,'f90cde5913235d172603cc4e7b9726e3',0,0,0);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_question_to_account_type`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_question_to_account_type`;
CREATE TABLE `%%TBL-PREFIX%%base_question_to_account_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountType` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `questionName` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

--
-- Records of `%%TBL-PREFIX%%base_question_to_account_type`
--

LOCK TABLES `%%TBL-PREFIX%%base_question_to_account_type` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_question_to_account_type` VALUES (2,'290365aadde35a97f11207ca7e4279cc','9221d78a4201eac23c972e1d4aa2cee6'),(3,'290365aadde35a97f11207ca7e4279cc','c441a8a9b955647cdf4c81562d39068a'),(4,'290365aadde35a97f11207ca7e4279cc','password'),(5,'290365aadde35a97f11207ca7e4279cc','realname'),(6,'290365aadde35a97f11207ca7e4279cc','sex'),(7,'290365aadde35a97f11207ca7e4279cc','email'),(8,'290365aadde35a97f11207ca7e4279cc','match_sex'),(9,'290365aadde35a97f11207ca7e4279cc','birthdate'),(10,'290365aadde35a97f11207ca7e4279cc','username'),(11,'290365aadde35a97f11207ca7e4279cc','joinStamp'),(13,'290365aadde35a97f11207ca7e4279cc','relationship'),(14,'290365aadde35a97f11207ca7e4279cc','9221d78a4201eac23c972e1d4aa2cee6'),(15,'290365aadde35a97f11207ca7e4279cc','c441a8a9b955647cdf4c81562d39068a'),(16,'290365aadde35a97f11207ca7e4279cc','password'),(17,'290365aadde35a97f11207ca7e4279cc','realname'),(18,'290365aadde35a97f11207ca7e4279cc','sex'),(19,'290365aadde35a97f11207ca7e4279cc','email'),(20,'290365aadde35a97f11207ca7e4279cc','match_sex'),(21,'290365aadde35a97f11207ca7e4279cc','birthdate'),(22,'290365aadde35a97f11207ca7e4279cc','username'),(23,'290365aadde35a97f11207ca7e4279cc','joinStamp');
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_question_value`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_question_value`;
CREATE TABLE `%%TBL-PREFIX%%base_question_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `questionName` varchar(255) DEFAULT NULL,
  `value` int(11) NOT NULL DEFAULT '0',
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `questionName` (`questionName`,`value`)
) ENGINE=MyISAM AUTO_INCREMENT=427 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_question_value`
--

LOCK TABLES `%%TBL-PREFIX%%base_question_value` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_question_value` VALUES (335,'d68489df439fe45427e305a0e2dbe349',1,0),(336,'d68489df439fe45427e305a0e2dbe349',2,1),(305,'4971fc7002dca728f9a7f2a417c5284e',32,0),(304,'28f881c609c933f6b1719cdf6dcf4cab',8,3),(303,'28f881c609c933f6b1719cdf6dcf4cab',4,2),(302,'28f881c609c933f6b1719cdf6dcf4cab',2,1),(301,'28f881c609c933f6b1719cdf6dcf4cab',1,0),(300,'490d035a492be91d7bf9589f881e2d22',4,2),(299,'490d035a492be91d7bf9589f881e2d22',2,1),(298,'490d035a492be91d7bf9589f881e2d22',1,0),(297,'4971fc7002dca728f9a7f2a417c5284e',16,6),(296,'4971fc7002dca728f9a7f2a417c5284e',8,8),(294,'4971fc7002dca728f9a7f2a417c5284e',2,1),(334,'4971fc7002dca728f9a7f2a417c5284e',1,5),(292,'92947e48441284286fe8a7b175f34a6e',32,5),(291,'92947e48441284286fe8a7b175f34a6e',16,4),(290,'92947e48441284286fe8a7b175f34a6e',8,3),(289,'92947e48441284286fe8a7b175f34a6e',4,2),(288,'92947e48441284286fe8a7b175f34a6e',2,1),(287,'92947e48441284286fe8a7b175f34a6e',1,0),(307,'4971fc7002dca728f9a7f2a417c5284e',128,2),(337,'d68489df439fe45427e305a0e2dbe349',4,2),(309,'4971fc7002dca728f9a7f2a417c5284e',512,4),(310,'sex',1,0),(311,'sex',2,1),(312,'28f881c609c933f6b1719cdf6dcf4cab',16,4),(313,'28f881c609c933f6b1719cdf6dcf4cab',32,5),(314,'28f881c609c933f6b1719cdf6dcf4cab',64,6),(315,'28f881c609c933f6b1719cdf6dcf4cab',128,7),(316,'28f881c609c933f6b1719cdf6dcf4cab',256,8),(317,'28f881c609c933f6b1719cdf6dcf4cab',512,9),(318,'28f881c609c933f6b1719cdf6dcf4cab',1024,10),(319,'5d32f746a541b97f18a957ad5856318e',1,0),(320,'5d32f746a541b97f18a957ad5856318e',2,1),(321,'5d32f746a541b97f18a957ad5856318e',4,2),(322,'5d32f746a541b97f18a957ad5856318e',8,3),(323,'5d32f746a541b97f18a957ad5856318e',16,4),(324,'5d32f746a541b97f18a957ad5856318e',32,5),(325,'5d32f746a541b97f18a957ad5856318e',64,6),(326,'5d32f746a541b97f18a957ad5856318e',128,7),(327,'ab9fc810a1938e599b7d084efea97d91',1,0),(328,'ab9fc810a1938e599b7d084efea97d91',2,1),(329,'ab9fc810a1938e599b7d084efea97d91',4,2),(330,'ab9fc810a1938e599b7d084efea97d91',8,3),(333,'4971fc7002dca728f9a7f2a417c5284e',256,3),(338,'1e615090f832c4fbee805ded8e9ced08',1,0),(339,'1e615090f832c4fbee805ded8e9ced08',2,1),(340,'1e615090f832c4fbee805ded8e9ced08',4,2),(341,'f8f4c260c54166c8fcf79057fd85aec0',1,0),(342,'f8f4c260c54166c8fcf79057fd85aec0',2,1),(343,'f8f4c260c54166c8fcf79057fd85aec0',4,2),(347,'match_sex',2,1),(346,'match_sex',1,0),(356,'relationship',1,0),(357,'relationship',2,3),(358,'relationship',4,2),(359,'relationship',8,1),(360,'9ce3cf807fd94892c8c7bb75dc2af60d',1,0),(361,'9ce3cf807fd94892c8c7bb75dc2af60d',2,1),(362,'9ce3cf807fd94892c8c7bb75dc2af60d',4,2),(364,'9ce3cf807fd94892c8c7bb75dc2af60d',16,4),(365,'9ce3cf807fd94892c8c7bb75dc2af60d',32,5),(366,'9ce3cf807fd94892c8c7bb75dc2af60d',64,6),(367,'9ce3cf807fd94892c8c7bb75dc2af60d',128,7),(368,'9ce3cf807fd94892c8c7bb75dc2af60d',256,8),(369,'9ce3cf807fd94892c8c7bb75dc2af60d',512,9),(370,'9ce3cf807fd94892c8c7bb75dc2af60d',1024,10),(371,'9ce3cf807fd94892c8c7bb75dc2af60d',2048,11),(372,'9ce3cf807fd94892c8c7bb75dc2af60d',4096,12),(373,'9ce3cf807fd94892c8c7bb75dc2af60d',8192,13),(374,'9ce3cf807fd94892c8c7bb75dc2af60d',16384,14),(375,'9ce3cf807fd94892c8c7bb75dc2af60d',32768,15),(376,'9ce3cf807fd94892c8c7bb75dc2af60d',65536,16),(377,'9ce3cf807fd94892c8c7bb75dc2af60d',131072,17),(378,'9ce3cf807fd94892c8c7bb75dc2af60d',262144,18),(379,'9ce3cf807fd94892c8c7bb75dc2af60d',524288,19),(380,'9ce3cf807fd94892c8c7bb75dc2af60d',1048576,20),(381,'9ce3cf807fd94892c8c7bb75dc2af60d',2097152,21),(382,'9ce3cf807fd94892c8c7bb75dc2af60d',4194304,22),(383,'9ce3cf807fd94892c8c7bb75dc2af60d',8388608,23),(384,'9ce3cf807fd94892c8c7bb75dc2af60d',16777216,24),(385,'9ce3cf807fd94892c8c7bb75dc2af60d',33554432,25),(386,'9ce3cf807fd94892c8c7bb75dc2af60d',67108864,26),(387,'9ce3cf807fd94892c8c7bb75dc2af60d',134217728,27),(388,'9ce3cf807fd94892c8c7bb75dc2af60d',268435456,28),(389,'9ce3cf807fd94892c8c7bb75dc2af60d',536870912,29),(390,'9ce3cf807fd94892c8c7bb75dc2af60d',1073741824,30),(392,'8100f639e8becdefa741e05f0de73a15',1,0),(393,'8100f639e8becdefa741e05f0de73a15',2,1),(394,'d37d41b71a78dfb62b379d0aa7bd3ba5',1,0),(395,'c5dc53f371fe6ba3001a7c7e31bd95fc',1,0),(396,'c5dc53f371fe6ba3001a7c7e31bd95fc',2,1),(397,'c5dc53f371fe6ba3001a7c7e31bd95fc',4,2),(398,'c5dc53f371fe6ba3001a7c7e31bd95fc',8,3),(399,'c5dc53f371fe6ba3001a7c7e31bd95fc',16,4),(400,'7f2450f06779439551c75a8566c4070e',1,0),(401,'7f2450f06779439551c75a8566c4070e',2,1),(402,'7f2450f06779439551c75a8566c4070e',4,2),(403,'7f2450f06779439551c75a8566c4070e',8,3),(404,'7f2450f06779439551c75a8566c4070e',16,4),(405,'7f2450f06779439551c75a8566c4070e',32,5),(406,'7fbd88047415229961f4d2aac620fe25',1,0),(407,'7fbd88047415229961f4d2aac620fe25',2,1),(408,'7fbd88047415229961f4d2aac620fe25',4,2),(409,'7fbd88047415229961f4d2aac620fe25',8,3),(410,'7fbd88047415229961f4d2aac620fe25',16,4),(411,'7fbd88047415229961f4d2aac620fe25',32,5),(412,'7fbd88047415229961f4d2aac620fe25',64,6),(413,'a5115de7f38988e748370a59ba0b311d',1,0),(414,'a5115de7f38988e748370a59ba0b311d',2,1),(415,'a5115de7f38988e748370a59ba0b311d',4,2),(416,'a5115de7f38988e748370a59ba0b311d',8,3),(417,'d8aa20d67fbb6c6864e46c474d0bde10',1,0),(418,'d8aa20d67fbb6c6864e46c474d0bde10',2,1),(419,'d8aa20d67fbb6c6864e46c474d0bde10',4,2),(420,'d8aa20d67fbb6c6864e46c474d0bde10',8,3),(421,'d8aa20d67fbb6c6864e46c474d0bde10',16,4),(422,'d8aa20d67fbb6c6864e46c474d0bde10',32,5),(423,'d8aa20d67fbb6c6864e46c474d0bde10',64,6),(424,'d8aa20d67fbb6c6864e46c474d0bde10',128,7),(425,'d8aa20d67fbb6c6864e46c474d0bde10',256,8),(426,'d8aa20d67fbb6c6864e46c474d0bde10',512,9);
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_rate`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_rate`;
CREATE TABLE `%%TBL-PREFIX%%base_rate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entityType` varchar(255) NOT NULL,
  `entityId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `score` int(10) unsigned NOT NULL,
  `timeStamp` int(10) unsigned NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `entityType` (`entityType`),
  KEY `entityId` (`entityId`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_rate`
--

LOCK TABLES `%%TBL-PREFIX%%base_rate` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_remote_auth`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_remote_auth`;
CREATE TABLE `%%TBL-PREFIX%%base_remote_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `userId` int(11) NOT NULL,
  `remoteId` varchar(50) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `custom` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Records of `%%TBL-PREFIX%%base_remote_auth`
--

LOCK TABLES `%%TBL-PREFIX%%base_remote_auth` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_restricted_usernames`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_restricted_usernames`;
CREATE TABLE `%%TBL-PREFIX%%base_restricted_usernames` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Records of `%%TBL-PREFIX%%base_restricted_usernames`
--

LOCK TABLES `%%TBL-PREFIX%%base_restricted_usernames` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_scheme`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_scheme`;
CREATE TABLE `%%TBL-PREFIX%%base_scheme` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `rightCssClass` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `leftCssClass` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `cssClass` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Records of `%%TBL-PREFIX%%base_scheme`
--

LOCK TABLES `%%TBL-PREFIX%%base_scheme` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_scheme` VALUES (1,'peep_superwide','peep_supernarrow','peep_scheme_enew'),(2,'peep_wide','peep_narrow','peep_scheme_nw'),(3,'peep_column','peep_column','peep_scheme_equal'),(4,'peep_narrow','peep_wide','peep_scheme_wn'),(5,'peep_supernarrow','peep_superwide','peep_scheme_ewen');
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_search`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_search`;
CREATE TABLE `%%TBL-PREFIX%%base_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `timeStamp` (`timeStamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_search`
--

LOCK TABLES `%%TBL-PREFIX%%base_search` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_search_entity`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_search_entity`;
CREATE TABLE `%%TBL-PREFIX%%base_search_entity` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entityType` varchar(50) NOT NULL,
  `entityId` int(10) unsigned NOT NULL,
  `text` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `timeStamp` int(10) unsigned NOT NULL,
  `activated` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `entity` (`entityType`,`entityId`),
  KEY `status` (`status`,`activated`,`timeStamp`),
  FULLTEXT KEY `entityText` (`text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_search_entity`
--

LOCK TABLES `%%TBL-PREFIX%%base_search_entity` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_search_entity_tag`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_search_entity_tag`;
CREATE TABLE `%%TBL-PREFIX%%base_search_entity_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entityTag` varchar(50) NOT NULL,
  `searchEntityId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `searchEntityId` (`searchEntityId`),
  KEY `entityTag` (`entityTag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_search_entity_tag`
--

LOCK TABLES `%%TBL-PREFIX%%base_search_entity_tag` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_search_result`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_search_result`;
CREATE TABLE `%%TBL-PREFIX%%base_search_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `searchId` int(11) NOT NULL DEFAULT '0',
  `userId` int(11) NOT NULL,
  `sortOrder` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `searchResult` (`searchId`,`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_search_result`
--

LOCK TABLES `%%TBL-PREFIX%%base_search_result` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_tag`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_tag`;
CREATE TABLE `%%TBL-PREFIX%%base_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `label` (`label`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of for table `%%TBL-PREFIX%%base_tag`
--

LOCK TABLES `%%TBL-PREFIX%%base_tag` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_theme`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_theme`;
CREATE TABLE `%%TBL-PREFIX%%base_theme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `developerKey` varchar(255) DEFAULT NULL,
  `build` int(11) NOT NULL DEFAULT '0',
  `update` tinyint(4) NOT NULL DEFAULT '0',
  `licenseKey` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `isActive` tinyint(1) NOT NULL DEFAULT '0',
  `customCss` text,
  `mobileCustomCss` text,
  `customCssFileName` varchar(255) DEFAULT NULL,
  `sidebarPosition` enum('left','right','none') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=956 DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_theme`
--

LOCK TABLES `%%TBL-PREFIX%%base_theme` WRITE;
INSERT INTO `%%TBL-PREFIX%%base_theme` VALUES (925,'e547ebcf734341ec11911209d93a1054',7000,0,NULL,'peeptheme','Peeptheme','{\"name\":\"Peeptheme\",\"key\":\"peeptheme\",\"version\":\"1.0\",\"for\":\"1.0 \",\"description\":\"The Peepmatches default theme\",\"author\":\"Peepdev Co.\",\"authorEmail\":\"addons@peepdev.com\",\"authorUrl\":\"http:\\/\\/www.peepdev.com\\/company\",\"developerKey\":\"peepdev1\",\"build\":\"1\",\"copyright\":\"(C) 2015 Peepdev Co. All rights reserved.\",\"license\":\"Peepdev License\",\"licenseUrl\":\"http:\\/\\/www.peepdev.com\\/licenses\",\"sidebarPosition\":\"right\"}',0,NULL,NULL,NULL,'right');
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_theme_content`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_theme_content`;
CREATE TABLE `%%TBL-PREFIX%%base_theme_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `themeId` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `themeId` (`themeId`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_theme_content`
--

LOCK TABLES `%%TBL-PREFIX%%base_theme_content` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_theme_control`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_theme_control`;
CREATE TABLE `%%TBL-PREFIX%%base_theme_control` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attribute` varchar(255) NOT NULL,
  `selector` text NOT NULL,
  `defaultValue` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'text',
  `themeId` int(10) unsigned NOT NULL,
  `key` varchar(255) NOT NULL DEFAULT '',
  `section` text NOT NULL,
  `label` text NOT NULL,
  `description` text,
  `mobile` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`key`),
  KEY `themeId` (`themeId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_theme_control`
--

LOCK TABLES `%%TBL-PREFIX%%base_theme_control` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_theme_control_value`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_theme_control_value`;
CREATE TABLE `%%TBL-PREFIX%%base_theme_control_value` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `themeControlKey` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `themeId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `themeControlKey` (`themeControlKey`,`themeId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_theme_control_value`
--

LOCK TABLES `%%TBL-PREFIX%%base_theme_control_value` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_theme_image`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_theme_image`;
CREATE TABLE `%%TBL-PREFIX%%base_theme_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_theme_image`
--

LOCK TABLES `%%TBL-PREFIX%%base_theme_image` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_theme_master_page`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_theme_master_page`;
CREATE TABLE `%%TBL-PREFIX%%base_theme_master_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `themeId` int(11) NOT NULL,
  `documentKey` varchar(255) NOT NULL,
  `masterPage` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `themeId` (`themeId`),
  KEY `documentKey` (`documentKey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_theme_master_page`
--

LOCK TABLES `%%TBL-PREFIX%%base_theme_master_page` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_user`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_user`;
CREATE TABLE `%%TBL-PREFIX%%base_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL DEFAULT '',
  `username` varchar(32) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '',
  `joinStamp` int(11) NOT NULL DEFAULT '0',
  `activityStamp` int(11) NOT NULL DEFAULT '0',
  `accountType` varchar(32) NOT NULL DEFAULT '',
  `emailVerify` tinyint(2) NOT NULL DEFAULT '0',
  `joinIp` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `accountType` (`accountType`),
  KEY `joinStamp` (`joinStamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';

--
-- Records of `%%TBL-PREFIX%%base_user`
--

LOCK TABLES `%%TBL-PREFIX%%base_user` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_user_auth_token`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_user_auth_token`;
CREATE TABLE `%%TBL-PREFIX%%base_user_auth_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `token` varchar(50) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_user_auth_token`
--

LOCK TABLES `%%TBL-PREFIX%%base_user_auth_token` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_user_block`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_user_block`;
CREATE TABLE `%%TBL-PREFIX%%base_user_block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `blockedUserId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId_blockedUserId` (`userId`,`blockedUserId`),
  KEY `userId` (`userId`),
  KEY `blockedUserId` (`blockedUserId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_user_block`
--

LOCK TABLES `%%TBL-PREFIX%%base_user_block` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_user_disapprove`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_user_disapprove`;
CREATE TABLE `%%TBL-PREFIX%%base_user_disapprove` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_user_disapprove`
--

LOCK TABLES `%%TBL-PREFIX%%base_user_disapprove` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_user_featured`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_user_featured`;
CREATE TABLE `%%TBL-PREFIX%%base_user_featured` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 MIN_ROWS=20;

--
-- Records of `%%TBL-PREFIX%%base_user_featured`
--

LOCK TABLES `%%TBL-PREFIX%%base_user_featured` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_user_online`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_user_online`;
CREATE TABLE `%%TBL-PREFIX%%base_user_online` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `activityStamp` int(11) NOT NULL,
  `context` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of`%%TBL-PREFIX%%base_user_online`
--

LOCK TABLES `%%TBL-PREFIX%%base_user_online` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_user_reset_password`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_user_reset_password`;
CREATE TABLE `%%TBL-PREFIX%%base_user_reset_password` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `code` varchar(150) NOT NULL,
  `expirationTimeStamp` int(11) NOT NULL,
  `updateTimeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_user_reset_password`
--

LOCK TABLES `%%TBL-PREFIX%%base_user_reset_password` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_user_status`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_user_status`;
CREATE TABLE `%%TBL-PREFIX%%base_user_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_user_status`
--

LOCK TABLES `%%TBL-PREFIX%%base_user_status` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_user_suspend`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_user_suspend`;
CREATE TABLE `%%TBL-PREFIX%%base_user_suspend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_user_suspend`
--

LOCK TABLES `%%TBL-PREFIX%%base_user_suspend` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `%%TBL-PREFIX%%base_vote`
--

DROP TABLE IF EXISTS `%%TBL-PREFIX%%base_vote`;
CREATE TABLE `%%TBL-PREFIX%%base_vote` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL,
  `entityId` int(11) unsigned NOT NULL,
  `entityType` varchar(255) NOT NULL,
  `vote` tinyint(4) NOT NULL,
  `timeStamp` int(11) unsigned NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `entityId` (`entityId`),
  KEY `entityType` (`entityType`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Records of `%%TBL-PREFIX%%base_vote`
--

LOCK TABLES `%%TBL-PREFIX%%base_vote` WRITE;
UNLOCK TABLES;