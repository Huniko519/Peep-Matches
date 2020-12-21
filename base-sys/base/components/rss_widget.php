<?php

require_once PEEP_DIR_LIB . 'rss' . DS . 'rss.php';

class BASE_CMP_RssWidget extends BASE_CLASS_Widget
{
    private $rss = array();

    private $titleOnly = false;

    private static $countInterval = array(1, 10);

    private $count = 5;

    public function __construct( BASE_CLASS_WidgetParameter $param )
    {
        parent::__construct();

        $this->titleOnly = (bool)$param->customParamList['title_only'];
        $this->assign('titleOnly', $this->titleOnly);
        $url = trim($param->customParamList['rss_url']);

        if ( !$url )
        {
            return;
        }

        $cacheKey = 'rss_widget_cache_' . $url;
        $cachedState = PEEP::getCacheService()->get($cacheKey);

        if ( $cachedState === false )
        {
            try
            {
                $rssLoading = PEEP::getConfig()->getValue('base', 'rss_loading');

                if ( !empty($rssLoading) && ( time() - $rssLoading ) < ( 60 * 5 ) )
                {
                    return;
                }
                else if ( $rssLoading === null )
                {
                    PEEP::getConfig()->addConfig('base', 'rss_loading', time());
                }
                else
                {
                    PEEP::getConfig()->saveConfig('base', 'rss_loading', time());
                }

                $rssIterator = RssParcer::getIterator($param->customParamList['rss_url'], self::$countInterval[1]);

                PEEP::getConfig()->saveConfig('base', 'rss_loading', 0);
            }
            catch (Exception $e)
            {
                PEEP::getConfig()->saveConfig('base', 'rss_loading', 0);

                return;
            }

            foreach ( $rssIterator as $item )
            {
                $item->time = strtotime($item->date);
                $this->rss[] = (array) $item;
            }

            try
            {
                PEEP::getCacheService()->set($cacheKey, json_encode($this->rss), 60 * 60);
            }
            catch (Exception $e) {}
        }
        else
        {
            $this->rss = (array) json_decode($cachedState, true);
        }

        $this->count = intval($param->customParamList['item_count']);
    }

    public function render()
    {
        $rss = array_slice($this->rss, 0, $this->count);
        $this->assign('rss', $rss);

        $toolbars = array();
        if ( !$this->titleOnly )
        {
            foreach ( $rss as $key => $item )
            {
                $toolbars[$key] = array(array('label' => UTIL_DateTime::formatDate($item['time'])));
            }
        }
        $this->assign('toolbars', $toolbars);

        return parent::render();
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['rss_url'] = array(
            'presentation' => self::PRESENTATION_TEXT,
            'label' => PEEP::getLanguage()->text('base', 'rss_widget_url_label'),
            'value' => ''
        );

        $settingList['item_count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => PEEP::getLanguage()->text('base', 'rss_widget_count_label'),
            'value' => 5
        );

        for ( $i = self::$countInterval[0]; $i <= self::$countInterval[1]; $i++ )
        {
            $settingList['item_count']['optionList'][$i] = $i;
        }

        $settingList['title_only'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => PEEP::getLanguage()->text('base', 'rss_widget_title_only_label'),
            'value' => false
        );

        return $settingList;
    }

    public static function validateSettingList( $settingList )
    {
        parent::validateSettingList($settingList);

        if ( !UTIL_Validator::isUrlValid($settingList['rss_url']) )
        {
            throw new WidgetSettingValidateException(PEEP::getLanguage()->text('base', 'rss_widget_url_invalid_msg'), 'rss_url');
        }

        $urlInfo = parse_url($settingList['rss_url']);
        $urlHomeInfo = parse_url(PEEP_URL_HOME);

        if ( $urlInfo['host'] == $urlHomeInfo['host'] )
        {
            throw new WidgetSettingValidateException(PEEP::getLanguage()->text('base', 'rss_widget_url_invalid_msg'), 'rss_url');
        }
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('base', 'rss_widget_default_title'),
            self::SETTING_ICON => self::ICON_RSS
        );
    }


}