<?php

class BASE_CMP_UserAvatarWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $avatarService = BOL_AvatarService::getInstance();

        $viewerId = PEEP::getUser()->getId();

        $userId = $paramObj->additionalParamList['entityId'];

        $owner = false;
        
        if ( $viewerId == $userId )
        {
            $owner = true;
            

            $label = PEEP::getLanguage()->text('base', 'avatar_change');

            $script =
            '$("#avatar-change").click(function(){
                document.avatarFloatBox = PEEP.ajaxFloatBox(
                    "BASE_CMP_AvatarChange",
                    { params : { step : 1 } },
                    { width : 749, title: ' . json_encode($label) . '}
                );
            });

            PEEP.bind("base.avatar_cropped", function(data){
                if ( data.bigUrl != undefined ) {
                    $("#avatar_console_image").css({ "background-image" : "url(" + data.bigUrl + ")" });
                }

                if ( data.modearationStatus )
                {
                    if ( data.modearationStatus != "active" )
                    {
                        $(".peep_avatar_pending_approval").show();
                    }
                    else 
                    {
                        $(".peep_avatar_pending_approval").hide();
                    }
                }
            });
            ';

            PEEP::getDocument()->addOnloadScript($script);
        }
        
        $isModerator = (PEEP::getUser()->isAuthorized('base') || PEEP::getUser()->isAdmin());
        
        $this->assign('owner', $owner);
        $this->assign('isModerator', $isModerator);
        
        $avatarDto = $avatarService->findByUserId($userId);
        
        $this->assign('hasAvatar', !empty($avatarDto));
        $moderation = false;

        // approve button
        if ( $isModerator && !empty($avatarDto) && $avatarDto->status == BOL_ContentService::STATUS_APPROVAL )
        {
            $moderation = true;
            
            $script = ' window.avartar_arrove_request = false;
            $("#avatar-approve").click(function(){
            
                if ( window.avartar_arrove_request == true )
                {
                    return;
                }
                
                window.avartar_arrove_request = true;
                
                $.ajax({
                    "type": "POST",
                    "url": '.json_encode(PEEP::getRouter()->urlFor('BASE_CTRL_Avatar', 'ajaxResponder')).',
                    "data": {
                        \'ajaxFunc\' : \'ajaxAvatarApprove\',
                        \'avatarId\' : '.((int)$avatarDto->id).'
                    },
                    "success": function(data){
                        if ( data.result == true )
                        {
                            if ( data.message )
                            {
                                PEEP.info(data.message);
                            }
                            else
                            {
                                PEEP.info('.json_encode(PEEP::getLanguage()->text('base', 'avatar_has_been_approved')).');
                            }
                            
                            $("#avatar-approve").remove();
                            $(".peep_avatar_pending_approval").hide();
                        }
                        else
                        {
                            if ( data.error )
                            {
                                PEEP.info(data.error);
                            }
                        }
                    },
                    "complete": function(){
                        window.avartar_arrove_request = false;
                    },
                    "dataType": "json"
                });
            }); ';

            PEEP::getDocument()->addOnloadScript($script);
        }
        
        $avatar = $avatarService->getAvatarUrl($userId, 2, null, false, !($moderation || $owner));
        $this->assign('avatar', $avatar ? $avatar : $avatarService->getDefaultAvatarUrl(2));
        $roles = BOL_AuthorizationService::getInstance()->getRoleListOfUsers(array($userId));
        $this->assign('role', !empty($roles[$userId]) ? $roles[$userId] : null);

        $userService = BOL_UserService::getInstance();

        $showPresence = true;
        // Check privacy permissions 
        $eventParams = array(
            'action' => 'base_view_my_presence_on_site',
            'ownerId' => $userId,
            'viewerId' => PEEP::getUser()->getId()
        );
        try
        {
            PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {
            $showPresence = false;
        }

        $this->assign('isUserOnline', ($userService->findOnlineUserById($userId) && $showPresence));
        $this->assign('userId', $userId);

        $this->assign('avatarSize', PEEP::getConfig()->getValue('base', 'avatar_big_size'));
        
        $this->assign('moderation', $moderation);
        $this->assign('avatarDto', $avatarDto);
        
        PEEP::getLanguage()->addKeyForJs('base', 'avatar_has_been_approved');
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('base', 'avatar_widget'),
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_ICON => self::ICON_PICTURE,
            self::SETTING_FREEZE => true
        );
    }
}