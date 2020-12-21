<?php

class PEEP_MasterPage extends PEEP_Renderable
{
    /*
     * List of default master page templates.
     */
    //const TEMPLATE_HTML_DOCUMENT = 'template';
    const TEMPLATE_GENERAL = 'site';
    const TEMPLATE_BLANK = 'blank';
    const TEMPLATE_ADMIN = 'adminboard';
    const TEMPLATE_SIGN_IN = 'sign_in';
    const TEMPLATE_INDEX = 'index-landing';

    /**
     * @var array
     */
    protected $menus;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    /**
     * Adds menu components to master page object.
     * 
     * @param string $name
     * @param BASE_CMP_Menu $menu Adds
     */
    public function addMenu( $name, BASE_CMP_Menu $menu )
    {
        $this->menus[$name] = $menu;
    }

    /**
     * Returns master page menu components.
     *
     * @param string $name
     * @return BASE_CMP_Menu
     */
    public function getMenu( $name )
    {
        if ( isset($this->menus[$name]) )
        {
            return $this->menus[$name];
        }

        return null;
    }

    /**
     * @param string $name
     */
    public function deleteMenu( $name )
    {
        if ( isset($this->menus[$name]) )
        {
            unset($this->menus[$name]);
        }
    }

    /**
     * Master page can't handle forms.
     * 
     * @see PEEP_Renderable::addForm()
     * @param Form $form
     * @throws LogicException
     */
    public function addForm( Form $form )
    {
        throw new LogicException('Cant add form to master page object!');
    }

    /**
     * Master page can't handle forms.
     * 
     * @see PEEP_Renderable::getForm()
     * @param string $name
     * @throws LogicException
     */
    public function getForm( $name )
    {
        throw new LogicException('Master page cant cantain forms!');
    }

    /**
     * Master page init actions. Template assigning, registering standard cmps, etc.
     * Default version works for `general` master page. 
     */
    protected function init()
    {
        // add main menu
        $mainMenu = new BASE_CMP_MainMenu();
        $this->addMenu(BOL_NavigationService::MENU_TYPE_MAIN, $mainMenu);
        $this->addComponent('main_menu', $mainMenu);

        // add bottom menu
        $bottomMenu = new BASE_CMP_BottomMenu();
        $this->addMenu(BOL_NavigationService::MENU_TYPE_BOTTOM, $bottomMenu);
        $this->addComponent('bottom_menu', $bottomMenu);

        // assign image control values
        $currentTheme = PEEP::getThemeManager()->getCurrentTheme()->getDto();
        $values = json_decode(PEEP::getConfig()->getValue('base', 'master_page_theme_info'), true);

        if ( isset($values[$currentTheme->getId()]) )
        {
            $this->assign('imageControlValues', $values[$currentTheme->getId()]);
        }
    }

    public function onBeforeRender()
    {
        if ( $this->getTemplate() === null )
        {
            $this->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate(self::TEMPLATE_GENERAL));
        }

        parent::onBeforeRender();
    }
}