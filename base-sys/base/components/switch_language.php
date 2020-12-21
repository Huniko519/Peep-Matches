<?php

class BASE_CMP_SwitchLanguage extends PEEP_Component
{
    /**
     * Constructor.
     *
     */
    public function __construct($languages)
    {
        parent::__construct();

        $this->assign('languages', $languages);

    }

}
