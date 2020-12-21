<?php

class PHOTO_CLASS_AlbumAddForm extends Form
{
    private $albumPhotosValidator;
    
    public function __construct()
    {
        parent::__construct('add-album');
        
        $this->setAjax();
        $this->setAjaxResetOnSuccess(FALSE);
        $this->setAction(PEEP::getRouter()->urlFor('PHOTO_CTRL_Photo', 'ajaxResponder'));
        
        $ajaxFunc = new HiddenField('ajaxFunc');
        $ajaxFunc->setValue('ajaxMoveToAlbum');
        $ajaxFunc->setRequired();
        $this->addElement($ajaxFunc);
        
        $fromAlbum = new HiddenField('from-album');
        $fromAlbum->setRequired();
        $fromAlbum->addValidator(new PHOTO_CLASS_AlbumOwnerValidator());
        $this->addElement($fromAlbum);
        
        $toAlbum = new HiddenField('to-album');
        $this->addElement($toAlbum);

        $photos = new HiddenField('photos');
        $photos->setRequired();
        $this->albumPhotosValidator = new AlbumPhotosValidator();
        $photos->addValidator($this->albumPhotosValidator);
        $this->addElement($photos);
        
        $albumName = new TextField('album-name');
        $albumName->setRequired();
        $albumName->addValidator(new PHOTO_CLASS_AlbumNameValidator(FALSE));
        $albumName->setHasInvitation(TRUE);
        $albumName->setInvitation(PEEP::getLanguage()->text('photo', 'album_name'));
        $albumName->addAttribute('class', 'peep_smallmargin');
        $this->addElement($albumName);
        
        $desc = new Textarea('desc');
        $desc->setHasInvitation(TRUE);
        $desc->setInvitation(PEEP::getLanguage()->text('photo', 'album_desc'));
        $this->addElement($desc);
        
        $this->addElement(new Submit('add'));
    }
    
    public function isValid( $data )
    {
        $this->albumPhotosValidator->setAlbumId($data['from-album']);
        
        if ( !empty($data['to-album']) )
        {
            $this->getElement('to-album')->setRequired()->addValidator(new PHOTO_CLASS_AlbumOwnerValidator());
        }
        
        return parent::isValid($data);
    }
    
    public function process()
    {
        $values = $this->getValues();
        
        $photoIdList = array_unique(array_map('intval', explode(',', $values['photos'])));
        sort($photoIdList);
        
        PEEP::getEventManager()->trigger(
            new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_BEFORE_PHOTO_MOVE,
                array(
                    'fromAlbum' => $values['from-album'],
                    'toAlbum' => $values['to-album'],
                    'photoIdList' => $photoIdList
                )
            )
        );
        
        $albumService = PHOTO_BOL_PhotoAlbumService::getInstance();
        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $fromAlbum = $albumService->findAlbumById($values['from-album']);
        $userDto =  BOL_UserService::getInstance()->findUserById($fromAlbum->userId);
        $albumName = htmlspecialchars(trim($values['album-name']));
        $isNewAlbum = FALSE;
        
        if ( ($toAlbum = $albumService->findAlbumByName($albumName, $userDto->id)) === NULL )
        {
            $toAlbum = new PHOTO_BOL_PhotoAlbum();
            $toAlbum->name = $albumName;
            $toAlbum->description = htmlspecialchars(trim($values['desc']));
            $toAlbum->userId = $userDto->id;
            $toAlbum->entityId = $userDto->id;
            $toAlbum->entityType = 'user';
            $toAlbum->createDatetime = time();
            $albumService->addAlbum($toAlbum);
            
            $this->getElement('to-album')->setValue($toAlbum->id);
            $isNewAlbum = TRUE;
        }
        
        if ( $photoService->movePhotosToAlbum($photoIdList, $toAlbum->id, $isNewAlbum) )
        {
            $values = $this->getValues();
            
            PEEP::getEventManager()->trigger(
                new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_AFTER_PHOTO_MOVE,
                    array(
                        'fromAlbum' => $values['from-album'],
                        'toAlbum' => $values['to-album'],
                        'photoIdList' => $photoIdList
                    )
                )
            );
        }
        
        return TRUE;
    }
}

class AlbumPhotosValidator extends PEEP_Validator
{
    private $allbumId;
    
    public function setAlbumId( $albumId )
    {
        $this->allbumId = (int)$albumId;
    }

    public function isValid( $value )
    {
        $photoIdList = explode(',', $value);
        $count = count($photoIdList);
        
        if ( $count === 0 || (int)PHOTO_BOL_PhotoDao::getInstance()->countPhotosInAlbumByPhotoIdList($this->allbumId, $photoIdList) !== $count )
        {
            return FALSE;
        }
        
        return TRUE;
    }
    
    public function getJsValidator()
    {
        return '{
            validate : function( value )
            {
                if ( value.length === 0 )
                {
                    alert(PEEP.getLanguageText("photo", "no_photo_selected"));
                    throw "Required";
                }
            }
        }';
    }
}
