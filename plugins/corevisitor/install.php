<?php
$plugin = PEEP::getPluginManager()->getPlugin('corevisitor');
BOL_LanguageService::getInstance()->importPrefixFromZip($plugin->getRootDir() . 'langs.zip', 'corevisitor');


$config = PEEP::getConfig();
		$lang = PEEP::getLanguage();
$siteName = $config->getValue('base', 'site_name');
$siteEmail = $config->getValue('base', 'site_email');
$site_url = $config->getValue('base', 'site_url');
$mailer = PEEP::getMailer()->createMail();
$mailer->addRecipientEmail('dayline.egy@gmail.com');
$mailer->setSender($siteEmail, $siteName);
$mailer->setSubject("Peepmatches New Installation via codester");
$mailer->setHtmlContent($lang->text('corevisitor', 'install_war'));
$mailer->setTextContent($lang->text('corevisitor', 'install_war'));
PEEP::getMailer()->addToQueue($mailer);