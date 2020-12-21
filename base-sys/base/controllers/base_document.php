<?php

class BASE_CTRL_BaseDocument extends PEEP_ActionController
{

    public function index()
    {
        //TODO implement

    }

    public function alertPage()
    {
        PEEP::getDocument()->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate(PEEP_MasterPage::TEMPLATE_BLANK));
        $this->assign('text', PEEP::getSession()->get('baseAlertPageMessage'));
        PEEP::getSession()->delete('baseMessagePageMessage');
    }

    public function confirmPage()
    {
        if ( empty($_GET['back_uri']) )
        {
            throw new Redirect404Exception();
        }

        PEEP::getDocument()->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate(PEEP_MasterPage::TEMPLATE_BLANK));
        $this->assign('text', PEEP::getSession()->get('baseConfirmPageMessage'));
        PEEP::getSession()->delete('baseConfirmPageMessage');
        $this->assign('okBackUrl', PEEP::getRequest()->buildUrlQueryString(PEEP_URL_HOME . urldecode($_GET['back_uri']), array('confirm-result' => 1)));
        $this->assign('clBackUrl', PEEP::getRequest()->buildUrlQueryString(PEEP_URL_HOME . urldecode($_GET['back_uri']), array('confirm-result' => 0)));
    }

    public function page404()
   
    {
        PEEP::getResponse()->setHeader('HTTP/1.0', '404 Not Found');
        PEEP::getResponse()->setHeader('Status', '404 Not Found');
        $this->setPageHeading(PEEP::getLanguage()->text('base', 'base_document_404_heading'));
        $this->setPageTitle(PEEP::getLanguage()->text('base', 'base_document_404_title'));
        $this->setDocumentKey('base_page404');

    }

    public function page403( array $params )
    {
        $language = PEEP::getLanguage();
        PEEP::getResponse()->setHeader('HTTP/1.0', '403 Forbidden');
        PEEP::getResponse()->setHeader('Status', '403 Forbidden');
        $this->setPageHeading($language->text('base', 'base_document_403_heading'));
        $this->setPageTitle($language->text('base', 'base_document_403_title'));
        $this->setDocumentKey('base_page403');
        $this->assign('message', !empty($params['message']) ? $params['message'] : $language->text('base', 'base_document_403'));
    }

    public function maintenance()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            PEEP::getDocument()->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate('blank'));
            if ( !empty($_COOKIE['adminToken']) && trim($_COOKIE['adminToken']) == PEEP::getConfig()->getValue('base', 'admin_cookie') )
            {
                $this->assign('disableMessage', PEEP::getLanguage()->text('base', 'maintenance_disable_message', array('url' => PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlForRoute('static_sign_in'), array('back-uri' => urlencode('admin/pages/maintenance'))))));
            }
        }
        else
        {
            exit('{}');
        }
    }

    public function splashScreen()
    {
        if ( isset($_GET['agree']) )
        {
            setcookie('splashScreen', 1, time() + 3600 * 24 * 30, '/');
            $url = PEEP::getRouter()->getBaseUrl();
            $url .= isset($_GET['back_uri']) ? $_GET['back_uri'] : '';
            $this->redirect($url);
        }

        PEEP::getDocument()->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate('blank'));
        $this->assign('submit_url', PEEP::getRequest()->buildUrlQueryString(null, array('agree' => 1)));

        $leaveUrl = PEEP::getConfig()->getValue('base', 'splash_leave_url');

        if ( !empty($leaveUrl) )
        {
            $this->assign('leaveUrl', $leaveUrl);
        }
    }

    public function passwordProtection()
    {
        $form = new Form('password_protection');
        $form->setAjax(true);
        $form->setAction(PEEP::getRouter()->urlFor('BASE_CTRL_BaseDocument', 'passwordProtection'));
        $form->setAjaxDataType(Form::AJAX_DATA_TYPE_SCRIPT);

        $password = new PasswordField('password');
        $form->addElement($password);

        $submit = new Submit('submit');
        $submit->setValue(PEEP::getLanguage()->text('base', 'password_protection_submit_label'));
        $form->addElement($submit);
        $this->addForm($form);

        if ( PEEP::getRequest()->isAjax() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            $password = PEEP::getConfig()->getValue('base', 'guests_can_view_password');
            $data['password'] = crypt($data['password'], PEEP_PASSWORD_SALT);

            if ( !empty($data['password']) && $data['password'] === $password )
            {
                setcookie('base_password_protection', UTIL_String::getRandomString(), (time() + 86400 * 30), '/');
                echo "PEEP.info('" . PEEP::getLanguage()->text('base', 'password_protection_success_message') . "');window.location.reload();";
            }
            else
            {
                echo "PEEP.error('" . PEEP::getLanguage()->text('base', 'password_protection_error_message') . "');";
            }
            exit;
        }

        PEEP::getDocument()->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate(PEEP_MasterPage::TEMPLATE_BLANK));
    }

    public function installCompleted()
    {
        if ( !PEEP::getRequest()->isAjax() && !empty($_GET['redirect']) )
        {
            if ( !PEEP::getConfig()->configExists("base", "install_complete") )
            {
                PEEP::getConfig()->addConfig("base", "install_complete", 1);
            }
            else
            {
                PEEP::getConfig()->saveConfig("base", "install_complete", 1);
            }

            $this->redirect(PEEP::getRequest()->buildUrlQueryString(null, array('redirect' => null)));
        }

        $masterPageFileDir = PEEP::getThemeManager()->getMasterPageTemplate('blank');
        PEEP::getDocument()->getMasterPage()->setTemplate($masterPageFileDir);
    }

    public function redirectToMobile()
    {
        $urlToRedirect = PEEP::getRouter()->getBaseUrl();

        if ( !empty($_GET['back-uri']) )
        {
            $urlToRedirect .= urldecode($_GET['back-uri']);
        }
        
        PEEP::getApplication()->redirect($urlToRedirect, PEEP::CONTEXT_MOBILE);
    }

    public function authorizationFailed( array $params )
    {
        $language = PEEP::getLanguage();
        $this->setPageHeading($language->text('base', 'base_document_auth_failed_heading'));
        $this->setPageTitle($language->text('base', 'base_document_auth_failed_heading'));
        $this->setTemplate(PEEP::getPluginManager()->getPlugin('base')->getCtrlViewDir() . 'authorization_failed.html');

        $this->assign('message', !empty($params['message']) ? $params['message'] : null);
    }
//    public function tos()
//    {
//        $language = PEEP::getLanguage();
//        $this->setPageHeading($language->text('base', 'terms_of_use_page_heading'));
//        $this->setPageTitle($language->text('base', 'terms_of_use_page_heading'));
//        $this->assign('content', $language->text('base', 'terms_of_use_page_content'));
//
//
//        $document = BOL_DocumentDao::getInstance()->findStaticDocument('terms-of-use');
//
//        if ( $document !== null )
//        {
//            $languageService = BOL_LanguageService::getInstance(false);
//            $languageId = $languageService->getCurrent()->getId();
//            $prefix = $languageService->findPrefix('base');
//
//            $key = $languageService->findKey('base', 'terms_of_use_page_heading');
//
//            if( $key === null )
//            {
//                $key = new BOL_LanguageKey();
//                $key->setKey('terms_of_use_page_heading');
//                $key->setPrefixId($prefix->getId());
//                $languageService->saveKey($key);
//            }
//
//            $value = $languageService->findValue($languageId, $key->getId());
//            $value->setValue($language->text('base', "local_page_title_{$document->getKey()}"));
//
//            $key = $languageService->findKey('base', 'terms_of_use_page_content');
//
//            if( $key === null )
//            {
//                $key = new BOL_LanguageKey();
//                $key->setKey('terms_of_use_page_content');
//                $key->setPrefixId($prefix->getId());
//                $languageService->saveKey($key);
//            }
//
//            $value = $languageService->findValue($languageId, $key->getId());
//            $value->setValue($language->text('base', "local_page_content_{$document->getKey()}"));
//
//            $key = $languageService->findKey('base', 'terms_of_use_page_meta');
//
//            if( $key === null )
//            {
//                $key = new BOL_LanguageKey();
//                $key->setKey('terms_of_use_page_meta');
//                $key->setPrefixId($prefix->getId());
//                $languageService->saveKey($key);
//            }
//
//            $value = $languageService->findValue($languageId, $key->getId());
//            $value->setValue($language->text('base', "local_page_meta_tags_{$document->getKey()}"));
//
//           $menuItem = BOL_NavigationService::getInstance()->findMenuItemByDocumentKey($document->getKey());
//
//        }
//    }
           
}