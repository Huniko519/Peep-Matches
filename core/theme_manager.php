<?php

final class PEEP_ThemeManager
{
    const DEFAULT_THEME = 'default';
    const CURRENT_THEME = 'current';

    /**
     * @var BOL_ThemeService
     */
    private $themeService;

    /**
     * @var type
     */
    private $themeObjects = array();

    /**
     * Registered decorators.
     *
     * @var array
     */
    private $decorators;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->decorators = array();
        $this->themeService = BOL_ThemeService::getInstance();
    }
    /**
     * Singleton instance.
     *
     * @var PEEP_ThemeManager
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_ThemeManager
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function initDefaultTheme( $mobile = false )
    {
        $defaultTheme = $this->themeService->getThemeObjectByName(BOL_ThemeService::DEFAULT_THEME, $mobile);
        $this->themeObjects[self::DEFAULT_THEME] = $defaultTheme;
        $this->themeObjects[$defaultTheme->getDto()->getName()] = $defaultTheme;
        $this->themeObjects[self::CURRENT_THEME] = $defaultTheme;
    }

    /**
     * Returns current theme.
     *
     * @return PEEP_Theme
     */
    public function getCurrentTheme()
    {
        return $this->themeObjects[self::CURRENT_THEME];
    }

    /**
     * Returns selected theme name.
     *
     * @return PEEP_Theme
     */
    public function getSelectedTheme()
    {
        $selectedTheme = PEEP::getConfig()->getValue('base', 'selectedTheme');

        if ( empty($this->themeObjects[$selectedTheme]) )
        {
            $this->themeObjects[$selectedTheme] = $this->themeService->getThemeObjectByName(PEEP::getConfig()->getValue('base', 'selectedTheme'));
        }

        return $this->themeObjects[$selectedTheme];
    }

    /**
     * Returns default theme.
     *
     * @return string
     */
    public function getDefaultTheme()
    {
        return $this->themeObjects[self::DEFAULT_THEME];
    }

    /**
     * Sets current theme.
     *
     * @param PEEP_Theme $theme
     */
    public function setCurrentTheme( PEEP_Theme $theme )
    {
        if ( $theme === null )
        {
            return;
        }
        $this->themeObjects[self::CURRENT_THEME] = $theme;
        $this->themeObjects[$theme->getDto()->getName()] = $theme;
    }

    /**
     * @return BOL_ThemeService
     */
    public function getThemeService()
    {
        return $this->themeService;
    }

    /**
     * Adds new decorator.
     * throws InvalidArgumentException
     *
     * @param string $decoratorName
     * @param string $decoratorDir
     */
    public function addDecorator( $decoratorName, $pluginKey )
    {
        $decoratorName = trim(mb_strtolower($decoratorName));

        if ( array_key_exists($decoratorName, $this->decorators) )
        {
            throw new LogicException("Can't add decorator! Decorator `" . $decoratorName . "` already exists!");
        }

        $this->decorators[trim($decoratorName)] = PEEP::getPluginManager()->getPlugin($pluginKey)->getDecoratorDir() . $decoratorName . '.html';
    }

    /**
     *
     * @param string $name
     * @param string $path
     */
    public function addDecoratorPath( $name, $path )
    {
        $this->decorators[trim($name)] = $path;
    }

    /**
     * Returns decorator template path.
     *
     * @param string $decoratorName
     * @return string
     * @throws InvalidArgumentException
     */
    public function getDecorator( $decoratorName )
    {
        $decoratorName = trim(mb_strtolower($decoratorName));

        if ( !array_key_exists($decoratorName, $this->decorators) )
        {
            throw new InvalidArgumentException(" Can't find decorator `'.$decoratorName.'` !");
        }

        if ( $this->themeObjects[self::CURRENT_THEME]->hasDecorator($decoratorName) )
        {
            return $this->themeObjects[self::CURRENT_THEME]->getDecorator($decoratorName);
        }

//        if ( $this->defaultTheme->hasDecorator($decoratorName) )
//        {
//            return $this->defaultTheme->getDecorator($decoratorName);
//        }

        return $this->decorators[$decoratorName];
    }

    /**
     * Returns all decoarators list.
     *
     * @return array
     */
    public function getDecoratorsList()
    {
        return array_keys($this->decorators);
    }

    /**
     * Returns master page template path.
     *
     * @param string $templateName
     * @return string
     */
    public function getMasterPageTemplate( $masterPage )
    {
        $masterPage = trim(mb_strtolower($masterPage));

        if ( $this->themeObjects[self::CURRENT_THEME]->hasMasterPage($masterPage) )
        {
            return $this->themeObjects[self::CURRENT_THEME]->getMasterPage($masterPage);
        }

        if ( $this->themeObjects[self::DEFAULT_THEME]->hasMasterPage($masterPage) )
        {
            return $this->themeObjects[self::DEFAULT_THEME]->getMasterPage($masterPage);
        }

        throw new InvalidArgumentException("Can't find master page `'.$masterPage.'` !");
    }

    /**
     * Returns master page path for provided document key.
     *
     * @param string $documentKey
     * @return string
     */
    public function getDocumentMasterPage( $documentKey )
    {
        $masterPage = null;

        if ( $this->themeObjects[self::DEFAULT_THEME]->hasDocumentMasterPage($documentKey) )
        {
            $masterPage = $this->themeObjects[self::DEFAULT_THEME]->getDocumentMasterPage($documentKey);
        }

        if ( $this->themeObjects[self::CURRENT_THEME]->hasDocumentMasterPage($documentKey) )
        {
            $masterPage = $this->themeObjects[self::CURRENT_THEME]->getDocumentMasterPage($documentKey);
        }

        return ( $masterPage === null ? null : $this->getMasterPageTemplate($masterPage) );
    }

    /**
     * Returns theme images static url.
     *
     * @return string
     */
    public function getThemeImagesUrl()
    {
        return $this->themeObjects[self::CURRENT_THEME]->getStaticImagesUrl();
    }

    /**
     * Renders decorator and returns result markup.
     *
     * @param string $name
     * @param array $params
     * @return string
     */
    public function processDecorator( $name, array $params )
    {
        $viewRenderer = PEEP_ViewRenderer::getInstance();
        $prevVars = $viewRenderer->getAllAssignedVars();
        $viewRenderer->clearAssignedVars();

        if ( isset($params['data']) && is_array($params['data']) )
        {
            foreach ( $params['data'] as $key => $value )
            {
                $params[$key] = $value;
            }
        }

        $params['decoratorName'] = $name;
        $event = new BASE_CLASS_PropertyEvent('base.before_decorator', $params);
        PEEP::getEventManager()->trigger($event);
        $params = $event->getProperties();
        $viewRenderer->assignVar('data', $params);
        $markup = $viewRenderer->renderTemplate($this->getDecorator($name));
        $viewRenderer->clearAssignedVars();
        $viewRenderer->assignVars($prevVars);

        return $markup;
    }

    /**
     * Renders block decorator and returns result markup.
     *
     * @param string $name
     * @param array $params
     * @return string
     */
    public function processBlockDecorator( $name, array $params, $content = '' )
    {
        $viewRenderer = PEEP_ViewRenderer::getInstance();
        $prevVars = $viewRenderer->getAllAssignedVars();
        $viewRenderer->clearAssignedVars();

        // TODO remove hardcode
        switch ( $name )
        {
            case 'box':
                if ( !empty($params['langLabel']) )
                {
                    $tmpArr = explode('+', $params['langLabel']);
                    $params['label'] = PEEP::getLanguage()->text($tmpArr[0], $tmpArr[1]);
                }

                if ( isset($params['href']) )
                {
                    $params['label'] = '<a href="' . $params['href'] . '"' . (isset($params['extraString']) ? ' ' . $params['extraString'] : '' ) . '>' . $params['label'] . '</a>';
                }

                if ( !isset($params['capEnabled']) )
                {
                    $params['capEnabled'] = ( isset($params['label']) || isset($params['capContent']) || isset($params['capAddClass']) );
                }

                if ( empty($params['iconClass']) )
                {
                    $params['iconClass'] = 'peep_ic_file';
                }

                $params['type'] = empty($params['type']) ? '' : '_' . trim($params['type']);
                $params['capAddClass'] = $params['type'] . (empty($params['capAddClass']) ? '' : ' ' . $params['capAddClass']);
                $params['addClass'] = $params['type'] . (empty($params['addClass']) ? '' : ' ' . $params['addClass']) . ( $params['capEnabled'] ? '' : ' peep_no_cap' );
                $params['capContent'] = empty($params['capContent']) ? '' : $params['capContent'];
                $params['style'] = empty($params['style']) ? '' : $params['style'];
                break;
        }

        $params['content'] = $content;
        $params['decoratorName'] = $name;
        $event = new BASE_CLASS_PropertyEvent('base.before_decorator', $params);
        PEEP::getEventManager()->trigger($event);
        $params = $event->getProperties();
        $viewRenderer->assignVar('data', $params);
        $markup = $viewRenderer->renderTemplate(PEEP::getThemeManager()->getDecorator($name));

        $viewRenderer->clearAssignedVars();
        $viewRenderer->assignVars($prevVars);

        return $markup;
    }

    /**
     * Returns theme css file url.
     *
     * @param string $cssFileName
     * @return string
     */
    public function getCssFileUrl( $mobile = false )
    {
        return $this->themeObjects[self::CURRENT_THEME]->getStaticUrl() . ( $mobile ? 'mobile/' : '' ) . BOL_ThemeService::CSS_FILE_NAME;
    }
}
