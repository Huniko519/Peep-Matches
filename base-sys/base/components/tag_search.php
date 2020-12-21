<?php

class BASE_CMP_TagSearch extends PEEP_Component
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $routeName;

    /**
     * Constructor.
     * 
     * @param string $entityType
     * @param string $url
     */
    public function __construct( $url = null )
    {
        parent::__construct();
        $this->url = $url;
    }

    /**
     * Sets route name for url generation. 
     * Route should be added to router and contain var - `tag`.
     * 
     * @param $routeName
     * @return BASE_CMP_TagSearch
     */
    public function setRouteName( $routeName )
    {
        $this->routeName = trim($routeName);
    }

    /**
     * @see PEEP_Renderable::onBeforeRender 
     */
    public function onBeforeRender()
    {
        $randId = rand(1, 100000);
        $formId = 'tag_search_form_' . $randId;
        $elId = 'tag_search_input_' . $randId;

        $this->assign('form_id', $formId);
        $this->assign('el_id', $elId);

        $urlToRedirect = ($this->routeName === null) ? PEEP::getRequest()->buildUrlQueryString($this->url, array('tag' => '_tag_')) : PEEP::getRouter()->urlForRoute($this->routeName, array('tag' => '#tag#'));

        $script = "
			var tsVar" . $randId . " = '" . $urlToRedirect . "';
			
			$('#" . $formId . "').bind( 'submit', 
				function(){
					if( !$.trim( $('#" . $elId . "').val() ) )
					{
						PEEP.error(".  json_encode(PEEP::getLanguage()->text('base', 'tag_search_empty_value_error')).");
					}
					else
					{
						window.location = tsVar" . $randId . ".replace(/_tag_/, $('#" . $elId . "').val());
					}

					return false;  
				}
			);
		";

        PEEP::getDocument()->addOnloadScript($script);
    }
}