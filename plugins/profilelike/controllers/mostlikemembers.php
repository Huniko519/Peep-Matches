<?php

class PROFILELIKE_CTRL_Mostlikemembers extends PEEP_ActionController 
{ 
	public function index()
    {
    	$userId = PEEP::getUser()->getId();
		$service = PROFILELIKE_BOL_ProfilelikeDao::getInstance();
		$people = $service->mostLikeMembersCtr();
	
		$language = PEEP::getLanguage();  
		
		$userIdList = array();
		
		foreach($people as $t) {
			array_push($userIdList, $t->id);
		}

		$avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList, true, true, true, true);
		$this->assign('avatars', $avatars);
		$this->assign('people', $people);
		$this->assign('url', PEEP_URL_HOME);
	}
}