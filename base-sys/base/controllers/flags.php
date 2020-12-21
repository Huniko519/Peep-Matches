<?php

class BASE_CTRL_Flags extends PEEP_ActionController
{

    public function index( $params )
    {
        $s = BOL_FlagService::getInstance();

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;

        $rpp = 20;

        $first = ($page - 1) * $rpp;
        $count = $rpp;

        $itemCount = $s->count('blog-post');

        $pageCount = 0;

        $type = (!empty($params['type'])) ? $params['type'] : '';

        $this->assign('type', $type);

        $this->assign('langKey', BOL_FlagService::getInstance()->findLangKey($type));

        $list = BOL_FlagService::getInstance()->findList($first, $count, $type);
        $itemCount = BOL_FlagService::getInstance()->countFlaggedItems($type);

        if ( empty($list) )
        {
            $this->redirect(PEEP::getRouter()->urlForRoute('base_member_dashboard'));
        }

        foreach ( $list as $key => $f )
        {
            $list[$key]['spamUsers'] = $s->findFlaggedUserIdList($type, $f['entityId'], 'spam');
            $list[$key]['offenceUsers'] = $s->findFlaggedUserIdList($type, $f['entityId'], 'offence');
            $list[$key]['illegalUsers'] = $s->findFlaggedUserIdList($type, $f['entityId'], 'illegal');

            $uil = array_merge($list[$key]['spamUsers'], $list[$key]['offenceUsers'], $list[$key]['illegalUsers']);

            $this->assign('dl', BOL_UserService::getInstance()->getDisplayNamesForList($uil));
            $this->assign('ul', BOL_UserService::getInstance()->getUserNamesForList($uil));
        }

        $this->assign('list', $list);

        $this->addComponent('menu', $this->getMenu($type));

        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($itemCount / $rpp), 5));
    }

    private function getMenu( $active )
    {
        $language = PEEP::getLanguage();

        $list = BOL_FlagService::getInstance()->findTypeList();

        $mil = array();
        $i = 0;
        foreach ( $list as $type )
        {
            $mi = new BASE_MenuItem();

            $c = BOL_FlagService::getInstance()->countFlaggedItems($type['type']);

            $a = explode('+', $type['langKey']);

            $mi->setLabel($language->text($a[0], $a[1]) . ($c > 0 ? " ($c)" : ''))
                ->setKey($type['type'])
                ->setOrder($i++)
                ->setUrl(PEEP::getRouter()->urlFor('BASE_CTRL_Flags', 'index', array('type' => $type['type'])));

            if ( $active == $type )
            {
                $mi->isActive(true);
            }

            $mil[] = $mi;
        }

        return new BASE_CMP_ContentMenu($mil);
    }
}