<?php

class PROFILELIKE_CMP_LikedWidget extends BASE_CLASS_Widget
{
	public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
		$userId = PEEP::getUser()->getId();
		$language = PEEP::getLanguage();
		$service = PROFILELIKE_BOL_ProfilelikeDao::getInstance();
		$profileId = $service->getProfileId();
		$whoLikeYou = $service->whoLikeYou($userId, PEEP::getConfig()->getValue('profilelike', 'thumbnails_in_profile_widget'));
		
		if (!$whoLikeYou || $userId != $profileId)
        {
        	$this->setVisible(false);
        	return;
        }

		$userIdList = array();
		
		foreach($whoLikeYou as $t) {
			array_push($userIdList, $t->userId);
		}

		$avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList, true, true, true, true);
		$this->assign('avatars', $avatars);
		$this->assign('people', $whoLikeYou);
		$this->assign('url', PEEP_URL_HOME);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_ICON => self::ICON_FRIENDS,
            self::SETTING_TITLE => PEEP::getLanguage()->text('profilelike', 'people_who_profile_like_widget_title')
        );
    }
} 