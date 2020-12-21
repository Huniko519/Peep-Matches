<?php
$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langId = null;

foreach ($languages as $lang)
{
    if ($lang->tag == 'en')
    {
        $langId = $lang->id;
        break;
    }
}

if ($langId !== null)
{
    $languageService->addOrUpdateValue($langId, 'photo', 'cnews_album', 'Contacts News Photos');
    
}