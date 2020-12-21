<?php

class RATEGAME_CMP_RandomPhoto extends PEEP_Component
{
    /**
     * @var PHOTO_BOL_PhotoService 
     */
    private $photoService;
    
    /**
     * @var RATEGAME_BOL_RategameService 
     */
    private $rategameService;
    
    /**
     *
     * @var BOL_RateService 
     */
    private $rateService;

    public function __construct( array $params)
    {
        parent::__construct();
        
        $language = PEEP::getLanguage();
        
        $this->photoService = PHOTO_BOL_PhotoService::getInstance();
        $this->rategameService = RATEGAME_BOL_RategameService::getInstance();


        $sexes = BOL_QuestionValueDao::getInstance()->findQuestionValues('sex');
        $allSex = array();
        foreach($sexes as $id=>$sex)
        {
            $allSex[$id]['value']=$sex->value;
            $allSex[$id]['name']=PEEP::getLanguage()->text('base', 'questions_question_sex_value_'.$sex->value);
        }
        
        $this->assign('allSex', $allSex);
        
        $this->assign('selectedSex', $params['sex']);
        
        $route = substr(PEEP::getRouter()->urlForRoute('rate_photo_game'), 0, -1);
        $this->assign('rategame_url', $route);        
        
        $photo = $this->rategameService->getNotRatedPhotoByUserId( PEEP::getUser()->getId(), $params['sex'] );

        if ( !$photo )
        {
            $this->assign('no_photos', true);
            $this->assign('label', PEEP::getLanguage()->text('base', 'empty_list'));
            return;
        }
        else
        {
            $this->assign('no_photos', false);
        }
        
        $this->assign('label', PEEP::getLanguage()->text('rategame', 'rate_photo'));
        
        $contentOwner = $this->photoService->findPhotoOwner($photo->id);
        $rate = new RATEGAME_CMP_Rate('photo', 'photo_rates', $photo->id, $contentOwner, $params['sex']);
        
        $this->assign('photo', $photo);
        $this->assign('url', $this->photoService->getPhotoUrl($photo->id));
        
        $toolbar = array();

       

        array_push($toolbar, array(
            'href' => 'javascript://',
            'id' => 'btn-photo-flag',
            'label' => $language->text('base', 'flag')
        ));
        
        $this->assign('toolbar', $toolbar);
        $this->addComponent('rate', $rate);
    }
}