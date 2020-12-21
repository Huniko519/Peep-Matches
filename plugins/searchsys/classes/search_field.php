<?php

class SEARCHSYS_CLASS_SearchField extends FormElement
{
    protected $data = array();

    protected $classAttr = array();

    protected $invitation;

    protected $groups;
    
    protected $staticInitComplete = false;

    protected $groupDefaults = array(
        'priority' => 0,
        'alwaysVisible' => true,
        'noMatchMessage' => false
    );

    /**
     * @var SEARCHSYS_BOL_Service
     */
    private $service;

    /**
     * Constructor.
     *
     * @param string $name
     * @param null $invitation
     */
    public function __construct( $name, $invitation = null )
    {
        parent::__construct($name);

        if ( !empty($invitation) )
        {
            $this->setInvitation($invitation);
        }
        
        $this->addClass('mc-user-select');
        $this->addClass('jhtmlarea');

        $this->service = SEARCHSYS_BOL_Service::getInstance();
    }

    public function setData( $data )
    {
        $this->data = $data;
    }
    
    public function setInvitation( $invitation )
    {
        $this->invitation = $invitation;
    }

    public function addClass( $class )
    {
        $this->classAttr[] = $class;
    }

    public function setupGroup( $group, $settings = array() )
    {
        $this->groups[$group] = isset($this->groups[$group])
                ? $this->groups[$group]
                : $this->groupDefaults;

        $this->groups[$group] = array_merge($this->groups[$group], $settings);
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        $staticUrl = PEEP::getPluginManager()->getPlugin('searchsys')->getStaticUrl();

        PEEP::getDocument()->addStyleSheet($staticUrl . 'select2.css?15');
        PEEP::getDocument()->addScript($staticUrl . 'select2.js?15');
        PEEP::getDocument()->addStyleSheet($staticUrl . 'style.css?15');
        PEEP::getDocument()->addScript($staticUrl . 'script.js?18');

        $this->addAttribute('type', 'hidden');

        return UTIL_HtmlTag::generateTag('input', $this->attributes)
        . '<div class="us-field-fake"><input type="text" class="peep_text invitation" value="' . $this->invitation . '" /></div>';
    }

    public function getElementJs()
    {
        $options = array(
            "multiple" => true,
            "width" => "copy",
            "allowClear" => false,
            "containerCssClass" => implode(' ', $this->classAttr),
            "dropdownCssClass" => 'peep_bg_color peep_border us_dropdown peep_small',
            "placeholder" => $this->invitation,
            "minimumInputLength" => 2,
            "maximumSelectionSize" => 1
        );

        $settings = array();
        $settings['rspUrl'] = PEEP::getRouter()->urlFor('SEARCHSYS_CTRL_Search', 'rsp');
        $settings['viewAllUrl'] = PEEP::getRouter()->urlForRoute('searchsys.search-result');
        $settings['groups'] = $this->groups;
        $settings['groupDefaults'] = $this->groupDefaults;

        PEEP::getLanguage()->addKeyForJs('searchsys', 'selector_searching');
        PEEP::getLanguage()->addKeyForJs('searchsys', 'selector_no_matches');
        PEEP::getLanguage()->addKeyForJs('searchsys', 'view_all_results');
        PEEP::getLanguage()->addKeyForJs('searchsys', 'input_too_short');

        $js = UTIL_JsGenerator::newInstance();
        $js->addScript('var formElement = new SEARCHSYS.UserSelectorFormElement({$id}, {$name});', array(
            'name' => $this->getName(),
            'id' => $this->getId()
        ));
        
        $js->addScript('formElement.init("#" + {$id}, {$settings}, {$options}, {$data});', array(
            'id' => $this->getId(),
            'settings' => $settings,
            'options' => $options,
            'data' => $this->data
        ));
        
        if ( !empty($this->value) ) 
        {
            $js->callFunction(array('formElement', 'setValue'), array($this->value));
        }
        
        /** @var $value PEEP_Validator  */
        foreach ( $this->validators as $value )
        {
            $js->addScript("formElement.addValidator(" . $value->getJsValidator() . ");");
        }

        $this->staticInitComplete = true;
        
        return $js->generateJs();
    }
}