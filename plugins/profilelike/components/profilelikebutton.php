<?php

class PROFILELIKE_CMP_Profilelikebutton extends BASE_CLASS_Widget
{
   public function __construct( BASE_CLASS_WidgetParameter $params )
    {
if( !PEEP::getUser()->isAuthenticated())
        {
            $this->setVisible(false);
            return array();
        }
        parent::__construct();
		$userId = PEEP::getUser()->getId();
		$language = PEEP::getLanguage();
		$service = PROFILELIKE_BOL_ProfilelikeDao::getInstance();
		$profileId = $service->getProfileId();
		$iLiked = $service->checkLike($userId, $profileId);
		$sFile = PEEP::getRouter()->urlFor('PROFILELIKE_CTRL_Profilelike');
		
		if($userId == $profileId)
		{
			$script = NULL;
		}
		if(!$iLiked)
		{
			$title	=  $language->text('profilelike', 'profilelike_button_label');
       
$likeico = PEEP::getPluginManager()->getPlugin('profilelike')->getStaticUrl() . 'img/like_heart.png';
			$action			=	'profilelike';
                        
		}
		else
		{
			$title	= $language->text('profilelike', 'unprofilelike_button_label');
$likeico = PEEP::getPluginManager()->getPlugin('profilelike')->getStaticUrl() . 'img/dislike_heart.png';
        
			$action			= 'unprofilelike';
                        
		}
		if($userId != $profileId)
		{
			$script = "<script>
							$('.peep_profile_gallery_avatar_image').append(
									'<div>' +
									'<form id=\"form-profilelike\" method=\"post\" action=\"".$sFile."\">' +
									'<input type=\"hidden\" name=\"actionlike\" value=\"".$action."\" /> ' +
									'<input type=\"hidden\" name=\"userid\" value=\"".$userId."\" /> ' +
									'<input type=\"hidden\" name=\"profileid\" value=\"".$profileId."\" /> ' +
'<a id=\"profile-like-action\" href=\"javascript://\" id=\"profile-like-action\">".$title."</a>'+
									'</form></div>' +
									'</div>'
									
							);
							
							var form = $('#form-profilelike'),
							action = $('#profile-like-action');
                                                        
									
							action.click(function(event){
								
								form.submit();
							});
						</script>";
		}
		$this->assign('likeico', $likeico);
                
		$this->assign('script', $script);
		$this->assign('userId', $userId);
		$this->assign('profileId', $profileId);
		if($userId == $profileId)
		{
			$this->setVisible(true);
			return;
		}
    }
	
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_SHOW_TITLE => false,
            //self::SETTING_ICON => self::ICON_GEAR_WHEEL,
            //self::SETTING_TITLE => PEEP::getLanguage()->text('profilelike', 'people_who_profile_like_widget_title')
        );
    }
} 
