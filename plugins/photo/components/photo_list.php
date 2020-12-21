<?php

class PHOTO_CMP_PhotoList extends PEEP_Component
{
    public function __construct( array $params )
    {
        parent::__construct();
        
        $plugin = PEEP::getPluginManager()->getPlugin('photo');
        
        $hasSideBar = PEEP::getThemeManager()->getCurrentTheme()->getDto()->getSidebarPosition() != 'none';
        $photoParams = array(
            'classicMode' => (bool)PEEP::getConfig()->getValue('photo', 'photo_list_view_classic')
        );
        $contParams = array(
            'isClassic' => $photoParams['classicMode'],
            'isModerator' => PEEP::getUser()->isAuthorized('photo')
        );
        
        switch ( $params['type'] )
        {
            case 'albums':
                $photoParams = array(
                    'userId' => $params['userId'],
                    'action' => 'getAlbumList',
                    'level' => ($hasSideBar ? 2 : 2),
                    'classicMode' => (bool)PEEP::getConfig()->getValue('photo', 'album_list_view_classic'),
                    'isOwner' => $params['userId'] == PEEP::getUser()->getId() || PEEP::getUser()->isAuthorized('photo')
                );
                $contParams['isOwner'] = $photoParams['isOwner'];
                $contParams['isClassic'] = $photoParams['classicMode'];
                break;
            case 'albumPhotos':
                $photoParams['albumId'] = $params['albumId'];
                $photoParams['isOwner'] = PHOTO_BOL_PhotoAlbumService::getInstance()->isAlbumOwner($params['albumId'], PEEP::getUser()->getId());
                $photoParams['level'] = ($photoParams['classicMode'] ? ($hasSideBar ? 2 : 3) : 2);
                
                $contParams['isOwner'] = $photoParams['isOwner'];
                $contParams['albumId'] = $params['albumId'];
                break;
            case 'userPhotos':
                $photoParams['userId'] = $params['userId'];
                $photoParams['isOwner'] = $params['userId'] == PEEP::getUser()->getId();
                $photoParams['level'] = ($photoParams['classicMode'] ? ($hasSideBar ? 2 : 3) : 2);
                
                $contParams['isOwner'] = $photoParams['isOwner'];
                break;
            case 'tag':
                $photoParams['searchVal'] = $params['tag'];
            default:
                $photoParams['level'] = ($photoParams['classicMode'] ? ($hasSideBar ? 2 : 3) : 2);
                break;
        }
        
        $photoDefault = array(
            'getPhotoURL' => PEEP::getRouter()->urlFor('PHOTO_CTRL_Photo', 'ajaxResponder'),
            'listType' => $params['type'],
            'rateUserId' => PEEP::getUser()->getId(),
            'urlHome' => PEEP_URL_HOME,
            'tagUrl' => PEEP::getRouter()->urlForRoute('view_tagged_photo_list', array('tag' => '-tag-'))
        );
        
        $contDefault = array(
            'downloadAccept' => (bool)PEEP::getConfig()->getValue('photo', 'download_accept'),
            'downloadUrl' => PEEP_URL_HOME . 'photo/download-photo/:id',
            'actionUrl' => $photoDefault['getPhotoURL'],
            'listType' => $params['type']
        );
        
        $document = PEEP::getDocument();
        
        $document->addScriptDeclarationBeforeIncludes(
            UTIL_JsGenerator::composeJsString(';window.browsePhotoParams = Object.freeze({$params});', array(
                'params' => array_merge($photoDefault, $photoParams)
            ))
        );
        $document->addOnloadScript(';window.browsePhoto.init();');

        $document->addScriptDeclarationBeforeIncludes(
            UTIL_JsGenerator::composeJsString(';window.photoContextActionParams = Object.freeze({$params});', array(
                'params' => array_merge($contDefault, $contParams)
            ))
        );
        $document->addOnloadScript(';window.photoContextAction.init();');
        
        $this->assign('isClassicMode', $photoParams['classicMode']);
        $this->assign('hasSideBar', $hasSideBar);
        $this->assign('type', $params['type']);
        
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'browse_photo.css');
        $document->addScript($plugin->getStaticJsUrl() . 'utils.js');
        $document->addScript($plugin->getStaticJsUrl() . 'browse_photo.js');
        
        $language = PEEP::getLanguage();
        
        if ( $params['type'] != 'albums' )
        {
            $event = new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_INIT_FLOATBOX, $photoParams);
            PEEP::getEventManager()->trigger($event);

            $language->addKeyForJs('photo', 'tb_edit_photo');
            $language->addKeyForJs('photo', 'confirm_delete');
            $language->addKeyForJs('photo', 'mark_featured');
            $language->addKeyForJs('photo', 'remove_from_featured');
            $language->addKeyForJs('photo', 'no_photo');

            $language->addKeyForJs('photo', 'rating_total');
            $language->addKeyForJs('photo', 'rating_your');
            $language->addKeyForJs('base', 'rate_cmp_owner_cant_rate_error_message');

            $language->addKeyForJs('photo', 'download_photo');
            $language->addKeyForJs('photo', 'delete_photo');
            $language->addKeyForJs('photo', 'save_as_avatar');
            $language->addKeyForJs('photo', 'save_as_cover');
            
            $language->addKeyForJs('photo', 'search_invitation');
            $language->addKeyForJs('photo', 'set_as_album_cover');
            $language->addKeyForJs('photo', 'search_result_empty');
        }
        else
        {
            $language->addKeyForJs('photo', 'edit_album');
            $language->addKeyForJs('photo', 'delete_album');
            $language->addKeyForJs('photo', 'album_delete_not_allowed');
            $language->addKeyForJs('photo', 'cnews_album');
            $language->addKeyForJs('photo', 'are_you_sure');
            $language->addKeyForJs('photo', 'album_description');
        }
        
        $language->addKeyForJs('photo', 'no_items');
    }
}
