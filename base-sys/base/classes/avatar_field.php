<?php

class BASE_CLASS_AvatarField extends FormElement
{
    /**
     * @param string $name
     */
    public function __construct( $name, $changeUserAvatar = true )
    {
        parent::__construct($name);

        $this->changeUserAvatar = $changeUserAvatar;
        $this->addAttribute('type', 'file');
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

        $deleteLabel = PEEP::getLanguage()->text('base', 'delete');

        if ( $this->value )
        {
            // hide the input
            $this->attributes = array_merge($this->attributes, array(
                'style' => 'display:none'
            ));
        }

        $markup = '<div class="peep_avatar_field">';
        $markup .= UTIL_HtmlTag::generateTag('input', $this->attributes);

        if ( !$this->value )
        {
            $markup .= '<div class="peep_avatar_field_preview" style="display: none;"><img src="" alt="" /><span title="'.$deleteLabel.'"></span></div>';
        }
        else 
        {
            $markup .= '<div class="peep_avatar_field_preview" style="display: block;"><img src="' . $this->value . '" alt="" /><span title="'.$deleteLabel.'"></span></div>';            
            $markup .= '<input type="hidden" id="' . $this->getId() . '_preload_avatar" name="avatarPreloaded" value="1" />';
        }

        $markup .= '<input type="hidden" name="" value="' . $this->value . '" class="peep_avatar_field_value" />';
        $markup .= '</div>';

        return $markup;
    }

    public function getElementJs()
    {
        $params = array(
            'ajaxResponder' => PEEP::getRouter()->urlFor('BASE_CTRL_Avatar', 'ajaxResponder'),
            'changeUserAvatar' => $this->changeUserAvatar
        );
        $jsString = "var formElement = new PeepAvatarField(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ", ".json_encode($params).");";

        /** @var $value PEEP_Validator  */
        foreach ( $this->validators as $value )
        {
            $jsString .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

        $jsString .= "
			formElement.getValue = function(){

                var value = $(this.input).closest('.peep_avatar_field').find('.peep_avatar_field_value').val();

		        return value;
			};

			formElement.resetValue = function(){
                $(this.input).closest('.peep_avatar_field').find('.peep_avatar_field_value').val('');
            };

			formElement.setValue = function(value){
			    $(this.input).closest('.peep_avatar_field').find('.peep_avatar_field_value').val(value);
			};
		";

        return $jsString;
    }
}