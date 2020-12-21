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
PEEP::getNavigation()->addMenuItem(PEEP_Navigation::MAIN, 'view_photo_list', 'photo', 'page_title_browse_photos', PEEP_Navigation::VISIBLE_FOR_ALL);
PEEP::getNavigation()->deleteMenuItem('photo', 'photo');
if ($langId !== null)
{
    $languageService->addOrUpdateValue($langId, 'photo', 'dnd_not_support', 'Browse photos to upload');
    $languageService->addOrUpdateValue($langId, 'photo', 'dnd_support', 'Or drag and drop here');
    $languageService->addOrUpdateValue($langId, 'photo', 'accepted_types', 'Accepted types : Jpeg | Png | Gif .');
    $languageService->addOrUpdateValue($langId, 'photo', 'choose_existing_or_create', 'Choose or create album');
    $languageService->addOrUpdateValue($langId, 'photo', 'menu_latest', 'Recent');
    $languageService->addOrUpdateValue($langId, 'photo', 'menu_toprated', 'Popular');
    $languageService->addOrUpdateValue($langId, 'photo', 'menu_most_discussed', 'Most Commented');
    $languageService->addOrUpdateValue($langId, 'photo', 'search_invitation', 'Type @user for member photos');
    $languageService->addOrUpdateValue($langId, 'photo', 'menu_explore', 'Browse All');
    $languageService->addOrUpdateValue($langId, 'photo', 'uploaded_by', 'Uploaded By');
    $languageService->addOrUpdateValue($langId, 'photo', 'album', 'In Album');
    
}

Updater::getLanguageService()->importPrefixFromZip(__DIR__ . DS . 'langs.zip', 'photo');

$sqls = array(
    'ALTER TABLE `' . PEEP_DB_PREFIX . 'photo_album` ADD INDEX (`entityType`, `entityId`);'
);

foreach ( $sqls as $sql )
{
    try
    {
        Updater::getDbo()->query($sql);
    }
    catch ( Exception $e )
    {
        Updater::getLogger()->addEntry(json_encode($e));
    }
}
