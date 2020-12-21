<?php

class PROFILELIKE_CMP_LikesWidget extends BASE_CLASS_Widget
{
	public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
		$userId = PEEP::getUser()->getId();
		$language = PEEP::getLanguage();
		$service = PROFILELIKE_BOL_ProfilelikeDao::getInstance();
		$mostLike = $service->mostLikeMembers(PEEP::getConfig()->getValue('profilelike', 'thumbnails_in_dashboard_widget'));
	
		if (!$mostLike)
        {
        	$this->setVisible(false);
        	return;
        }

		$userIdList = array();
		
		foreach($mostLike as $t) {
			array_push($userIdList, $t->id);
		}

		$avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList, true, true, true, true);
		$this->assign('avatars', $avatars);
		$this->assign('people', $mostLike);
		$this->assign('url', PEEP_URL_HOME);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_ICON => self::ICON_FRIENDS,
            self::SETTING_TITLE => PEEP::getLanguage()->text('profilelike', 'most_like_members_widget_title')
        );
    }
} 