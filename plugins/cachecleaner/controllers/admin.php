<?php

class CACHECLEANER_CTRL_Admin extends ADMIN_CTRL_Abstract {

	function index() {
		$language = PEEP::getLanguage();

        $mainItem = new BASE_MenuItem();
        $mainItem->setLabel( $language->text( 'cachecleaner', 'adm_menu_config' ) );
        $mainItem->setUrl( PEEP::getRouter()->urlForRoute( 'cachecleaner.admin' ) );
        $mainItem->setKey( 'general' );
        $mainItem->setIconClass( 'peep_ic_gear_wheel' );
        $mainItem->setOrder( 0 );


        $menu = new BASE_CMP_ContentMenu( array( $mainItem ) );
        $this->addComponent( 'menu', $menu );

        $configs = PEEP::getConfig()->getValues( 'cachecleaner' );

        $cacheControlForm = new CacheControlForm();

        $this->addForm( $cacheControlForm );

        if ( PEEP::getRequest()->isPost() && $cacheControlForm->isValid( $_POST ) ) {
            $res = $cacheControlForm->process();

            CACHECLEANER_BOL_Service::getInstance()->processCleanUp();

            PEEP::getFeedback()->info( $language->text( 'cachecleaner', 'settings_updated' ) );
            $this->redirect( PEEP::getRouter()->urlForRoute( 'cachecleaner.admin' ) );
        }

        if ( !PEEP::getRequest()->isAjax() ) {
            $this->setPageHeading( PEEP::getLanguage()->text( 'cachecleaner', 'admin_heading' ) );
            $this->setPageHeadingIconClass( 'peep_ic_gear_wheel' );

            $menu->deactivateElements();
            $elem = $menu->getElement( 'general' );
            if ( $elem ) {
                $elem->setActive( true );
            }
        }

        $cacheControlForm->getElement( 'templateCache' )->setValue( $configs['template_cache'] );
        $cacheControlForm->getElement( 'backendCache' )->setValue( $configs['backend_cache'] );
        $cacheControlForm->getElement( 'themeStatic' )->setValue( $configs['theme_static'] );
        $cacheControlForm->getElement( 'pluginStatic' )->setValue( $configs['plugin_static'] );
	}

	function about() {
		
		$language = PEEP::getLanguage();
		$mainItem = new BASE_MenuItem();
        $mainItem->setLabel( $language->text( 'cachecleaner', 'adm_menu_config' ) );
        $mainItem->setUrl( PEEP::getRouter()->urlForRoute( 'cachecleaner.admin' ) );
        $mainItem->setKey( 'general' );
        $mainItem->setIconClass( 'peep_ic_gear_wheel' );
        $mainItem->setOrder( 0 );

       

        $this->setPageHeading( PEEP::getLanguage()->text( 'cachecleaner', 'admin_heading' ) );
        $this->setPageHeadingIconClass( 'peep_ic_help' );

        $menu = new BASE_CMP_ContentMenu( array( $mainItem ) );
        $this->addComponent( 'menu', $menu );

        
	}
}

class CacheControlForm extends Form
{

    /**
     * Class constructor
     *
     */
    public function __construct() {
        parent::__construct( 'cacheControlForm' );

        $language = PEEP::getLanguage();

        // template cache control
        $templateCacheField = new CheckboxField( 'templateCache' );
        $this->addElement( $templateCacheField->setLabel( $language->text( 'cachecleaner', 'opTemplateCache' ) ) );

        // backend cache control
        $backendCacheField = new CheckboxField( 'backendCache' );
        $this->addElement( $backendCacheField->setLabel( $language->text( 'cachecleaner', 'opBackendCache' ) ) );

        // themes static cache control
        $themeStaticField = new CheckboxField( 'themeStatic' );
        $this->addElement( $themeStaticField->setLabel( $language->text( 'cachecleaner', 'opThemeStatic' ) ) );

        // plugin static cache control
        $pluginStaticField = new CheckboxField( 'pluginStatic' );
        $this->addElement( $pluginStaticField->setLabel( $language->text( 'cachecleaner', 'opPluginStatic' ) ) );

        // submit
        $submit = new Submit( 'clean' );
        $submit->setValue( $language->text( 'cachecleaner', 'btn_clean' ) );
        $this->addElement( $submit );
    }

    public function process() {
        $values = $this->getValues();

        $config = PEEP::getConfig();

        $config->saveConfig( 'cachecleaner', 'template_cache', $values['templateCache'] );
        $config->saveConfig( 'cachecleaner', 'backend_cache', $values['backendCache'] );
        $config->saveConfig( 'cachecleaner', 'theme_static', $values['themeStatic'] );
        $config->saveConfig( 'cachecleaner', 'plugin_static', $values['pluginStatic'] );

        return array( 'result' => true );
    }
}
