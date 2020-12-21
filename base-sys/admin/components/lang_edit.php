<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CMP_LangEdit extends PEEP_Component
{
    /**
     * BOL_LanguageService
     */
    private $service;

    /**
     * Constructor.
     * 
     * @param array $itemsList
     */
    public function __construct( $langId )
    {
        parent::__construct();
        $this->service = BOL_LanguageService::getInstance();

        if ( empty($langId) )
        {
            $this->setVisible(false);
            return;
        }

        $languageDto = $this->service->findById($langId);

        if ( $languageDto === null )
        {
            $this->setVisible(false);
            return;
        }

        $language = PEEP::getLanguage();

        $form = new Form('lang_edit');
        $form->setAjax();
        $form->setAction(PEEP::getRouter()->urlFor('ADMIN_CTRL_Languages', 'langEditFormResponder'));
        $form->setAjaxResetOnSuccess(false);

        $labelTextField = new TextField('label');
        $labelTextField->setLabel($language->text('admin', 'clone_form_lbl_label'));
        $labelTextField->setDescription($language->text('admin', 'clone_form_descr_label'));
        $labelTextField->setRequired();
        $labelTextField->setValue($languageDto->getLabel());
        $form->addElement($labelTextField);

        $tagTextField = new TextField('tag');
        $tagTextField->setLabel($language->text('admin', 'clone_form_lbl_tag'));
        $tagTextField->setDescription($language->text('admin', 'clone_form_descr_tag'));
        $tagTextField->setRequired();
        $tagTextField->setValue($languageDto->getTag());

        if ( $languageDto->getTag() == 'en' )
        {
            $tagTextField->addAttribute('disabled', 'disabled');
        }
        
        $form->addElement($tagTextField);

        $rtl = new CheckboxField('rtl');
        $rtl->setLabel($language->text('admin', 'lang_edit_form_rtl_label'));
        $rtl->setDescription($language->text('admin', 'lang_edit_form_rtl_desc'));
        $rtl->setValue((bool) $languageDto->getRtl());
        $form->addElement($rtl);

        $hiddenField = new HiddenField('langId');
        $hiddenField->setValue($languageDto->getId());
        $form->addElement($hiddenField);

        $submit = new Submit('submit');
        $submit->setValue($language->text('admin', 'btn_label_edit'));
        $form->addElement($submit);

        $form->bindJsFunction(Form::BIND_SUCCESS, "function(data){if(data.result){PEEP.info(data.message);setTimeout(function(){window.location.reload();}, 1000);}else{PEEP.error(data.message);}}");

        $this->addForm($form);
    }
}