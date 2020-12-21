<?php

class COREVISITOR_CMP_UserCarousel extends PEEP_Component
{
    private $uniqId;
    
    private $params = array(
        "speed" => 200,
        "visible" => null,
        "start" => 0,
        "scroll" => null,
        "mouseWheel" => false,
        "auto" => null,
        "easing" => null,
        
        "count" => 20,
        "list" => "latest"
    );
    
    public function __construct( $params )
    {
        parent::__construct();
        
        $this->uniqId = uniqid("skuc-");
        $this->params = array_merge($this->params, $params);
    }
    
    public function getList($listName, $first, $count)
    {
        $idList = array();
        $outList = array();
        
        switch ($listName)
        {
            case "latest":
                $list = BOL_UserService::getInstance()->findRecentlyActiveList(0, $count);
                break;
            
            case "online":
                $list = BOL_UserService::getInstance()->findOnlineList(0, $count);
                break;
            
            case "featured":
                $list = BOL_UserService::getInstance()->findFeaturedList(0, $count);
                break;
        }
        
        foreach ( $list as $user )
        {
            $idList[] = $user->id;
        }
        
        $avatarUrlList = BOL_AvatarService::getInstance()->getAvatarsUrlList($idList);
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($idList);
        $urlList = BOL_UserService::getInstance()->getUserUrlsForList($idList);
        $locationList = BOL_QuestionService::getInstance()->getQuestionData($idList, array("googlemap_location"));
        $addressList = array();
        
        foreach ($locationList as $userId => $loc) 
        {
            if ( empty($loc["googlemap_location"]) ) 
            {
                $addressList[$userId] = null;
                continue;
            }
            
            $addr = $loc["googlemap_location"];
            
            $addressList[$userId] = is_array($addr) ? $addr["address"] : $addr;
        }
        
        $fields = $this->getFields($idList);
        
        foreach ( $list as $user )
        {
            $outList[$user->id] = array(
                "id" => $user->id,
                "avatar" => $avatarUrlList[$user->id],
                "displayName" => $displayNames[$user->id],
                "url" => $urlList[$user->id],
                "fields" => empty($fields[$user->id]) ? array() : $fields[$user->id],
                "location" => empty($addressList[$user->id]) ? null : $addressList[$user->id]
            );
        }
        
        return $outList;
    }
    
    public function getFields( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
            $qs[] = 'sex';

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $q )
        {

            $fields[$uid] = array();
            if( !empty($q['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($q['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                $fields[$uid]["age"] = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            if ( !empty($q['sex']) )
            {
                $fields[$uid]["sex"] = BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $q['sex']);
            }
        }

        return $fields;
    }
    
    public function onBeforeRender()
    {
        parent::onBeforeRender();
        
        $this->assign("list", $this->getList($this->params["list"], 0, $this->params["count"]));
        $this->assign("uniqId", $this->uniqId);
        
        $staticJs = PEEP::getPluginManager()->getPlugin("corevisitor")->getStaticJsUrl();
        PEEP::getDocument()->addScript($staticJs . "jcarousellite.js");
        
        $js = UTIL_JsGenerator::newInstance();
        $js->callFunction(array("Corevisitor", "UserCarousel"), array(
            $this->uniqId, $this->params
        ));
        
        PEEP::getDocument()->addOnloadScript($js);
    }
}