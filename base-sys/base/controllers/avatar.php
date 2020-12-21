<?php

class BASE_CTRL_Avatar extends PEEP_ActionController
{
    /**
     * @var BOL_AvatarService
     */
    private $avatarService;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->avatarService = BOL_AvatarService::getInstance();
    }

    /**
     * Method acts as ajax responder. Calls methods using ajax
     *
     * @return string
     */
    public function ajaxResponder()
    {
        $request = $_POST;

        if ( isset($request['ajaxFunc']) && PEEP::getRequest()->isAjax() )
        {
            $callFunc = (string) $request['ajaxFunc'];

            $result = call_user_func(array($this, $callFunc), $request);
        }
        else
        {
            exit();
        }

        exit(json_encode($result));
    }

    public function ajaxUploadImage( $params )
    {
        if ( isset($_FILES['file']) )
        {
            $file = $_FILES['file'];
            $lang = PEEP::getLanguage();

            if ( !UTIL_File::validateImage($file['name']) )
            {
                return array('result' => false, 'error' => $lang->text('base', 'not_valid_image'));
            }
            
            $message = BOL_FileService::getInstance()->getUploadErrorMessage($_FILES['file']['error']);
            
            if ( !empty($message) )
            {
                return array('result' => false, 'error' => $message);
            }
            
            $filesize = PEEP::getConfig()->getValue('base', 'avatar_max_upload_size');
            
            if ( $filesize*1024*1024 < $_FILES['file']['size'] )
            {
                $message = PEEP::getLanguage()->text('base', 'upload_file_max_upload_filesize_error');
                return array('result' => false, 'error' => $message);
            }

            $avatarService = BOL_AvatarService::getInstance();

            $key = $avatarService->getAvatarChangeSessionKey();
            $uploaded = $avatarService->uploadUserTempAvatar($key, $file['tmp_name']);

            if ( !$uploaded )
            {
                return array('result' => false, 'error' => $lang->text('base', 'upload_avatar_faild'));
            }

            $url = $avatarService->getTempAvatarUrl($key, 3);

            return array('result' => true, 'url' => $url);
        }

        return array('result' => false);
    }

    public function ajaxDeleteImage( $params )
    {
        $avatarService = BOL_AvatarService::getInstance();

        $key = $avatarService->getAvatarChangeSessionKey();
        $avatarService->deleteUserTempAvatar($key);

        return array('result' => true);
    }

    public function ajaxLoadMore( $params )
    {
        if ( isset($params['entityType']) && isset($params['entityId']) && isset($params['offset']) )
        {
            $entityType = $params['entityType'];
            $entityId = $params['entityId'];
            $offset = $params['offset'];

            $section = BOL_AvatarService::getInstance()->getAvatarChangeSection($entityType, $entityId, $offset);

            if ( $section )
            {
                $cmp = new BASE_CMP_AvatarLibrarySection($section['list'], $offset, $section['count']);
                $markup = $cmp->render();

                return array('result' => true, 'markup' => $markup, 'count' => $section['count']);
            }
        }

        return array('result' => false);
    }

    public function ajaxCropPhoto( $params )
    {
        if ( !isset($params['coords']) || !isset($params['view_size']) )
        {
            return array('result' => false, 'case' => 0);
        }

        $changeUserAvatar = isset($params['changeUserAvatar']) && (int) !$params['changeUserAvatar'] ? false : true;
        $coords = $params['coords'];
        $viewSize = $params['view_size'];
        $path = null;

        $localFile = false;

        $avatarService = BOL_AvatarService::getInstance();

        if ( !empty($params['entityType']) && !empty($params['id']) )
        {
            $item = $avatarService->getAvatarChangeGalleryItem($params['entityType'], $params['entityId'], $params['id']);
            
            if ( !$item || empty($item['path']) || !PEEP::getStorage()->fileExists($item['path']) )
            {
                return array('result' => false, 'case' => 1);
            }

            $path = $item['path'];
        }
        else if ( isset($params['url']) ) 
        {
            $path = UTIL_Url::getLocalPath($params['url']);
            
            if ( !PEEP::getStorage()->fileExists($path)  )
            {
                if ( !file_exists($path) )
                {
                    return array('result' => false, 'case' => 2);
                }
                
                $localFile = true;
            }
        }

        $userId = PEEP_Auth::getInstance()->getUserId();
        if ( $userId && $changeUserAvatar)
        {
            $avatar = $avatarService->findByUserId($userId);

            try
            {
                $event = new PEEP_Event('base.before_avatar_change', array(
                    'userId' => $userId,
                    'avatarId' => $avatar ? $avatar->id : null,
                    'upload' => false,
                    'crop' => true
                ));
                PEEP::getEventManager()->trigger($event);

                if ( !$avatarService->cropAvatar($userId, $path, $coords, $viewSize, array('isLocalFile' => $localFile )) )
                {
                    return array(
                        'result' => false,
                        'case' => 6
                    );
                }

                $avatar = $avatarService->findByUserId($userId, false);

                $event = new PEEP_Event('base.after_avatar_change', array(
                    'userId' => $userId,
                    'avatarId' => $avatar ? $avatar->id : null,
                    'upload' => false,
                    'crop' => true
                ));
                PEEP::getEventManager()->trigger($event);

                return array(
                    'result' => true,
                    'modearationStatus' => $avatar->status,
                    'url' => $avatarService->getAvatarUrl($userId, 1, null, false, false),
                    'bigUrl' => $avatarService->getAvatarUrl($userId, 2, null, false, false)
                );
            }
            catch ( Exception $e )
            {
                return array('result' => false, 'case' => 4);
            }
        }
        else
        {
            $key = $avatarService->getAvatarChangeSessionKey();
            $path = $avatarService->getTempAvatarPath($key, 3);
            
            if ( !file_exists($path) )
            {
                return array('result' => false, 'case' => 5);
            }
            
            $avatarService->cropTempAvatar($key, $coords, $viewSize);

            return array(
                'result' => true,
                'url' => $avatarService->getTempAvatarUrl($key, 1),
                'bigUrl' => $avatarService->getTempAvatarUrl($key, 2)
            );
        }
    }
    
    public function ajaxAvatarApprove( $params )
    {
        if ( isset($params['avatarId']) && PEEP::getUser()->isAuthorized('base') )
        {
            $entityId = $params['avatarId'];
            $entityType = BASE_CLASS_ContentProvider::ENTITY_TYPE_AVATAR;

            $backUrl = PEEP::getRouter()->urlForRoute("event.view", array(
                "eventId" => $entityId
            ));

            $event = new PEEP_Event("moderation.approve", array(
                "entityType" => $entityType,
                "entityId" => $entityId
            ));

            PEEP::getEventManager()->trigger($event);

            $data = $event->getData();
            
            if ( empty($data) )
            {
                return array('result' => true);
            }
            
            if ( !empty($data["message"]) )
            {
                return array('result' => true, 'message' => $data["message"]);
            }
            else
            {
                return array('result' => false, 'error' => $data["error"]);
            }
        }

        return array('result' => false);
    }
}