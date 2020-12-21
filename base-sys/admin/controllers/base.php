<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CTRL_Base extends ADMIN_CTRL_Abstract
{
	public function index()
	{
	    $this->setPageHeading(PEEP::getLanguage()->text('admin', 'admin_dashboard'));
	    $this->setPageHeadingIconClass('peep_ic_dashboard');
$this->assign('totalUsers', BOL_UserService::getInstance()->count(true));

        $this->assign('version', PEEP::getConfig()->getValue('base', 'soft_version'));
        
	}
}