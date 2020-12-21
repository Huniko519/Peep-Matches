<?php

class STORIES_Cron extends PEEP_Cron
{
    const IMAGES_DELETE_LIMIT = 10;

    public function __construct()
    {
        parent::__construct();

        $this->addJob('imagesDeleteProcess', 1);
    }

    public function run()
    {

    }

    public function imagesDeleteProcess()
    {
        $config = PEEP::getConfig();

        // check if uninstall is in progress
        if ( !$config->getValue('stories', 'uninstall_inprogress') )
        {
            return;
        }

        // check if cron queue is not busy
        if ( $config->getValue('stories', 'uninstall_cron_busy') )
        {
            return;
        }

        $config->saveConfig('stories', 'uninstall_cron_busy', 1);

        $mediaPanelService = BOL_MediaPanelService::getInstance();

        $mediaPanelService->deleteImages('stories', self::IMAGES_DELETE_LIMIT);

        $config->saveConfig('stories', 'uninstall_cron_busy', 0);

        if ( !$mediaPanelService->countGalleryImages('stories') )
        {
            $config->saveConfig('stories', 'uninstall_inprogress', 0);
            BOL_PluginService::getInstance()->uninstall('stories');
        }
    }
}