<?php

class RATEGAME_CTRL_Rate extends PEEP_ActionController {

    const ENTITY_TYPE = 'photo_rates';
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Rate rate photo game action
     *
     * @param array $params
     */
    public function index(array $params) {
        $language = PEEP::getLanguage();

        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, 'rategame', 'rategame');

        if (!PEEP::getPluginManager()->isPluginActive('photo')) {
            $this->assign('service_not_available', $language->text('rategame', 'service_not_available'));
            return;
        } else {
            $this->assign('service_not_available', false);
        }

        $sex = 0;
        if (!empty($params['sex'])) {
            $sex = $params['sex'];
        }

        $randomPhoto = new RATEGAME_CMP_RandomPhoto(array('sex' => $sex));
        $this->addComponent('randomPhoto', $randomPhoto);
    }

    public function refreshPhoto() {
        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $rategameService = RATEGAME_BOL_RategameService::getInstance();
        $photo = $rategameService->getNotRatedPhotoByUserId(PEEP::getUser()->getId(), $_POST['sex']);

        if (!$photo) {
            exit( json_encode(array('noPhoto' => true)) );
            return;
        }

        $ownerId = $photoService->findPhotoOwner($photo->id);
        $imagePath = $photoService->getPhotoUrl($photo->id);
        $totalScoreCmp = new BASE_CMP_TotalScore($photo->id, RATEGAME_CTRL_Rate::ENTITY_TYPE);

        exit( json_encode(array('totalScoreCmp' => $totalScoreCmp->render(), 'noPhoto' => false, 'ownerId' => $ownerId, 'entityId' => $photo->id, 'imagePath' => $imagePath)) );
    }

    public function getNextPhoto() {
        $service = BOL_RateService::getInstance();

        $entityId = (int) $_POST['entityId'];
        $entityType = RATEGAME_CTRL_Rate::ENTITY_TYPE;
        $rate = (int) $_POST['rate'];
        $ownerId = (int) $_POST['ownerId'];
        $userId = PEEP::getUser()->getId();

        if (!PEEP::getUser()->isAuthenticated()) {
            exit( json_encode(array('errorMessage' => PEEP::getLanguage()->text('base', 'rate_cmp_auth_error_message'))) );
        }

        if ($userId === $ownerId) {
            exit( json_encode(array('errorMessage' => PEEP::getLanguage()->text('base', 'rate_cmp_owner_cant_rate_error_message'))) );
        }

        if (false) {
            exit( json_encode(array('errorMessage' => 'Auth error')) );
        }

        $rateItem = $service->findRate($entityId, $entityType, $userId);

        if ($rateItem === null) {
            $rateItem = new BOL_Rate();
            $rateItem->setEntityId($entityId)->setEntityType($entityType)->setUserId($userId)->setActive(true);
        }

        $rateItem->setScore($rate)->setTimeStamp(time());

        $service->saveRate($rateItem);

        /**/
        
        $this->refreshPhoto();
        
        /*
          $photoService = PHOTO_BOL_PhotoService::getInstance();
          $rategameService = RATEGAME_BOL_RategameService::getInstance();
          $photo = $rategameService->getNotRatedPhotoByUserId( PEEP::getUser()->getId(), $_POST['sex'] );

          if ( !$photo )
          {
          exit( json_encode(array('noPhoto'=>true)) );
          }

          $entityId = $photo->id;
          $ownerId = $photoService->findPhotoOwner($photo->id);
          $imagePath = $photoService->getPhotoUrl($photo->id);
          $totalScoreCmp = new BASE_CMP_TotalScore($entityId, $entityType);

          exit( json_encode(array('totalScoreCmp' => $totalScoreCmp->render(),'noPhoto'=>false, 'ownerId'=>$ownerId, 'entityId'=>$entityId, 'imagePath'=>$imagePath)) );
         */
    }

}

