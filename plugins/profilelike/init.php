<?php

PEEP::getRouter()->addRoute(new PEEP_Route('profilelike.admin', 'admin/plugins/profilelike', "PROFILELIKE_CTRL_Admin", 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('profilelike', 'profilelike', "PROFILELIKE_CTRL_Profilelike", 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('profilelike.peoplewhoprofilelike', '/peoplewhoprofilelike', "PROFILELIKE_CTRL_PeopleWhoProfilelike", 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('profilelike.mostlikemembers', '/mostlikemembers', "PROFILELIKE_CTRL_Mostlikemembers", 'index'));
