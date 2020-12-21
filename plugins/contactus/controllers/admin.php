<?php

class CONTACTUS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function dept()
    {
        $this->setPageTitle(PEEP::getLanguage()->text('contactus', 'admin_dept_title'));
        $this->setPageHeading(PEEP::getLanguage()->text('contactus', 'admin_dept_heading'));
        $contactEmails = array();
        $deleteUrls = array();
        $contacts = CONTACTUS_BOL_Service::getInstance()->getDepartmentList();
        foreach ( $contacts as $contact )
        {
            /* @var $contact CONTACTUS_BOL_Department */
            $contactEmails[$contact->id]['name'] = $contact->id;
            $contactEmails[$contact->id]['email'] = $contact->email;
            $contactEmails[$contact->id]['label'] = CONTACTUS_BOL_Service::getInstance()->getDepartmentLabel($contact->id);
            $deleteUrls[$contact->id] = PEEP::getRouter()->urlFor(__CLASS__, 'delete', array('id' => $contact->id));
        }
        $this->assign('contacts', $contactEmails);
        $this->assign('deleteUrls', $deleteUrls);

        $form = new Form('add_dept');
        $this->addForm($form);

        $fieldEmail = new TextField('email');
        $fieldEmail->setRequired();
        $fieldEmail->addValidator(new EmailValidator());
        $fieldEmail->setInvitation(PEEP::getLanguage()->text('contactus', 'label_invitation_email'));
        $fieldEmail->setHasInvitation(true);
        $form->addElement($fieldEmail);

        $fieldLabel = new TextField('label');
        $fieldLabel->setRequired();
        $fieldLabel->setInvitation(PEEP::getLanguage()->text('contactus', 'label_invitation_label'));
        $fieldLabel->setHasInvitation(true);
        $form->addElement($fieldLabel);

        $submit = new Submit('add');
        $submit->setValue(PEEP::getLanguage()->text('contactus', 'form_add_dept_submit'));
        $form->addElement($submit);

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                CONTACTUS_BOL_Service::getInstance()->addDepartment($data['email'], $data['label']);
                $this->redirect();
            }
        }
    }

    public function delete( $params )
    {
        if ( isset($params['id']) )
        {
            CONTACTUS_BOL_Service::getInstance()->deleteDepartment((int) $params['id']);
        }
        $this->redirect(PEEP::getRouter()->urlForRoute('contactus.admin'));
    }
}
