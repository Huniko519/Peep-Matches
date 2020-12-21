<?php

class SEARCHSYS_CLASS_ConsoleSearchForm extends Form
{
    public function __construct()
    {
        parent::__construct('console-search-form');

        $this->setAjax(true);

        $lang = PEEP::getLanguage();

        $criteria = new SEARCHSYS_CLASS_SearchField('criteria', $lang->text('searchsys', 'console_search_invitation'));
        $criteria->setRequired(true);
        $this->addElement($criteria);
    }
}