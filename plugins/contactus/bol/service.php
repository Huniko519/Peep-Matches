<?php

class CONTACTUS_BOL_Service
{
    /**
     * Singleton instance.
     *
     * @var CONTACTUS_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CONTACTUS_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {

    }

    public function getDepartmentLabel( $id )
    {
        return PEEP::getLanguage()->text('contactus', $this->getDepartmentKey($id));
    }

    public function addDepartment( $email, $label )
    {
        $contact = new CONTACTUS_BOL_Department();
        $contact->email = $email;
        CONTACTUS_BOL_DepartmentDao::getInstance()->save($contact);

        BOL_LanguageService::getInstance()->addValue(
            PEEP::getLanguage()->getCurrentId(),
            'contactus',
            $this->getDepartmentKey($contact->id),
            trim($label));
    }

    public function deleteDepartment( $id )
    {
        $id = (int) $id;
        if ( $id > 0 )
        {
            $key = BOL_LanguageService::getInstance()->findKey('contactus', $this->getDepartmentKey($id));
            BOL_LanguageService::getInstance()->deleteKey($key->id, true);
            CONTACTUS_BOL_DepartmentDao::getInstance()->deleteById($id);
        }
    }

    private function getDepartmentKey( $name )
    {
        return 'dept_' . trim($name);
    }

    public function getDepartmentList()
    {
        return CONTACTUS_BOL_DepartmentDao::getInstance()->findAll();
    }
}