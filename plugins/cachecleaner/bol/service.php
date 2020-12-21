<?php

final class CACHECLEANER_BOL_Service {
	/**
	 *
	 */
	protected static $instance = null;

	public static function getInstance() {
		if ( self::$instance==null ) {
			self::$instance = new CACHECLEANER_BOL_Service();
		}

		return self::$instance;
	}

	public function processCleanUp() {
		$configs = PEEP::getConfig()->getValues( 'cachecleaner' );

		//clean template cache
		if ($configs['template_cache']) {
			PEEP_ViewRenderer::getInstance()->clearCompiledTpl();
		}

		//clean db backend cache
		if ($configs['backend_cache']) {
			PEEP::getCacheManager()->clean(array(),PEEP_CacheManager::CLEAN_ALL);
		}

		//clean themes static contents cache
		if ($configs['theme_static']) {
			PEEP::getThemeManager()->getThemeService()->processAllThemes();
		}

		//clean plugins static contents cache
		if ($configs['plugin_static']) {
			$pluginService = BOL_PluginService::getInstance();
            $activePlugins = $pluginService->findActivePlugins();

            /* @var $pluginDto BOL_Plugin */
            foreach ( $activePlugins as $pluginDto )
            {
                $pluginStaticDir = PEEP_DIR_PLUGIN . $pluginDto->getModule() . DS . 'static' . DS;

                if ( file_exists($pluginStaticDir) )
                {
                    $staticDir = PEEP_DIR_STATIC_PLUGIN . $pluginDto->getModule() . DS;

                    if ( file_exists($staticDir) )
                    {
                    	UTIL_File::removeDir($staticDir);
                    }
                    mkdir($staticDir);
                    chmod($staticDir, 0777);

                    UTIL_File::copyDir($pluginStaticDir, $staticDir);
                }
            }
		}		
	}
}