<?php

class SEARCHSYS_CTRL_Search extends PEEP_ActionController
{
    public function rsp()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect403Exception;
        }

        if ( !PEEP::getConfig()->getValue('searchsys', 'site_search_enabled') )
        {
            throw new Redirect403Exception;
        }

        if ( !PEEP::getUser()->isAuthorized('searchsys', 'site_search') )
        {
            throw new Redirect403Exception;
        }

        $kw = $_GET['term'];

        $entries = SEARCHSYS_BOL_Service::getInstance()->searchEntries($kw, 750);

        if ( $entries )
        {
            foreach ( $entries as &$item )
            {
                $cmp = new SEARCHSYS_CMP_ConsoleResultItem($item);
                $item['html'] = $cmp->render();
            }
        }

        echo json_encode($entries);

        exit;
    }
    
    public function ajaxSearchAction()
    {
        if ( !PEEP::getRequest()->isPost() || !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect403Exception();
        }
        
        $authService = BOL_AuthorizationService::getInstance();
        
        $data = $_POST;
        PEEP::getSession()->set(SEARCHSYS_CLASS_SearchSystemForm::FORM_SESSION_VAR, $data);
        
        if ( !PEEP::getUser()->isAuthorized('searchsys', 'search_system') )
        {
            $status = $authService->getActionStatus('base', 'search_system');
            
            exit(json_encode(array('result' => false, 'error' => $status['msg'])));
        }

        if ( isset($data['accountType']) && $data['accountType'] === BOL_QuestionService::ALL_ACCOUNT_TYPES )
        {
            unset($data['accountType']);
        }

        $addParams = array('join' => '', 'where' => '');
        if ( !empty($data['onlineOnly']) )
        {
            $addParams['join'] .= " INNER JOIN `".BOL_UserOnlineDao::getInstance()->getTableName()."` `online` ON (`online`.`userId` = `user`.`id`) ";
        }

        if ( !empty($data['withPhoto']) )
        {
            $addParams['join'] .= " INNER JOIN `".PEEP_DB_PREFIX . "base_avatar` avatar ON (`avatar`.`userId` = `user`.`id`) ";
        }

        $userIdList = BOL_UserService::getInstance()->findUserIdListByQuestionValues($data, 0, BOL_SearchService::USER_LIST_SIZE, false, $addParams);
        $listId = 0;

        if ( count($userIdList) > 0 )
        {
            $listId = BOL_SearchService::getInstance()->saveSearchResult($userIdList);
        }

        PEEP::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);

        $authService->trackActionForUser(PEEP::getUser()->getId(), 'searchsys', 'search_system');

        $url = PEEP::getRouter()->urlForRoute('users-search-result');
        exit(json_encode(array('result' => true, 'url' => $url)));
    }
    
    public function ajaxSetAccType()
    {
        if ( !PEEP::getRequest()->isPost() || !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect403Exception();
        }

        $questionData = PEEP::getSession()->get(SEARCHSYS_CLASS_SearchSystemForm::FORM_SESSION_VAR);

        if ( $questionData === null )
        {
            $questionData = array();
        }
        
        $questionData['accountType'] = $_POST['accType'];
        PEEP::getSession()->set(SEARCHSYS_CLASS_SearchSystemForm::FORM_SESSION_VAR, $questionData);
        
        exit(json_encode(array('result' => true)));
    }

    public function result()
    {
        if ( !PEEP::getConfig()->getValue('searchsys', 'site_search_enabled') )
        {
            throw new Redirect404Exception();
        }

        if ( !PEEP::getUser()->isAuthorized('searchsys', 'site_search') )
        {
            $this->setTemplate(PEEP::getPluginManager()->getPlugin('base')->getCtrlViewDir() . 'authorization_failed.html');
            return;
        }

        if ( empty($_GET['term']) )
        {
            $this->redirect(PEEP::getRouter()->urlForRoute('base_index'));
        }

        $service = SEARCHSYS_BOL_Service::getInstance();
        $lang = PEEP::getLanguage();

        $groups = $service->getConfiguredGroupsForSiteSearch(true);
        $this->assign('groups', $groups);

        $term = htmlspecialchars($_GET['term']);
        $counters = $service->countEntries($term);

        if ( empty($_GET['group']) )
        {
            foreach ( $counters as $key => $counter )
            {
                if ( $counter > 0 )
                {
                    $group = $key;

                    break;
                }
            }
        }
        else
        {
            $group = $_GET['group'];
        }

        if ( empty($group) )
        {
            $group = $service->getDefaultGroup();
        }

        $this->assign('group', $group);
        $groupInfo = $service->getGroup($group);

        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;
        $limit = SEARCHSYS_BOL_Service::LIST_LIMIT;
        $offset = ($page - 1) * $limit;

        $entries = $service->searchEntriesInGroup($term, $group, $offset, $limit);
        if ( $entries )
        {
            foreach ( $entries as &$item )
            {
                if ( mb_strlen($item['info']) )
                {
                    $formatter = new SEARCHSYS_CLASS_Formatter();
                    $item['info'] = $formatter->formatResult(strip_tags($item['info']), array($term));
                }
                $cmp = new SEARCHSYS_CMP_ListResultItem($item);
                $item['html'] = $cmp->render();
            }
        }
        $this->assign('entries', $entries);

        $this->assign('counters', $counters);

        $pages = (int) ceil($counters[$group] / $limit);
        $paging = new BASE_CMP_Paging($page, $pages, $limit);
        $this->assign('paging', $paging->render());

        $this->assign('url', PEEP::getRouter()->urlForRoute('searchsys.search-result') . '?term=' . $term);

        PEEP::getDocument()->setHeading($lang->text('searchsys', 'search_result_page_heading'));
        PEEP::getDocument()->setTitle($lang->text('searchsys', 'search_result_page_heading'));
        $this->assign('themeUrl', PEEP::getThemeManager()->getCurrentTheme()->getStaticImagesUrl());

        $bcItems = array(
            array(
                'href' => $groupInfo['url'],
                'label' => $groupInfo['label']
            ),
            array(
                'label' => $lang->text('searchsys', 'search_result_for', array('term' => $term))
            )
        );

        $breadCrumbCmp = new BASE_CMP_Breadcrumb($bcItems);
        $this->addComponent('breadcrumb', $breadCrumbCmp);
    }
}