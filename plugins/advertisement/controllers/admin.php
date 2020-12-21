<?php

class ADS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
 
    private $menu;
  
    private $adsService;

 
    public function __construct()
    {
        parent::__construct();

        $language = PEEP::getLanguage();

        $menu = new BASE_CMP_ContentMenu();

        $menuItem = new BASE_MenuItem();
        $menuItem->setKey('index');
        $menuItem->setLabel($language->text('ads', 'advertisement_menu_banner_list'));
        $menuItem->setUrl(PEEP::getRouter()->urlForRoute('ads.admin_index'));
        $menuItem->setIconClass('peep_ic_files');
        $menuItem->setOrder(1);
        $menu->addElement($menuItem);

        $menuItem = new BASE_MenuItem();
        $menuItem->setKey('manage');
        $menuItem->setLabel($language->text('ads', 'advertisement_menu_manage_banners'));
        $menuItem->setUrl(PEEP::getRouter()->urlForRoute('ads.admin_manage'));
        $menuItem->setIconClass('peep_ic_gear_wheel');
        $menuItem->setOrder(2);
        $menu->addElement($menuItem);

        $this->addComponent('menu', $menu);
        $this->menu = $menu;
        $this->adsService = ADS_BOL_Service::getInstance();

        $this->setPageTitle($language->text('ads', 'page_title_ads'));
        $this->setPageHeading($language->text('ads', 'page_heading_ads'));
        $this->setPageHeadingIconClass('peep_ic_star');

        PEEP::getNavigation()->activateMenuItem('admin_plugins', 'admin', 'sidebar_menu_plugins_installed');
    }

    public function index()
    {
        $this->menu->getElement('index')->setActive(true);
        $this->assign('addUrl', PEEP::getRouter()->urlFor('ADS_CTRL_Admin', 'add'));
        $banners = $this->adsService->findAllBannersInfo();

        foreach ( $banners as $key => $banner )
        {
            $banners[$key]['editUrl'] = PEEP::getRouter()->urlForRoute('ads.banner_edit', array('bannerId' => $key));
            $banners[$key]['deleteUrl'] = PEEP::getRouter()->urlForRoute('ads.banner_delete', array('bannerId' => $key));
        }

        $this->assign('banners', $banners);
    }

    public function add()
    {
        $language = PEEP::getLanguage();

        $this->menu->getElement('index')->setActive(true);

        $form = $this->getAdsForm('banner_add_form');

        $this->addForm($form);

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                $banner = new ADS_BOL_Banner();
                $banner->setLabel($data['title']);
                $banner->setCode($data['code']);
                $this->adsService->saveBanner($banner);

                if ( $data['select_country'] !== null && sizeof($data['select_country']) > 0 )
                {
                    foreach ( $data['select_country'] as $loc )
                    {
                        $bannerLocation = new ADS_BOL_BannerLocation();
                        $bannerLocation->setBannerId($banner->getId());
                        $bannerLocation->setLocation($loc);
                        $this->adsService->saveBannerLocation($bannerLocation);
                    }
                }

                PEEP::getFeedback()->info($language->text('ads', 'ads_banner_add_success_message'));
                $this->redirectToAction('index');
            }
        }
    }

    public function edit( $params )
    {
        $language = PEEP::getLanguage();

        $this->menu->getElement('index')->setActive(true);

        if ( empty($params['bannerId']) )
        {
            $this->redirectToAction('index');
        }

        $bannerId = (int) $params['bannerId'];
        $banner = $this->adsService->findBannerById($bannerId);

        if ( $banner === null )
        {
            $this->redirectToAction('index');
        }

        $form = $this->getAdsForm('banner_edit_form');
        $form->getSubmitElement('submit')->setValue($language->text('ads', 'ads_edit_banner_submit_label'));
        $form->getElement('title')->setValue($banner->getLabel());
        $form->getElement('code')->setValue($banner->getCode());

        if ( $this->adsService->getLocationEnabled() )
        {
        $bannerLocations = $this->adsService->findBannerLocations($banner->getId());
        $locationCodes = array();
        if ( !empty($bannerLocations) )
        {
            /* @var $bannerLocation ADS_BOL_BannerLocation */
            foreach ( $bannerLocations as $bannerLocation )
            {
                $locationCodes[] = $bannerLocation->getLocation();
            }
        }
        $form->getElement('select_country')->setValue($locationCodes);
        }

        $this->addForm($form);

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                $banner->setLabel($data['title']);
                $banner->setCode($data['code']);
                $this->adsService->saveBanner($banner);
                $this->adsService->resetBannerLocations($banner->getId());

                if ( $data['select_country'] !== null && sizeof($data['select_country']) > 0 )
                {
                    foreach ( $data['select_country'] as $loc )
                    {
                        $bannerLocation = new ADS_BOL_BannerLocation();
                        $bannerLocation->setBannerId($banner->getId());
                        $bannerLocation->setLocation($loc);
                        $this->adsService->saveBannerLocation($bannerLocation);
                    }
                }

                PEEP::getFeedback()->info($language->text('ads', 'ads_banner_edit_success_message'));
                $this->redirectToAction('index');
            }
        }
    }

    public function delete( $params )
    {
        if ( empty($params['bannerId']) )
        {
            $this->redirectToAction('index');
        }

        $bannerId = (int) $params['bannerId'];
        $banner = $this->adsService->findBannerById($bannerId);

        if ( $banner === null )
        {
            $this->redirectToAction('index');
        }

        $this->adsService->deleteBanner($banner->getId());
        PEEP::getFeedback()->info(PEEP::getLanguage()->text('ads', 'ads_banner_delete_success_message'));
        $this->redirectToAction('index');
    }

    public function manage()
    {
        $this->menu->getElement('manage')->setActive(true);

        $language = PEEP::getLanguage();

        $event = new BASE_CLASS_EventCollector('ads.enabled_plugins');
        PEEP::getEventManager()->trigger($event);

        $pluginList = $event->getData();

        $pluginsArray = array();
        $first = false;
        
        foreach ( $pluginList as $plugin )
        {
            $pluginObj = PEEP::getPluginManager()->getPlugin($plugin);

            if ( $pluginObj === null )
            {
                continue;
            }

            $pluginsArray[$pluginObj->getDto()->getKey()] = strtolower($pluginObj->getDto()->getKey()) === 'base' ? $language->text('ads', 'ads_manage_global_label') : $pluginObj->getDto()->getTitle();

            if ( !$first )
            {
                $first = $pluginObj->getDto()->getKey();
            }
        }

        $selected = ( isset($_GET['plugin']) && in_array(trim($_GET['plugin']), array_keys($pluginsArray)) ) ? trim($_GET['plugin']) : $first;

        $topForm = $this->getManageForm('top_form');
        $this->addForm($topForm);
        $topForm->getElement('plugin_key')->setValue($selected);
        $topForm->getElement('banners')->setValue($this->adsService->findBannerIdList(ADS_BOL_Service::BANNER_POSITION_TOP, $selected));
        $rightForm = $this->getManageForm('right_form');
        $rightForm->getElement('plugin_key')->setValue($selected);
        $rightForm->getElement('banners')->setValue($this->adsService->findBannerIdList(ADS_BOL_Service::BANNER_POSITION_RIGHT, $selected));
        $this->addForm($rightForm);
        
        $leftForm = $this->getManageForm('left_form');
        $leftForm->getElement('plugin_key')->setValue($selected);
        $leftForm->getElement('banners')->setValue($this->adsService->findBannerIdList(ADS_BOL_Service::BANNER_POSITION_LEFT, $selected));
        $this->addForm($leftForm);

        $bottomForm = $this->getManageForm('bottom_form');
        $bottomForm->getElement('plugin_key')->setValue($selected);
        $bottomForm->getElement('banners')->setValue($this->adsService->findBannerIdList(ADS_BOL_Service::BANNER_POSITION_BOTTOM, $selected));
        $this->addForm($bottomForm);

        $this->assign('plugins', $pluginsArray);
        $this->assign('selected', $selected);

        $this->assign('top_link_label', $language->text('ads', 'ads_banners_count_label', array('count' => $this->adsService->findBannersCount('top', $selected))));
        $this->assign('right_link_label', $language->text('ads', 'ads_banners_count_label', array('count' => $this->adsService->findBannersCount('right', $selected))));
        $this->assign('left_link_label', $language->text('ads', 'ads_banners_count_label', array('count' => $this->adsService->findBannersCount('left', $selected))));
        $this->assign('bottom_link_label', $language->text('ads', 'ads_banners_count_label', array('count' => $this->adsService->findBannersCount('bottom', $selected))));

       

        $floatboxCapLabel = $language->text('ads', 'ads_banners_add_floatbox_label');

        $script = "$('#top_link').click(function(){window.adsForm = new PEEP_FloatBox({\$title:'" . $floatboxCapLabel . "', \$contents: $('#top_form'), width: '550px'})});";
        $script .= "$('#bottom_link').click(function(){window.adsForm = new PEEP_FloatBox({\$title:'" . $floatboxCapLabel . "', \$contents: $('#bottom_form'), width: '550px'})});";
        $script .= "$('#right_link').click(function(){window.adsForm = new PEEP_FloatBox({\$title:'" . $floatboxCapLabel . "', \$contents: $('#right_form'), width: '550px'})});";
        $script .= "$('#left_link').click(function(){window.adsForm = new PEEP_FloatBox({\$title:'" . $floatboxCapLabel . "', \$contents: $('#left_form'), width: '550px'})});";
        PEEP::getDocument()->addOnloadScript($script);
    }

    public function processBannerForm()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $form = $this->getManageForm($_POST['form_name']);

        if ( $form->isValid($_POST) )
        {
            $data = $form->getValues();

            switch ( $data['form_name'] )
            {
                case 'top_form':
                    $position = ADS_BOL_Service::BANNER_POSITION_TOP;
                    break;

                case 'right_form':
                    $position = ADS_BOL_Service::BANNER_POSITION_RIGHT;
                    break;

               case 'left_form':
                    $position = ADS_BOL_Service::BANNER_POSITION_LEFT;
                    break;

                case 'bottom_form':
                    $position = ADS_BOL_Service::BANNER_POSITION_BOTTOM;
                    break;
            }

            $pluginKey = trim($data['plugin_key']);

            $this->adsService->resetBannersForPlugin($position, $pluginKey);

            if ( $data['banners'] !== null )
            {
                foreach ( $data['banners'] as $banner )
                {
                    $bannerPosition = new ADS_BOL_BannerPosition();
                    $bannerPosition->setPosition($position);
                    $bannerPosition->setPluginKey($pluginKey);
                    $bannerPosition->setBannerId($banner);
                    $this->adsService->saveBannerPosition($bannerPosition);
                }
            }

            echo json_encode(
                array(
                    'message' => PEEP::getLanguage()->text('ads', 'ads_manage_add_banners_message'),
                    'id' => $position . '_link',
                    'html' => PEEP::getLanguage()->text('ads', 'ads_banners_count_label', array('count' => $this->adsService->findBannersCount($position, $pluginKey)))
                )
            );
            exit;
        }
    }
 
    private $banners;

    private function getManageForm( $name )
    {
        if ( $this->banners === null )
        {
            $banners = $this->adsService->findAllBanners();
            $bannerInfo = array();

            foreach ( $banners as $banner )
            {
                
                $this->banners[$banner->getId()] = $banner->getLabel();
            }
        }

        $form = new Form($name);
        $form->setAjax(true);
        $form->setAction(PEEP::getRouter()->urlFor('ADS_CTRL_Admin', 'processBannerForm'));
        $form->setAjaxResetOnSuccess(false);
        $form->bindJsFunction(Form::BIND_SUCCESS, "function(data){PEEP.info(data.message);window.adsForm.close();$('#'+data.id).html(data.html);}");

        $multiCheckbox = new CheckboxGroup('banners');
        $multiCheckbox->setOptions($this->banners === null ? array() : $this->banners);
        $form->addElement($multiCheckbox);
        $submit = new Submit('submit');
        $submit->setValue(PEEP::getLanguage()->text('ads', 'banner_position_submit_label'));
        $form->addElement($submit);
        $pluginHidden = new HiddenField('plugin_key');
        $form->addElement($pluginHidden);

        return $form;
    }

    private function getAdsForm( $name )
    {
        $language = PEEP::getLanguage();

        $form = new Form($name);

        $title = new TextField('title');
        $title->setLabel($language->text('ads', 'ads_add_banner_title_label'));
        $title->setRequired(true);
        $form->addElement($title);

        $bannerCode = new Textarea('code');
        $bannerCode->setRequired(true);
        $bannerCode->setLabel($language->text('ads', 'ads_add_banner_code_label'));
        $bannerCode->setDescription($language->text('ads', 'ads_add_banner_code_desc'));
        $form->addElement($bannerCode);

        if ( $this->adsService->getLocationEnabled() )
        {
        $countSelect = new Multiselect('select_country');
        $countSelect->setLabel($language->text('ads', 'ads_add_banner_country_label'));
        $countSelect->setDescription($language->text('ads', 'ads_add_banner_country_desc'));
        $countSelect->setOptions(BOL_GeolocationService::getInstance()->getAllCountryNameListForCC3());
        $form->addElement($countSelect);
        }
        else
        {
            $this->assign('locDisabled', true);
        }

        $submit = new Submit('submit');
        $submit->setValue($language->text('ads', 'ads_add_banner_submit_label'));
        $form->addElement($submit);

        return $form;
    }
}
