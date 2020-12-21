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
    $languageService->addOrUpdateValue($langId, 'googlelocation', 'map_page_heading', 'Show On Map');
    $languageService->addOrUpdateValue($langId, 'googlelocation', 'users_map_menu_item', 'Show On Map');
    $languageService->addOrUpdateValue($langId, 'googlelocation', 'url_back', 'Back to members list');
    
}