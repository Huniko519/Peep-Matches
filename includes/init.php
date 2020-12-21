<?php
require_once PEEP_DIR_ROOT . 'includes/config.php';
require_once PEEP_DIR_ROOT . 'includes/define.php';
require_once PEEP_DIR_UTIL . 'debug.php';
require_once PEEP_DIR_UTIL . 'string.php';
require_once PEEP_DIR_CORE . 'autoload.php';
require_once PEEP_DIR_CORE . 'exception.php';
require_once PEEP_DIR_INC . 'function.php';
require_once PEEP_DIR_CORE . 'peep.php';
require_once PEEP_DIR_CORE . 'plugin.php';

mb_internal_encoding('UTF-8');

if ( PEEP_DEBUG_MODE )
{
    ob_start();
}

spl_autoload_register(array('PEEP_Autoload', 'autoload'));

// adding standard package pointers
$autoloader = PEEP::getAutoloader();
$autoloader->addPackagePointer('PEEP', PEEP_DIR_CORE);
$autoloader->addPackagePointer('INC', PEEP_DIR_INC);
$autoloader->addPackagePointer('UTIL', PEEP_DIR_UTIL);
$autoloader->addPackagePointer('BOL', PEEP_DIR_SYSTEM_PLUGIN . 'base' . DS . 'bol');

// Force autoload of classes without package pointer
$classesToAutoload = array(
    'Form' => PEEP_DIR_CORE . 'form.php',
    'TextField' => PEEP_DIR_CORE . 'form_element.php',
    'HiddenField' => PEEP_DIR_CORE . 'form_element.php',
    'FormElement' => PEEP_DIR_CORE . 'form_element.php',
    'RequiredValidator' => PEEP_DIR_CORE . 'validator.php',
    'StringValidator' => PEEP_DIR_CORE . 'validator.php',
    'RegExpValidator' => PEEP_DIR_CORE . 'validator.php',
    'EmailValidator' => PEEP_DIR_CORE . 'validator.php',
    'UrlValidator' => PEEP_DIR_CORE . 'validator.php',
    'AlphaNumericValidator' => PEEP_DIR_CORE . 'validator.php',
    'IntValidator' => PEEP_DIR_CORE . 'validator.php',
    'FloatValidator' => PEEP_DIR_CORE . 'validator.php',
    'DateValidator' => PEEP_DIR_CORE . 'validator.php',
    'CaptchaValidator' => PEEP_DIR_CORE . 'validator.php',
    'RadioField' => PEEP_DIR_CORE . 'form_element.php',
    'CheckboxField' => PEEP_DIR_CORE . 'form_element.php',
    'Selectbox' => PEEP_DIR_CORE . 'form_element.php',
    'CheckboxGroup' => PEEP_DIR_CORE . 'form_element.php',
    'RadioField' => PEEP_DIR_CORE . 'form_element.php',
    'PasswordField' => PEEP_DIR_CORE . 'form_element.php',
    'Submit' => PEEP_DIR_CORE . 'form_element.php',
    'Button' => PEEP_DIR_CORE . 'form_element.php',
    'Textarea' => PEEP_DIR_CORE . 'form_element.php',
    'FileField' => PEEP_DIR_CORE . 'form_element.php',
    'TagsField' => PEEP_DIR_CORE . 'form_element.php',
    'SuggestField' => PEEP_DIR_CORE . 'form_element.php',
    'MultiFileField' => PEEP_DIR_CORE . 'form_element.php',
    'Multiselect' => PEEP_DIR_CORE . 'form_element.php',
    'CaptchaField' => PEEP_DIR_CORE . 'form_element.php',
    'InvitationFormElement' => PEEP_DIR_CORE . 'form_element.php',
    'Range' => PEEP_DIR_CORE . 'form_element.php',
    'WyswygRequiredValidator' => PEEP_DIR_CORE . 'validator.php',
    'DateField' => PEEP_DIR_CORE . 'form_element.php',
    'DateRangeInterface' => PEEP_DIR_CORE . 'form_element.php'
);

PEEP::getAutoloader()->addClassArray($classesToAutoload);

if ( defined("PEEP_URL_HOME") )
{
    PEEP::getRouter()->setBaseUrl(PEEP_URL_HOME);
}

if ( PEEP_PROFILER_ENABLE )
{
    UTIL_Profiler::getInstance();
}

require_once PEEP_DIR_SYSTEM_PLUGIN . 'base' . DS . 'classes' . DS . 'file_log_writer.php';
require_once PEEP_DIR_SYSTEM_PLUGIN . 'base' . DS . 'classes' . DS . 'db_log_writer.php';
require_once PEEP_DIR_SYSTEM_PLUGIN . 'base' . DS . 'classes' . DS . 'err_output.php';

$errorManager = PEEP_ErrorManager::getInstance(PEEP_DEBUG_MODE);
$errorManager->setErrorOutput(new BASE_CLASS_ErrOutput());
