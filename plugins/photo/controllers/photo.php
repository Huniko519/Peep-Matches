<?php

class PHOTO_CTRL_Photo extends PEEP_ActionController
{
    private $photoService;
    private $photoAlbumService;

    public function __construct()
    {
        parent::__construct();

        $this->photoService = PHOTO_BOL_PhotoService::getInstance();
        $this->photoAlbumService = PHOTO_BOL_PhotoAlbumService::getInstance();

        PEEP::getDocument()->setHeadingIconClass('peep_ic_picture');
        PEEP::getDocument()->setHeading(PEEP::getLanguage()->text('photo', 'page_title_browse_photos'));
    }

    public function init()
    {
        parent::init();
        
        $hadler = PEEP::getRequestHandler()->getHandlerAttributes();
        
        if ( PEEP::getUser()->isAuthenticated() )
        {
            switch ( $hadler[PEEP_RequestHandler::ATTRS_KEY_ACTION] )
            {
                case 'view':
                    $ownerMode = $this->photoService->findPhotoOwner($hadler[PEEP_RequestHandler::ATTRS_KEY_VARLIST]['id']) == PEEP::getUser()->getId();
                    $contentOwner = $this->photoService->findPhotoOwner((int)$hadler[PEEP_RequestHandler::ATTRS_KEY_VARLIST]['id']);
                    break;
                case 'getFloatbox':
                    $ownerMode = $this->photoService->findPhotoOwner($_POST['photoId']) == PEEP::getUser()->getId();
                    break;
                case 'userAlbums':
                case 'userPhotos':
                    $ownerMode = $hadler[PEEP_RequestHandler::ATTRS_KEY_VARLIST]['user'] == PEEP::getUser()->getUserObject()->username;
                    $contentOwner = ($user = BOL_UserService::getInstance()->findByUsername($hadler[PEEP_RequestHandler::ATTRS_KEY_VARLIST]['user'])) !== NULL ? $user->id : 0;
                    break;
                case 'userAlbum':
                    $ownerMode = $this->photoAlbumService->isAlbumOwner($hadler[PEEP_RequestHandler::ATTRS_KEY_VARLIST]['album'], PEEP::getUser()->getId());
                    $contentOwner = ($album = $this->photoAlbumService->findAlbumById((int)$hadler[PEEP_RequestHandler::ATTRS_KEY_VARLIST]['album'])) !== NULL ? $album->userId : 0;
                    break;
                case 'ajaxResponder':
                    switch ( $_POST['ajaxFunc'] )
                    {
                        case 'getAlbumList':
                            $ownerMode = $_POST['userId'] == PEEP::getUser()->getId();
                            break;
                        case 'getPhotoList':
                            if ( !empty($_POST['userId']) )
                            {
                                $ownerMode = $_POST['userId'] == PEEP::getUser()->getId();
                            }
                            elseif ( !empty($_POST['albumId']) )
                            {
                                $albumId = (int)$_POST['albumId'];
                                $ownerMode = $this->photoAlbumService->isAlbumOwner($albumId, PEEP::getUser()->getId());
                            }
                            else
                            {
                                $ownerMode = false;
                            }
                            break;
                        case 'ajaxDeletePhotos':
                        case 'ajaxMoverToAlbum':
                            $ownerMode = $this->photoAlbumService->isAlbumOwner($_POST['albumId'], PEEP::getUser()->getId());
                            break;
                        case 'ajaxSetFeaturedStatus':
                        case 'setAsAvatar':
                            $ownerMode = $this->photoService->findPhotoOwner($_POST['entityId']) == PEEP::getUser()->getId();
                            break;
                        case 'ajaxDeletePhoto':
                            $photoId = (int)$_POST['entityId'];
                            $ownerId = $this->photoService->findPhotoOwner($photoId);
                            $ownerMode = $ownerId !== null && $ownerId == PEEP::getUser()->getId();
                            break;
                        case 'ajaxDeletePhotoAlbum':
                            $albumId = (int)$_POST['entityId'];
                            $ownerMode = $this->photoAlbumService->isAlbumOwner($albumId, PEEP::getUser()->getId());
                            break;
                        case 'getFloatbox':
                            $photoId = (int)$_POST['photoId'];
                            $ownerId = $this->photoService->findPhotoOwner($photoId);
                            $ownerMode = $ownerId !== null && $ownerId == PEEP::getUser()->getId();
                            break;
                        default:
                            $ownerMode = FALSE;
                            break;
                    }
                    break;
                case 'ajaxUpdatePhoto':
                    $ownerMode = $this->photoService->findPhotoOwner($_POST['photoId']) == PEEP::getUser()->getId();
                    break;
                case 'downloadPhoto':
                    $ownerMode = $this->photoService->findPhotoOwner($hadler[PEEP_RequestHandler::ATTRS_KEY_VARLIST]['id']) == PEEP::getUser()->getId();
                    break;
                case 'ajaxUpdateAlbum':
                    $ownerMode = $this->photoAlbumService->isAlbumOwner($_POST['album-id'], PEEP::getUser()->getId());
                    break;
                case 'ajaxCreateAlbum':
                    $ownerMode = TRUE;
                    break;
                default:
                    $ownerMode = FALSE;
                    break;
            }
        }
        else
        {
            $ownerMode = FALSE;
        }
        
        $modPermissions = PEEP::getUser()->isAuthorized('photo');
        $isAuthorized = PEEP::getUser()->isAuthorized('photo', 'view');
        
        if ( !$ownerMode && !$modPermissions && !$isAuthorized )
        {
            if ( PEEP::getRequest()->isAjax() )
            {
                exit(json_encode(array('result' => FALSE, 'status' => 'error', 'msg' => PEEP::getLanguage()->text('photo', 'auth_view_permissions'))));
            }
            else
            {
                $this->setTemplate(PEEP::getPluginManager()->getPlugin('base')->getCtrlViewDir() . 'authorization_failed.html');
                
                return;
            }
        }
        
        if ( !empty($contentOwner) && !$ownerMode && !$modPermissions )
        {
            $privacyParams = array('action' => 'photo_view_album', 'ownerId' => $contentOwner, 'viewerId' => PEEP::getUser()->getId());
            $event = new PEEP_Event('privacy_check_permission', $privacyParams);
            PEEP::getEventManager()->trigger($event);
        }
        
        if ( PEEP::getRequest()->isAjax() || in_array($hadler[PEEP_RequestHandler::ATTRS_KEY_ACTION], array('downloadPhoto', 'approve')) )
        {
            return;
        }
        
        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, 'photo', 'photo');
        
        if ( $hadler[PEEP_RequestHandler::ATTRS_KEY_ACTION] != 'view' )
        {
            $this->addComponent('pageHead', PEEP::getClassInstance('PHOTO_CMP_PageHead', $ownerMode, !empty($album) ? $album : NULL));
        }
    }

    /**
     * View photo action
     *
     * @param array $params
     * @throws AuthorizationException
     * @throws Redirect404Exception
     */
    public function view( array $params )
    {
        if ( empty($params['id']) || ($photo = $this->photoService->findPhotoById((int)$params['id'])) === NULL )
        {
            throw new Redirect404Exception();
        }

//        if ( $photo->status != PHOTO_BOL_PhotoDao::STATUS_APPROVED )
//        {
//            throw new Redirect404Exception();
//        }

        $album = $this->photoAlbumService->findAlbumById($photo->albumId);
        $this->assign('album', $album);
        
        
        PEEP::getDocument()->setHeading($album->name);
        PEEP::getDocument()->setHeadingIconClass('peep_ic_picture');

        $imageUrl = $this->photoService->getPhotoUrl($photo->id, FALSE, $photo->hash);
        PEEP::getDocument()->addMetaInfo('image', $imageUrl, 'itemprop');
        PEEP::getDocument()->addMetaInfo('og:image', $imageUrl, 'property');

        $description = strip_tags(str_replace(PHP_EOL, ' ', $photo->description));
        $description = mb_strlen($description) ? $description : $photo->id;
        
        PEEP::getDocument()->setTitle(PEEP::getLanguage()->text('photo', 'meta_title_photo_view', array('title' => $description)));
        $tagsArr = BOL_TagService::getInstance()->findEntityTags($photo->id, 'photo');

        $labels = array();
        foreach ( $tagsArr as $t )
        {
            $labels[] = $t->label;
        }
        $tagStr = $tagsArr ? implode(', ', $labels) : '';
        PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('photo', 'meta_description_photo_view', array('title' => $description, 'tags' => $tagStr)));
        
        $event = new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_INIT_FLOATBOX, array('layout' => 'page'));
        PEEP::getEventManager()->trigger($event);

        PEEP::getDocument()->addOnloadScript(
            UTIL_JsGenerator::composeJsString(';window.photoView.setId({$id}, {$listType}, {$extend})',
                array(
                    'id' => (int)$photo->id,
                    'listType' => !empty($params['listType']) ? $params['listType'] : 'latest',
                    'extend' => $_GET
                )
            )
        );
    }

    /**
     * Photo list action
     *
     * @param array $params
     * @throws AuthorizationException
     */
    public function viewList( array $params )
    {
        $listType = isset($params['listType']) ? $params['listType'] : 'latest';
        
        $event = new BASE_CLASS_EventCollector('photo.collectPhotoList');
        PEEP::getEventManager()->trigger($event);
        
        $validLists = array_merge($event->getData(), array('featured', 'latest', 'toprated', 'tagged', 'most_discussed'));

        if ( !in_array($listType, $validLists) )
        {
            $this->redirect(PEEP::getRouter()->urlForRoute('view_photo_list', array('listType' => 'latest')));
        }
       
        $this->assign('listType', $listType);
        
        $language = PEEP::getLanguage();
        
        foreach ( $validLists as $type )
        {
            $language->addKeyForJs('photo', 'meta_title_photo_' . $type);
        }
        
        PEEP::getDocument()->setTitle($language->text('photo', 'meta_title_photo_' . $listType));
        PEEP::getDocument()->setDescription($language->text('photo', 'meta_description_photo_' . $listType));
    }

    /**
     * Tagged photo list action
     *
     * @param array $params
     * @throws AuthorizationException
     */
    public function viewTaggedList( array $params = null )
    {
        if ( !empty($params['tag']) )
        {
            $tag = htmlspecialchars(urldecode($params['tag']));
            $this->assign('tag', $tag);
            PEEP::getDocument()->setTitle(PEEP::getLanguage()->text('photo', 'meta_title_photo_tagged_as', array('tag' => $tag)));
            PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('photo', 'meta_description_photo_tagged_as', array('tag' => $tag)));
            
            $this->getComponent('pageHead')->getComponent('subMenu')->assign('tag', $tag);
        }
        else
        {
            $this->assign('tag', '');
        }

        PEEP::getDocument()->setHeading(PEEP::getLanguage()->text('photo', 'page_title_browse_photos'));
        PEEP::getDocument()->setHeadingIconClass('peep_ic_picture');
    }

    /**
     * Controller action for user albums list
     *
     * @param array $params
     * @throws AuthorizationException
     * @throws Redirect404Exception
     */
    public function userAlbums( array $params )
    {
        if ( empty($params['user']) || !mb_strlen($username = trim($params['user'])) )
        {
            throw new Redirect404Exception();
        }
        
        if ( ($user = BOL_UserService::getInstance()->findByUsername($username)) === NULL )
        {
            throw new Redirect404Exception();
        }
        
        $this->assign('userId', $user->id);
    }

    /**
     * Controller action for user album
     *
     * @param array $params
     * @throws AuthorizationException
     * @throws Redirect404Exception
     */
    public function userAlbum( array $params )
    {
        if (
            empty($params['user']) || ($userDto = BOL_UserService::getInstance()->findByUsername($params['user'])) === null ||
            empty($params['album']) || ($album = $this->photoAlbumService->findAlbumById($params['album'])) === null
        )
        {
            throw new Redirect404Exception();
        }

        PEEP::getEventManager()->trigger(new PEEP_Event('photo.user_album_view', array(
            'album' => $album
        )));

        PEEP::getDocument()->setTitle(
            PEEP::getLanguage()->text('photo', 'meta_title_photo_useralbum', array(
                'displayName' => BOL_UserService::getInstance()->getDisplayName($userDto->id),
                'albumName' => $album->name
            ))
        );

        $isOwner = $album->userId == PEEP::getUser()->getId();
        $isModerator = PEEP::getUser()->isAuthorized('photo');
        
        $this->assign('isModerator', $isModerator);
        $this->assign('isOwner', $isOwner);
        $this->assign('album', $album);

        $coverEvent = PEEP::getEventManager()->trigger(
            new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_GET_ALBUM_COVER_URL, array('albumId' => $album->id))
        );
        $coverData = $coverEvent->getData();
        
        $this->assign('coverUrl', $coverData['coverUrl']);
        $this->assign('coverUrlOrig', $coverData['coverUrlOrig']);
        
        if ( $isOwner || $isModerator )
        {
            $form = new PHOTO_CLASS_AlbumEditForm($album->id);
            $this->addForm($form);
            $this->assign('extendInputs', $form->getExtendedElements());

            $exclude = array($album->id);
            $cnewsAlbum = PHOTO_BOL_PhotoAlbumService::getInstance()->getCnewsAlbum($album->userId);
            
            if ( !empty($cnewsAlbum) )
            {
                $exclude[] = $cnewsAlbum->id;
            }

            $albumNameList = $this->photoAlbumService->findAlbumNameListByUserId($userDto->id, $exclude);
            $this->assign('albumNameList', $albumNameList);
            
            PEEP::getDocument()->addScriptDeclarationBeforeIncludes(
                UTIL_JsGenerator::composeJsString(';window.albumParams = Object.freeze({$params});', array(
                    'params' => array(
                        'url' => PEEP::getRouter()->urlFor('PHOTO_CTRL_Photo', 'ajaxResponder'),
                        'album' => $album,
                        'isClassic' => (bool)PEEP::getConfig()->getValue('photo', 'photo_list_view_classic'),
                        'albumNameList' => array_values($albumNameList)
                    )
                ))
            );

            PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('photo')->getStaticJsUrl() . 'album.js');
            PEEP::getDocument()->addOnloadScript(';window.photoAlbum.init();');
            
            $lang = PEEP::getLanguage();
            
            $lang->addKeyForJs('photo', 'move_to_new_album');
            $lang->addKeyForJs('photo', 'no_photo_selected');
            $lang->addKeyForJs('photo', 'crop_photo_title');
            $lang->addKeyForJs('photo', 'set_as_cover_label');
            $lang->addKeyForJs('photo', 'are_you_sure');
            $lang->addKeyForJs('photo', 'confirm_delete_photos');
            $lang->addKeyForJs('photo', 'photo_deleted');
            $lang->addKeyForJs('photo', 'photos_deleted');
            $lang->addKeyForJs('photo', 'photo_album_updated');
            $lang->addKeyForJs('photo', 'to_small_cover_img');
            $lang->addKeyForJs('photo', 'album_name_error');
            $lang->addKeyForJs('photo', 'cnews_album');
            $lang->addKeyForJs('photo', 'photo_success_moved');
        }
    }

    public function reloadAlbumCover( $params )
    {
        if ( empty($params['albumId']) || ($album = $this->photoAlbumService->findAlbumById($params['albumId'])) === null )
        {
            return array();
        }

        $coverEvent = PEEP::getEventManager()->trigger(
            new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_GET_ALBUM_COVER_URL, array('albumId' => $album->id))
        );

        return $coverEvent->getData();
    }
    
    public function userPhotos( array $params )
    {
        if ( empty($params['user']) )
        {
            throw new Redirect404Exception();
        }
        
        $userDto = BOL_UserDao::getInstance()->findByUserName(trim($params['user']));

        if ( $userDto === NULL )
        {
            throw new Redirect404Exception();
        }
        
        $this->assign('userId', $userDto->id);
    }

    public function downloadPhoto( $params )
    {
        if ( ($photo = PHOTO_BOL_PhotoDao::getInstance()->findById($params['id'])) === NULL )
        {
            exit();
        }

        $event = new PEEP_Event('photo.onDownloadPhoto', array('id' => $photo->id));
        PEEP::getEventManager()->trigger($event);
        $data = $event->getData();

        if ( $data !== null )
        {
            $path = $data;
        }
        else
        {
            $path = PHOTO_BOL_PhotoService::getInstance()->getPhotoPath($photo->id, $photo->hash, 'original');
        }
        
        if ( ini_get('zlib.output_compression') )
        {
            ini_set('zlib.output_compression', 'Off');
        }

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: image/jpg');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . basename($path) . '";');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($path));
        ob_end_clean();
        readfile($path);
        exit();
    }

    public function ajaxResponder()
    {
        if ( isset($_POST['ajaxFunc']) && PEEP::getRequest()->isAjax() )
        {
            $callFunc = (string)$_POST['ajaxFunc'];

            $result = call_user_func(array($this, $callFunc), $_POST);
        }
        else
        {
            throw new Redirect404Exception();
        }

        if ( !PEEP_DEBUG_MODE )
        {
            ob_end_clean();
        }

        $event = new PEEP_Event('photo.onReadyResponse', $_POST, $result);
        PEEP::getEventManager()->trigger($event);
        $result = $event->getData();

        $document = PEEP::getDocument();

        $result['scripts'] = array(
            'beforeIncludes' => $document->getScriptBeforeIncludes(),
            'scriptFiles' => $document->getScripts(),
            'onloadScript' => $document->getOnloadScript(),
            'styleDeclarations' => $document->getStyleDeclarations(),
            'styleSheets' => $document->getStyleSheets()
        );

        header('Content-Type: application/json');
        exit(json_encode($result));
    }
    
    public function getAlbumList( $params )
    {
        if ( empty($params['userId']) || !BOL_UserService::getInstance()->findUserById($params['userId']) )
        {
            exit(json_encode(array('status' => 'error', 'msg' => PEEP::getLanguage()->text('base', 'deleted_user'))));
        }
        
        $page = !empty($params['offset']) ? abs((int)$params['offset']) : 1;
        $idList = !empty($params['idList']) ? $params['idList'] : array();
        $photosPerPage = (int)PEEP::getConfig()->getValue('photo', 'photos_per_page');

        $albums = $this->photoAlbumService->getUserAlbumList($params['userId'], $page, $photosPerPage, $idList);
        
        $albumUrlList = array();
        
        if ( $albums )
        {
            $username = BOL_UserService::getInstance()->getUserName($params['userId']);
            
            foreach ( $albums as $key => $album )
            {
                $albums[$key]['name'] = UTIL_HtmlTag::autoLink($album['name']);
                $albums[$key]['count'] = $this->photoAlbumService->countAlbumPhotos($album['id']);
                $albums[$key]['albumUrl'] = PEEP::getRouter()->urlForRoute('photo_user_album', array('user' => $username, 'album' => $album['id']));
            }
        }
        
        return array('status' => 'success', 'data' => array(
            'photoList' => $albums,
            'photoUrlList' => $albumUrlList
        ));
    }
    
    public function getPhotoList( $params )
    {
        $listType = $params['listType'];
        $page = !empty($params['offset']) ? abs((int)$params['offset']) : 1;
        $idList = !empty($params['idList']) ? $params['idList'] : array();
        $photosPerPage = (int)PEEP::getConfig()->getValue('photo', 'photos_per_page');

        switch ( $listType )
        {
            case 'albumPhotos':
                $photos = $this->photoService->findPhotoListByAlbumId($params['albumId'], $page, $photosPerPage, $idList);
                break;
            case 'userPhotos':
                $photos = $this->photoService->findPhotoListByUserId($params['userId'], $page, $photosPerPage, $idList);
                break;
            case 'tag':
                $tags = BOL_TagDao::getInstance()->findTagsByLabel(array(ltrim($params['searchVal'], '#')));
                
                if ( empty($tags) )
                {
                    $photos = array();
                    
                    break;
                }
                
                $params['id'] = $tags[0]->id;
            case 'hash':
                $photos = $this->photoService->findTaggedPhotosByTagId($params['id'], $page, $photosPerPage);
                break;
            case 'user':
                $photos = $this->photoService->findPhotoListByUserId($params['id'], $page, $photosPerPage);
                break;
            case 'desc':
                $photos = $this->photoService->findPhotoListByDesc($params['searchVal'], $params['id'], $page, $photosPerPage);
                break;
            case 'latest':
            case 'featured':
            case 'toprated':
            case 'most_discussed':
                $photos = $this->photoService->findPhotoList($listType, $page, $photosPerPage);
                break;
            default:
                $event = new PEEP_Event('photo.getPhotosByListType', array('listType' => $listType, 'page' => $page, 'photosPerPage' => $photosPerPage), array());
                PEEP::getEventManager()->trigger($event);
                $photos = $event->getData();
                break;
        }
        
        return $this->generatePhotoList($photos);
    }

    public function getPhotoInfo( $params )
    {
        $albumId = isset($params['albumId']) ? (int)$params['albumId'] : null;
        $photos = (isset($params['photos']) && is_array($params['photos'])) ? $params['photos'] : array();

        $list = $this->photoService->findPhotosInAlbum($albumId, $photos);

        return $this->generatePhotoList($list);
    }
    
    public function generatePhotoList( $photos )
    {
        $userIds = $userUrlList = $albumIdList = $albumUrlList = $displayNameList = $albumNameList = $entityIdList = array();

        $unique = uniqid(time(), true);

        if ( $photos )
        {
            foreach ( $photos as $key => $photo )
            {
                $userIds[] = $photo['userId'];
                $albumIdList[] = $photo['albumId'];
                $entityIdList[] = $photo['id'];
                
                $photos[$key]['description'] = UTIL_HtmlTag::autoLink($photos[$key]['description']);
                $photos[$key]['unique'] = $unique;
            }

            $displayNameList = BOL_UserService::getInstance()->getDisplayNamesForList($userIds);
            
            foreach ( ($usernameList = BOL_UserService::getInstance()->getUserNamesForList($userIds)) as $id => $username )
            {
                $userUrlList[$id] = BOL_UserService::getInstance()->getUserUrlForUsername($username);
            }
            
            foreach ( ($albumNameList = $this->photoAlbumService->findAlbumNameListByIdList($albumIdList)) as $id => $album )
            {
                $albumUrlList[$id] = PEEP::getRouter()->urlForRoute('photo_user_album', array('user' => $usernameList[$album['userId']], 'album' => $id));
            }
        }
        
        return array('status' => 'success', 'data' => array(
            'photoList' => $photos, 
            'displayNameList' => $displayNameList,
            'userUrlList' => $userUrlList,
            'albumNameList' => $albumNameList,
            'albumUrlList' => $albumUrlList,
            'rateInfo' => BOL_RateService::getInstance()->findRateInfoForEntityList('photo_rates', $entityIdList),
            'userScore' => BOL_RateService::getInstance()->findUserSocre(PEEP::getUser()->getId(), 'photo_rates', $entityIdList),
            'commentCount' => BOL_CommentService::getInstance()->findCommentCountForEntityList('photo_comments', $entityIdList),
            'unique' => $unique
        ));
    }
    
    public function ajaxCropPhoto( array $params = array() )
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            exit();
        }
        
        $form = new PHOTO_CLASS_MakeAlbumCover();
        
        if ( $form->isValid($_POST) )
        {
            if ( ($album = $this->photoAlbumService->findAlbumById($form->getElement('albumId')->getValue())) === NULL )
            {
                exit(json_encode(array('result' => FALSE)));
            }

            if ( ($urls = $this->photoAlbumService->cropAlbumCover($album, $_POST['coords'], $_POST['view_size'], !empty($_POST['photoId']) ? $_POST['photoId'] : 0)) !== FALSE )
            {
                exit(json_encode(array('result' => TRUE, 'url' => $urls['cover'], 'urlOrig' => $urls['coverOrig'])));
            }
            
            exit(json_encode(array('result' => FALSE)));
        }
        else
        {
            exit(json_encode(array('result' => FALSE)));
        }
    }
    
    public function ajaxDeletePhotoAlbum( array $params )
    {
        $albumId = $params['entityId'];
        $lang = PEEP::getLanguage();

        $album = $this->photoAlbumService->findAlbumById($albumId);

        if ( $album )
        {
            if ( strcasecmp($album->name, PEEP::getLanguage()->text('photo', 'cnews_album')) === 0 )
            {
                return array('result' => FALSE, 'msg' => PEEP::getLanguage()->text('photo', 'delete_cnews_album_error'));
            }
            
            $canEdit = $album->userId == PEEP::getUser()->getId();
            $canModerate = PEEP::getUser()->isAuthorized('photo');

            $authorized = $canEdit || $canModerate;

            if ( $authorized )
            {
                $delResult = $this->photoAlbumService->deleteAlbum($albumId);

                if ( $delResult )
                {
                    $url = PEEP_Router::getInstance()->urlForRoute(
                        'photo_user_albums',
                        array('user' => BOL_UserService::getInstance()->getUserName($album->userId))
                    );

                    return array('result' => TRUE, 'msg' => $lang->text('photo', 'album_deleted'), 'url' => $url);
                }
            }
            else
            {
                $url = PEEP_Router::getInstance()->urlForRoute(
                    'photo_user_album',
                    array('user' => BOL_UserService::getInstance()->getUserName($album->userId), 'album' => $album->id)
                );

                return array('result' => FALSE, 'msg' => $lang->text('photo', 'album_delete_not_allowed'), 'url' => $url);
            }
        }

        return array('result' => FALSE);
    }
    
    public function ajaxDeletePhoto( array $params )
    {
        $return = array();
        
        if ( !empty($params['entityId']) && ($photo = $this->photoService->findPhotoById($params['entityId'])) !== NULL )
        {
            $ownerId = $this->photoService->findPhotoOwner($photo->id);
            $isOwner = $ownerId == PEEP::getUser()->getId();
            $isModerator = PEEP::getUser()->isAuthorized('photo');

            if ( !$isOwner && !$isModerator )
            {
                throw new Redirect404Exception();
            }

            if ( $this->photoService->deletePhoto($photo->id) )
            {
                $cover = PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->findByAlbumId($photo->albumId);
        
                if ( $cover === NULL || (int)$cover->auto )
                {
                    PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->deleteCoverByAlbumId($photo->albumId);

                    $this->photoService->createAlbumCover($photo->albumId, array_reverse(PHOTO_BOL_PhotoDao::getInstance()->getAlbumAllPhotos($photo->albumId)));
                }
        
                $url = PEEP_Router::getInstance()->urlForRoute(
                    'photo_user_albums',
                    array('user' => BOL_UserService::getInstance()->getUserName($ownerId))
                );
                $return = array(
                    'result' => TRUE,
                    'msg' => PEEP::getLanguage()->text('photo', 'photo_deleted'),
                    'url' => $url,
                    'coverUrl' => PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverUrlByAlbumId($photo->albumId),
                    'isHasCover' => PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->isAlbumCoverExist($photo->albumId)
                );
            }
            else
            {
                $return = array('result' => FALSE, 'error' => PEEP::getLanguage()->text('photo', 'photo_not_deleted'));
            }
        }

        return $return;
    }
    
    public function ajaxDeletePhotos( $params )
    {
        if ( !empty($params['albumId']) && !empty($params['photoIdList']) && ($album = $this->photoAlbumService->findAlbumById($params['albumId'])) !== NULL && ($album->userId == PEEP::getUser()->getId() || PEEP::getUser()->isAuthorized('photo')) )
        {
            $photoIdList = array_unique($params['photoIdList']);
        
            PEEP::getEventManager()->trigger(
                new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_BEFORE_MULTIPLE_PHOTO_DELETE,
                    array(
                        'albumId' => $album->id,
                        'photoIdList' => $photoIdList
                    )
                )
            );

            $photoList = PHOTO_BOL_PhotoDao::getInstance()->findByIdList($photoIdList);

            foreach ( $photoList as $photo )
            {
                if ( $photo->albumId != $album->id )
                {
                    continue;
                }

                $this->photoService->deletePhoto($photo->id, TRUE);
            }

            $cover = PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->findByAlbumId($album->id);

            if ( $cover === NULL || (int)$cover->auto )
            {
                PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->deleteCoverByAlbumId($album->id);

                $this->photoService->createAlbumCover($album->id, array_reverse(PHOTO_BOL_PhotoDao::getInstance()->getAlbumAllPhotos($album->id)));
            }

            return array(
                'result' => TRUE,
                'coverUrl' => PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverUrlByAlbumId($album->id),
                'isHasCover' => PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->isAlbumCoverExist($album->id)
            );
        }
        
        return array('result' => FALSE);
    }
    
    public function ajaxMoveToAlbum( $params )
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            exit(json_encode(array('result' => FALSE)));
        }

        $form = new PHOTO_CLASS_AlbumAddForm();
        
        if ( $form->isValid($params) && $form->process() )
        {
            $values = $form->getValues();
        
            $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($values['from-album']);
            $userDto =  BOL_UserService::getInstance()->findUserById($album->userId);

            $exclude = array($album->id);
            $cnewsAlbum = PHOTO_BOL_PhotoAlbumService::getInstance()->getCnewsAlbum($album->userId);

            if ( !empty($cnewsAlbum) )
            {
                $exclude[] = $cnewsAlbum->id;
            }

            return array(
                'result' => TRUE,
                'albumNameList' => $this->photoAlbumService->findAlbumNameListByUserId($userDto->id, $exclude),
                'coverUrl' => PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverUrlByAlbumId($album->id),
                'isHasCover' => PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->isAlbumCoverExist($album->id)
            );
        }
        else
        {
            $result = array('result' => FALSE);
            $errors = array_filter($form->getErrors(), 'count');
            
            if ( !empty($errors[key($errors)][0]) )
            {
                $result['msg'] = $errors[key($errors)][0];
            }
            
            return $result;
        }
    }
    
    public function getSearchResult( array $params = array() )
    {
        if ( strlen($searchVal = trim($_POST['searchVal'])) === 0 )
        {
            return array('result' => TRUE, 'list' => array());
        }
        
        $result = array_merge(array('result' => TRUE, 'list' => array()), PHOTO_BOL_SearchService::getInstance()->getSearchResult($searchVal));
        
        return $result;
    }
    
    public function getSearchAllResult( $params = array() )
    {
        if ( strlen($searchVal = trim($_POST['searchVal'])) === 0 )
        {
            return array('result' => TRUE, 'list' => array());
        }
        
        $page = !empty($params['offset']) ? abs((int)$params['offset']) : 1;
        $photosPerPage = (int)PEEP::getConfig()->getValue('photo', 'photos_per_page');
        
        $list = PHOTO_BOL_SearchService::getInstance()->getSearchAllResult($searchVal);
        $photos = $this->photoService->findPhotoListByIdList($list['ids'], $page, $photosPerPage);
        
        return $this->generatePhotoList($photos);
    }

    /**
     * Set photo approval status (approved | blocked)
     *
     * @param array $params
     * @throws Redirect404Exception
     * @return array
     */
    public function ajaxSetApprovalStatus( array $params )
    {
        $photoId = $params['photoId'];
        $status = $params['status'];

        $isModerator = PEEP::getUser()->isAuthorized('photo');

        if ( !$isModerator )
        {
            throw new Redirect404Exception();
        }

        $setStatus = $this->photoService->updatePhotoStatus($photoId, $status);

        if ( $setStatus )
        {
            $return = array('result' => true, 'msg' => PEEP::getLanguage()->text('photo', 'status_changed'));
        }
        else
        {
            $return = array('result' => false, 'error' => PEEP::getLanguage()->text('photo', 'status_not_changed'));
        }

        return $return;
    }
    
    public function ajaxUpdateAlbum()
    {
        if ( !PEEP::getRequest()->isAjax() || empty($_POST['album-id']) )
        {
            exit(json_encode(array('result' => false)));
        }

        $form = new PHOTO_CLASS_AlbumEditForm($_POST['album-id']);
        
        if ( !$form->isValid($_POST) )
        {
            exit(json_encode(array('result' => false)));
        }
        
        $values = $form->getValues();
        $albumId = (int)$values['album-id'];
        $album = $this->photoAlbumService->findAlbumById($albumId);

        if ( $album )
        {
            if ( $album->name != trim(PEEP::getLanguage()->text('photo', 'cnews_album')) )
            {
                $album->name = htmlspecialchars(trim($values['albumName']));
            }

            $album->description = htmlspecialchars(trim($values['desc']));

            if ( !empty($values['privacy']) && PEEP::getPluginManager()->isPluginActive('privacy') )
            {
                $album->privacy = in_array($values['privacy'], array('everybody', 'friends_only', 'only_for_me')) ? $values['privacy'] : 'everybody';
            }

            if ( $this->photoAlbumService->updateAlbum($album) )
            {
                $form->triggerComplete(array('album' => $album));

                exit(json_encode(array('result' => true, 'id' => $album->id)));
            }
        }

        exit(json_encode(array('result' => true)));
    }
    
    public function ajaxUpdatePhoto()
    {
        if ( !PEEP::getRequest()->isAjax() || empty($_POST['photoId']) )
        {
            exit();
        }
        
        $photoId = $_POST['photoId'];
        
        if ( $this->photoService->findPhotoOwner($photoId) != PEEP::getUser()->getId() && !PEEP::getUser()->isAuthorized('photo', 'upload') )
        {
            exit(json_encode(array(
                'result' => FALSE,
                'msg' => PEEP::getLanguage()->text('photo', 'auth_edit_permissions')
            )));
        }
        
        $form = new PHOTO_CLASS_EditForm($photoId); 

        if ( $form->isValid($_POST) )
        {
            $values = $form->getValues();

            $userId = PEEP::getUser()->getId();
            $photo = $this->photoService->findPhotoById($values['photoId']);
            $album = $this->photoAlbumService->findAlbumById($photo->albumId);
            $isNewAlbum = FALSE;
            
            if ( ($albumName = htmlspecialchars(trim($values['album-name']))) != $album->name )
            {
                if ( ($album = $this->photoAlbumService->findAlbumByName($albumName, $userId)) === NULL )
                {
                    $album = new PHOTO_BOL_PhotoAlbum();
                    $album->name = $albumName;
                    $album->userId = $userId;
                    $album->entityId = $userId;
                    $album->entityType = 'user';
                    $album->createDatetime = time();
                    $album->description = !empty($values['description']) ? htmlspecialchars(trim($values['description'])) : '';

                    $this->photoAlbumService->addAlbum($album);
                    $isNewAlbum = TRUE;
                }
            }

            if ( $photo->albumId != $album->id )
            {
                PEEP::getEventManager()->trigger(
                    new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_BEFORE_PHOTO_MOVE,
                        array(
                            'fromAlbum' => $photo->albumId,
                            'toAlbum' => $album->id,
                            'photoIdList' => array($photo->id)
                        )
                    )
                );
                
                if ( $this->photoService->movePhotosToAlbum(array($photo->id), $album->id, $isNewAlbum) )
                {
                    PEEP::getEventManager()->trigger(
                        new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_AFTER_PHOTO_MOVE,
                            array(
                                'fromAlbum' => $photo->albumId,
                                'toAlbum' => $album->id,
                                'photoIdList' => array($photo->id)
                            )
                        )
                    );
                }
            }

            $description = htmlspecialchars(trim($values['photo-desc']));

            if ( $photo->description != $description )
            {
                $photo->description = $description;
                $this->photoService->updatePhoto($photo);

                BOL_EntityTagDao::getInstance()->deleteItemsForEntityItem($photo->id, 'photo');
                BOL_TagService::getInstance()->updateEntityTags($photo->id, 'photo', $this->photoService->descToHashtag($photo->description));
                PHOTO_BOL_SearchService::getInstance()->deleteSearchItem(PHOTO_BOL_SearchService::ENTITY_TYPE_PHOTO, $photo->id);
                PHOTO_BOL_SearchService::getInstance()->addSearchIndex(PHOTO_BOL_SearchService::ENTITY_TYPE_PHOTO, $photo->id, $photo->description);
            }

            $newPhoto = $this->photoService->findPhotoById($photo->id);

            exit(json_encode(array(
                'result' => true,
                'id' => $photo->id,
                'description' => $photo->description,
                'albumName' => $album->name,
                'albumUrl' => PEEP::getRouter()->urlForRoute('photo_user_album', array('user' => PEEP::getUser()->getUserObject()->getUsername(), 'album' => $album->id)),
                'msg' => PEEP::getLanguage()->text('photo', 'photo_updated'),
                'msgApproval' => PEEP::getLanguage()->text('photo', 'photo_uploaded_pending_approval'),
                'photo' => get_object_vars($newPhoto)
            )));
        }
        else
        {
            $result = array('result' => FALSE);
            $errors = array_filter($form->getErrors(), 'count');
            
            if ( !empty($errors[key($errors)][0]) )
            {
                $result['msg'] = $errors[key($errors)][0];
            }
            
            exit(json_encode($result));
        }
    }

    /**
     * Set photo's 'is featured' status
     *
     * @param array $params
     * @throws Redirect404Exception
     * @return array
     */
    public function ajaxSetFeaturedStatus( array $params )
    {
        $photoId = $params['entityId'];
        $status = $params['status'];

        $isModerator = PEEP::getUser()->isAuthorized('photo');

        if ( !$isModerator )
        {
            throw new Redirect404Exception();
        }

        $setResult = $this->photoService->updatePhotoFeaturedStatus($photoId, $status);

        if ( $setResult )
        {
            $return = array('result' => true, 'msg' => PEEP::getLanguage()->text('photo', 'status_changed'));
        }
        else
        {
            $return = array('result' => false, 'error' => PEEP::getLanguage()->text('photo', 'status_not_changed'));
        }

        return $return;
    }
    
    public function ajaxRate( $params )
    {
        if ( empty($params['entityId']) || empty($params['rate']) || empty($params['ownerId']) )
        {
            return array('result' => FALSE, 'error' => 'Invalid request');
        }

        $entityId = (int)$params['entityId'];
        $rate = (int)$params['rate'];
        $ownerId = (int)$params['ownerId'];
        $userId = PEEP::getUser()->getId();

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            return array('result' => FALSE, 'error' => PEEP::getLanguage()->text('base', 'rate_cmp_auth_error_message'));
        }

        if ( $userId === $ownerId )
        {
            return array('result' => FALSE, 'error' => PEEP::getLanguage()->text('base', 'rate_cmp_owner_cant_rate_error_message'));
        }

        if ( BOL_UserService::getInstance()->isBlocked(PEEP::getUser()->getId(), $ownerId) )
        {
            return array('result' => FALSE, 'error' => PEEP::getLanguage()->text('base', 'user_block_message'));
        }

        $event = PEEP::getEventManager()->trigger(
            new PEEP_Event('photo.onRate', array(
                'photoId' => $entityId,
                'rate' => $rate,
                'ownerId' => $ownerId,
                'userId' => $userId
            ))
        );

        if ( $event->getData() !== null )
        {
            return $event->getData();
        }

        $service = BOL_RateService::getInstance();
        
        if ( ($rateItem = $service->findRate($entityId, 'photo_rates', $userId)) === NULL )
        {
            $rateItem = new BOL_Rate();
            $rateItem->setEntityId($entityId)
                    ->setEntityType('photo_rates')
                    ->setUserId($userId)
                    ->setActive(TRUE);
        }

        $rateItem->setScore($rate)->setTimeStamp(time());
        $service->saveRate($rateItem);
        
        return array(
            'result' => TRUE,
            'rateInfo' => $service->findRateInfoForEntityItem($entityId, 'photo_rates'),
            'msg' => PEEP::getLanguage()->text('base', 'rate_cmp_success_message')
        );
    }

    public function getFloatbox( $params )
    {
        if ( empty($params['photoId']) || !$params['photoId'] )
        {
            throw new Redirect404Exception();
        }
        
        $photoId = (int)$params['photoId'];
        
        if ( ($photo = $this->photoService->findPhotoById($photoId)) === NULL )
        {
            return array('result' => 'error');
        }
        
        $event = new BASE_CLASS_EventCollector('photo.collectPhotoList');
        PEEP::getEventManager()->trigger($event);
        $data = array();
        $listTypes = array(
            'list' => array_merge($event->getData(), array('latest', 'toprated', 'albumPhotos', 'userPhotos', 'featured', 'entityPhotos', 'most_discussed')),
            'search' => array('hash', 'user', 'desc', 'all')
        );
        
        if ( array_search($params['listType'], $listTypes['list']) === FALSE &&
            array_search($params['listType'], $listTypes['search']) === FALSE )
        {
            $listType = 'latest';
        }
        else
        {
            $listType = $params['listType'];
        }
        
        switch ( $listType )
        {
            case 'hash':
            case 'user':
            case 'desc':
            case 'all':
                $data['id'] = @$params['id'];
                $data['searchVal'] = $params['searchVal'];
                $listService = PHOTO_BOL_SearchService::getInstance();
                break;
            default:
                $event = new PEEP_Event('photo.getPhotoListService', array('listType' => $listType));
                PEEP::getEventManager()->trigger($event);
                $service = $event->getData();
                
                if ( !empty($service) )
                {
                    $listService = $service;
                }
                else
                {
                    $listService = $this->photoService;
                }
                break;
        }

        $userId = PEEP::getUser()->getId();
        $resp = array('result' => true, 'photos' => array());

        if ( !empty($params['photos']) )
        {
            foreach ( array_unique($params['photos']) as $photoId )
            {
                $resp['photos'][$photoId] = $this->prepareMarkup($photoId, $params['layout']);
                $resp['photos'][$photoId]['ownerId'] = $this->photoService->findPhotoOwner($photoId);

                $rateInfo = BOL_RateService::getInstance()->findRateInfoForEntityList('photo_rates', array($photoId));
                $userScore = BOL_RateService::getInstance()->findUserSocre($userId, 'photo_rates', array($photoId));
                $resp['photos'][$photoId]['rateInfo'] = $rateInfo[$photoId];
                $resp['photos'][$photoId]['userScore'] = $userScore[$photoId];
            }
        }
        
        if ( !empty($params['loadPrevList']) || !empty($params['loadPrevPhoto']) )
        {
            $resp['prevList'] = $prevIdList = $listService->getPrevPhotoIdList($listType, $photo->id, $data);
            
            if ( count($prevIdList) < PHOTO_BOL_PhotoService::ID_LIST_LIMIT )
            {
                $resp['prevCompleted'] = TRUE;
                $resp['firstList'] = $firstIdList = $listService->getFirstPhotoIdList($listType, $photo->id, $data);
            }
            
            if ( !empty($params['loadPrevPhoto']) )
            {
                $prevId = !empty($prevIdList) ? min($prevIdList) : (!empty($firstIdList) ? min($firstIdList) : null);
                
                if ( $prevId && !isset($resp['photos'][$prevId]) )
                {
                    $resp['photos'][$prevId] = $this->prepareMarkup($prevId, $params['layout']);
                    $resp['photos'][$prevId]['ownerId'] = $this->photoService->findPhotoOwner($prevId);

                    $rateInfo = BOL_RateService::getInstance()->findRateInfoForEntityList('photo_rates', array($prevId));
                    $userScore = BOL_RateService::getInstance()->findUserSocre($userId, 'photo_rates', array($prevId));
                    $resp['photos'][$prevId]['rateInfo'] = $rateInfo[$prevId];
                    $resp['photos'][$prevId]['userScore'] = $userScore[$prevId];
                }
            }
        }
        
        if ( !empty($params['loadNextList']) || !empty($params['loadNextPhoto']) )
        {
            $resp['nextList'] = $nextIdList = $listService->getNextPhotoIdList($listType, $photo->id, $data);
            
            if ( count($nextIdList) < PHOTO_BOL_PhotoService::ID_LIST_LIMIT )
            {
                $resp['nextCompleted'] = TRUE;
                $resp['lastList'] = $lastIdList = $listService->getLastPhotoIdList($listType, $photo->id, $data);
            }
            
            if ( !empty($params['loadNextPhoto']) )
            {
                $nextId = !empty($nextIdList) ? max($nextIdList) : (!empty($lastIdList) ? max($lastIdList) : null);
                
                if ( $nextId && !isset($resp['photos'][$nextId]) )
                {
                    $resp['photos'][$nextId] = $this->prepareMarkup($nextId, $params['layout']);
                    $resp['photos'][$nextId]['ownerId'] = $this->photoService->findPhotoOwner($nextId);

                    $rateInfo = BOL_RateService::getInstance()->findRateInfoForEntityList('photo_rates', array($nextId));
                    $userScore = BOL_RateService::getInstance()->findUserSocre($userId, 'photo_rates', array($nextId));
                    $resp['photos'][$nextId]['rateInfo'] = $rateInfo[$nextId];
                    $resp['photos'][$nextId]['userScore'] = $userScore[$nextId];
                }
            }
        }
        
        return $resp;
    }
    
    private function prepareMarkup( $photoId, $layout = NULL )
    {
        $markup = array();
        $photo = $this->photoService->findPhotoById($photoId);
        $album = $this->photoAlbumService->findAlbumById($photo->albumId);
        $layoutList = array(
            'page' => BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST,
            'floatbox' => BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI
        );
        $userId = PEEP::getUser()->getId();
        $ownerMode = $album->userId == $userId;
        $modPermissions = PEEP::getUser()->isAuthorized('photo');
        
        $photo->addDatetime = UTIL_DateTime::formatDate($photo->addDatetime);
        $photo->description = UTIL_HtmlTag::autoLink($photo->description);
        $dim = !empty($photo->dimension) ? $photo->dimension : FALSE;
        $photo->url = $this->photoService->getPhotoUrlByPhotoInfo($photo->id, PHOTO_BOL_PhotoService::TYPE_MAIN, get_object_vars($photo));
        
        if ( $photo->hasFullsize )
        {
            $photo->urlFullscreen = $this->photoService->getPhotoUrlByPhotoInfo($photo->id, PHOTO_BOL_PhotoService::TYPE_FULLSCREEN, get_object_vars($photo));
        }
        
        if ( !empty($photo->description) )
        {
            $photo->description = $this->photoService->hashtagToDesc($photo->description);
        }
        
        $markup['photo'] = $photo;
        $markup['album'] = $album;
        $markup['albumUrl'] = PEEP::getRouter()->urlForRoute('photo_user_album', array('user' => BOL_UserService::getInstance()->getUserName($album->userId), 'album' => $album->id));

        $markup['photoCount'] = $this->photoAlbumService->countAlbumPhotos($photo->albumId);
        $markup['photoIndex'] = $this->photoService->getPhotoIndex($photo->albumId, $photo->id);

        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($album->userId), TRUE, TRUE, TRUE, FALSE);
        $markup['avatar'] = $avatar[$album->userId];

        $cmtParams = new BASE_CommentsParams('photo', 'photo_comments');
        $cmtParams->setEntityId($photo->id);
        $cmtParams->setOwnerId($album->userId);
        $cmtParams->setWrapInBox(FALSE);
        $cmtParams->setDisplayType(array_key_exists($layout, $layoutList) ? $layoutList[$layout] : BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI);
        $cmtParams->setInitialCommentsCount(3);
        $cmtParams->setAddComment($photo->status == PHOTO_BOL_PhotoDao::STATUS_APPROVED);
        
        $customId = uniqid('photoComment');
        $cmtParams->setCustomId($customId);
        $markup['customId'] = $customId;
        
        $comment = new BASE_CMP_Comments($cmtParams);
        $markup['comment'] = $comment->render();
        
        $action = new BASE_ContextAction();
        $action->setKey('photo-moderate');

        $context = new BASE_CMP_ContextAction();
        $context->addAction($action);

        $contextEvent = new BASE_CLASS_EventCollector('photo.collect_photo_context_actions', array(
            'photoId' => $photo->id,
            'photoDto' => $photo
        ));

        PEEP::getEventManager()->trigger($contextEvent);

        foreach ( $contextEvent->getData() as $contextAction )
        {
            $action = new BASE_ContextAction();
            $action->setKey(empty($contextAction['key']) ? uniqid() : $contextAction['key']);
            $action->setParentKey('photo-moderate');
            $action->setLabel($contextAction['label']);

            if ( !empty($contextAction['id']) )
            {
                $action->setId($contextAction['id']);
            }

            if ( !empty($contextAction['order']) )
            {
                $action->setOrder($contextAction['order']);
            }

            if ( !empty($contextAction['class']) )
            {
                $action->setClass($contextAction['class']);
            }

            if ( !empty($contextAction['url']) )
            {
                $action->setUrl($contextAction['url']);
            }

            $attributes = empty($contextAction['attributes']) ? array() : $contextAction['attributes'];
            foreach ( $attributes as $key => $value )
            {
                $action->addAttribute($key, $value);
            }

            $context->addAction($action);
        }
        
        $lang = PEEP::getLanguage();
        
        if ( $userId && !$ownerMode && $photo->status == PHOTO_BOL_PhotoDao::STATUS_APPROVED)
        {
            $action = new BASE_ContextAction();
            $action->setKey('flag');
            $action->setParentKey('photo-moderate');
            $action->setLabel($lang->text('base', 'flag'));
            $action->setId('btn-photo-flag');
            $action->addAttribute('rel', $photoId);
            $action->addAttribute('url', PEEP::getRouter()->urlForRoute('view_photo', array('id' => $photo->id)));
            $context->addAction($action);
        }

        if ( $ownerMode || $modPermissions )
        {
            $action = new BASE_ContextAction();
            $action->setKey('edit');
            $action->setParentKey('photo-moderate');
            $action->setLabel($lang->text('base', 'edit'));
            $action->setId('btn-photo-edit');
            $action->addAttribute('rel', $photoId);
            $context->addAction($action);

            $action = new BASE_ContextAction();
            $action->setKey('delete');
            $action->setParentKey('photo-moderate');
            $action->setLabel($lang->text('base', 'delete'));
            $action->setId('photo-delete');
            $action->addAttribute('rel', $photoId);
            $context->addAction($action);
        }

        if ( $modPermissions )
        {
            if ( PHOTO_BOL_PhotoFeaturedService::getInstance()->isFeatured($photo->id) )
            {
                $action = new BASE_ContextAction();
                $action->setKey('unmark-featured');
                $action->setParentKey('photo-moderate');
                $action->setLabel($lang->text('photo', 'remove_from_featured'));
                $action->setId('photo-mark-featured');
                $action->addAttribute('rel', 'remove_from_featured');
                $action->addAttribute('photo-id', $photoId);
                $context->addAction($action);
            }
            elseif ( $photo->status == PHOTO_BOL_PhotoDao::STATUS_APPROVED )
            {
                $action = new BASE_ContextAction();
                $action->setKey('mark-featured');
                $action->setParentKey('photo-moderate');
                $action->setLabel($lang->text('photo', 'mark_featured'));
                $action->setId('photo-mark-featured');
                $action->addAttribute('rel', 'mark_featured');
                $action->addAttribute('photo-id', $photoId);
                $context->addAction($action);
            }

            if ( $photo->status != PHOTO_BOL_PhotoDao::STATUS_APPROVED )
            {
                $action = new BASE_ContextAction();
                $action->setKey('mark-approved');
                $action->setParentKey('photo-moderate');
                $action->setLabel($lang->text('photo', 'approve_photo'));
                $action->setUrl(PEEP::getRouter()->urlForRoute('photo.approve', array('id' => $photoId)));
//                $action->setId('photo-approve');
//                $action->addAttribute('url', PEEP::getRouter()->urlForRoute('photo.approve', array('id' => $photoId)));
                $context->addAction($action);
            }
        }

        $markup['contextAction'] = $context->render();

        $eventParams = array(
            'url' => PEEP::getRouter()->urlForRoute('view_photo', array('id' => $photo->id)),
            'image' => $photo->url,
            'title' => $photo->description,
            'entityType' => 'photo',
            'entityId' => $photo->id
        );
        $event = new BASE_CLASS_EventCollector('socialsharing.get_sharing_buttons', $eventParams);
        PEEP::getEventManager()->trigger($event);
        $markup['share'] = @implode("\n", $event->getData());

        $document = PEEP::getDocument();

        $onloadScript = $document->getOnloadScript();
        
        if ( !empty($onloadScript) )
        {
            $markup['onloadScript'] = $onloadScript;
        }
        
        $scriptFiles = $document->getScripts();
        
        if ( !empty($scriptFiles) )
        {
            $markup['scriptFiles'] = $scriptFiles;
        }

        $css = $document->getStyleDeclarations();
        
        if ( !empty($css) )
        {
            $markup['css'] = $css;
        }
        
        $cssFiles = $document->getStyleSheets();
        
        if ( !empty($cssFiles) )
        {
            $markup['cssFiles'] = $cssFiles;
        }

        $meta = $document->getMeta();

        if ( !empty($meta) )
        {
            $markup['meta'] = $meta;
        }
        
        return $markup;
    }

    public function approve( $params )
    {
        if ( !PEEP::getUser()->isAuthorized('photo') )
        {
            exit();
        }

        $entityId = $params['id'];

        $backUrl = PEEP::getRouter()->urlForRoute('view_photo', array(
            'id' => $entityId
        ));

        $event = new PEEP_Event("moderation.approve", array(
            "entityType" => PHOTO_CLASS_ContentProvider::ENTITY_TYPE,
            "entityId" => $entityId
        ));

        PEEP::getEventManager()->trigger($event);

        $data = $event->getData();

        if ( empty($data) )
        {
            $this->redirect($backUrl);
        }

        if ( $data["message"] )
        {
            PEEP::getFeedback()->info($data["message"]);
        }
        else
        {
            PEEP::getFeedback()->error($data["error"]);
        }

        $this->redirect($backUrl);
    }
}
