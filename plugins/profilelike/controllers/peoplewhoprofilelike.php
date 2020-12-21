<?php

class PROFILELIKE_CTRL_Peoplewhoprofilelike extends PEEP_ActionController 
{ 
	public function index()
    {
    	$userId = PEEP::getUser()->getId();
		$this->setPageTitle("Who liked me");
		$service = PROFILELIKE_BOL_ProfilelikeDao::getInstance();
		$people = $service->peopleWhoProfileLike($userId);
	
		$language = PEEP::getLanguage();  
		
		$userIdList = array();
		
		foreach($people as $t) {
			array_push($userIdList, $t->userId);
		}

		$avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList, true, true, true, true);
		$this->assign('avatars', $avatars);
		$this->assign('people', $people);
		$this->assign('url', PEEP_URL_HOME);
	}
}