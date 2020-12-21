<?php

class MAILBOX_CLASS_SearchField extends InvitationFormElement
{
    public $showCloseButton = true;
    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->addAttribute('type', 'text');
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

        if ( $this->getValue() !== null )
        {
            $this->addAttribute('value', $this->value);
        }
        else if ( $this->getHasInvitation() )
        {
            $this->addAttribute('value', $this->invitation);
            $this->addAttribute('class', 'invitation');
        }

        $tag = UTIL_HtmlTag::generateTag('input', $this->attributes);

        if ($this->showCloseButton)
        {
            $tag .= '<a href="javascript://" class="peep_btn_close_search" id="'.$this->attributes['name'].'_close_btn_search"></a>';
        }

        return $tag;
    }

}