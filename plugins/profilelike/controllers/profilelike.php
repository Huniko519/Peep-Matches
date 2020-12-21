<?php

class PROFILELIKE_CTRL_Profilelike extends PEEP_ActionController 
{ 
	public function index()
    {
    	$userId = PEEP::getUser()->getId();
		
		$service = PROFILELIKE_BOL_ProfilelikeDao::getInstance();
		$language = PEEP::getLanguage();  
		if (PEEP::getRequest()->isPost())
        {
			if(empty($_POST['actionlike']) || $_POST['actionlike'] == 'profilelike')
			{
				$service->addLike($_POST['userid'], $_POST['profileid']);
				PEEP::getFeedback()->info($language->text('profilelike', 'feedback_profilelike'));
			}
			else
			{
				$service->unLike($_POST['userid'], $_POST['profileid']);
				//$service->likeMeNotification($_POST['userid'], $_POST['profileid']);
				PEEP::getFeedback()->info($language->text('profilelike', 'feedback_unprofilelike'));
			}
			$this->redirect($_SERVER['HTTP_REFERER']);
		}
	}
}