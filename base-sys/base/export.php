<?php

class BASE_Export extends DATAEXPORTER_CLASS_Export
{
    public $configs = array();

    public function excludeTableList()
    {
        return array(
            PEEP_DB_PREFIX . 'base_config',
            PEEP_DB_PREFIX . 'base_theme',
            PEEP_DB_PREFIX . 'base_plugin',
            PEEP_DB_PREFIX . 'base_theme_control',
            PEEP_DB_PREFIX . 'base_theme_control_value',
            PEEP_DB_PREFIX . 'base_component_place_cache'
        );
    }

    public function includeTableList()
    {
        return array();
    }

    public function export( $params )
    {
        /* @var $za ZipArchives */
        $za = $params['zipArchive'];
        $archiveDir = $params['archiveDir'];

        // theme
        $this->exportThemes($za, $archiveDir);


        // configs
        $this->exportConfigs($za, $archiveDir);

        $this->configs['media_panel_url'] = PEEP::getStorage()->getFileUrl(PEEP::getPluginManager()->getPlugin('base')->getUserFilesDir());

        $string = json_encode($this->configs);
        $za->addFromString($archiveDir . '/' . 'config.txt', $string);
    }

    private function exportConfigs( ZipArchive $za, $archiveDir )
    {
        $this->configs['avatarUrl'] = PEEP::getStorage()->getFileUrl(BOL_AvatarService::getInstance()->getAvatarsDir());

        $tableName = PEEP::getDbo()->escapeString(str_replace(PEEP_DB_PREFIX, '%%TBL-PREFIX%%', BOL_ConfigDao::getInstance()->getTableName()));

        $query = " SELECT `key`, `name`, `value`, `description` FROM " . BOL_ConfigDao::getInstance()->getTableName() . " WHERE name NOT IN ( 'maintenance', 'update_soft', 'site_installed', 'soft_build', 'soft_version' )
                    AND `key` NOT IN ( 'dataimporter', 'dataexporter' ) ";

        $sql = DATAEXPORTER_BOL_ExportService::getInstance()->exportTableToSql(PEEP_DB_PREFIX . 'base_config', false, false, true, $query);

        $za->addFromString($archiveDir . '/configs.sql', $sql);
    }

    private function exportThemes( ZipArchive $za, $archiveDir )
    {
        $currentTheme = PEEP::getThemeManager()->getSelectedTheme()->getDto();
        $currentThemeDir = PEEP::getThemeManager()->getSelectedTheme()->getRootDir();
        $currentThemeUserfilesDir = PEEP_DIR_THEME_USERFILES;

        $this->configs['currentTheme'] = array(
            'name' => $currentTheme->name,
            'customCss' => $currentTheme->customCss,
            'customCssFileName' => $currentTheme->customCssFileName,
            'description' => $currentTheme->description,
            'isActive' => $currentTheme->isActive,
            'sidebarPosition' => $currentTheme->sidebarPosition,
            'title' => $currentTheme->title
        );

        $controlValueList = PEEP::getDbo()->queryForList(" SELECT * FROM " . BOL_ThemeControlValueDao::getInstance()->getTableName() . " WHERE themeId = :themeId ", array('themeId' => $currentTheme->id));

        foreach ( $controlValueList as $controlValue )
        {
            $this->configs['controlValue'][$controlValue['themeControlKey']] = $controlValue['value'];
        }

        $za->addEmptyDir($archiveDir . '/' . $currentTheme->getName());
        $this->zipFolder($za, $currentThemeDir, $archiveDir . '/' . $currentTheme->getName() . '/');

        $themesDir = Peep::getPluginManager()->getPlugin('dataexporter')->getPluginFilesDir(). 'themes' . DS;

        UTIL_File::copyDir(PEEP_DIR_THEME_USERFILES, $themesDir);

        $fileList = Peep::getStorage()->getFileNameList(PEEP_DIR_THEME_USERFILES);
        
        mkdir($themesDir, 0777);
        
        foreach($fileList as $file)
        {
            if ( Peep::getStorage()->isFile($file) )
            {
                Peep::getStorage()->copyFileToLocalFS($file, $themesDir . mb_substr($file, mb_strlen(PEEP_DIR_THEME_USERFILES)));
            }
        }
        
        $za->addEmptyDir($archiveDir . '/themes');
        
        $this->zipFolder($za, $themesDir, $archiveDir . '/themes/');
    }

    private function zipFolder( ZipArchive $zipArchive, $localDir, $archiveDir )
    {
        if ( $handle = opendir($localDir) )
        {
            while ( false !== ($file = readdir($handle)) )
            {
                if ( is_file($localDir . $file) )
                {
                    $zipArchive->addFile($localDir . $file, $archiveDir . $file);
                }
                elseif ( $file != '.' and $file != '..' and is_dir($localDir . $file) )
                {
                    $zipArchive->addEmptyDir($archiveDir . $file);
                    $this->zipFolder($zipArchive, $localDir . $file . DS, $archiveDir . $file . '/');
                }
            }
        }
        closedir($handle);
    }
}

?>
