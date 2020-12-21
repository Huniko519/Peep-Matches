<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CTRL_RestrictedUsernames extends ADMIN_CTRL_Abstract
{
    private $userService;
    private $ajaxResponderUrl;

    public function __construct()
    {
        $this->userService = BOL_UserService::getInstance();

        $this->ajaxResponderUrl = PEEP::getRouter()->urlFor("ADMIN_CTRL_RestrictedUsernames", "ajaxResponder");

        parent::__construct();
    }

    public function index( $params = array() )
    {
        $userService = BOL_UserService::getInstance();

        $language = PEEP::getLanguage();

        $this->setPageHeading($language->text('admin', 'restrictedusernames'));

        $this->setPageHeadingIconClass('peep_ic_script');

        $restrictedUsernamesForm = new Form('restrictedUsernamesForm');
        $restrictedUsernamesForm->setId('restrictedUsernamesForm');

        $username = new TextField('restrictedUsername');
        $username->addAttribute('class', 'peep_text');
        $username->addAttribute('style', 'width: auto;');
        $username->setRequired();
        $username->setLabel($language->text('admin', 'restrictedusernames_username_label'));

        $restrictedUsernamesForm->addElement($username);

        $submit = new Submit('addUsername');
        $submit->addAttribute('class', 'peep_button');
        $submit->setValue($language->text('admin', 'restrictedusernames_add_username_button'));

        $restrictedUsernamesForm->addElement($submit);

        $this->addForm($restrictedUsernamesForm);

        $this->assign('restricted_list', $this->userService->getRestrictedUsernameList());

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $restrictedUsernamesForm->isValid($_POST) )
            {
                $data = $restrictedUsernamesForm->getValues();

                $username = $this->userService->getRestrictedUsername($data['restrictedUsername']);

                if ( empty($username) )
                {
                    $username = new BOL_RestrictedUsernames();

                    $username->setRestrictedUsername($data['restrictedUsername']);

                    $this->userService->addRestrictedUsername($username);

                    PEEP::getFeedback()->info($language->text('admin', 'restrictedusernames_username_added'));
                    $this->redirect();
                }
                else
                {
                    PEEP::getFeedback()->warning($language->text('admin', 'restrictedusernames_username_already_exists'));
                }
            }
        }
    }

    public function delete()
    {
        $restrictedUsernamesService = BOL_RestrictedUsernamesDao::getInstance();
        $restrictedUsernamesService->deleteRestrictedUsername($_GET['username']);

        $language = PEEP::getLanguage();
        PEEP::getFeedback()->info($language->text('admin', 'restrictedusernames_username_deleted'));

        $this->redirect('admin/restricted-usernames');
    }
}
