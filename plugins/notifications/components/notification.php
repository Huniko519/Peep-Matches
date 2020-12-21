<?php

class NOTIFICATIONS_CMP_Notification extends PEEP_Component
{
    private $items = array();
    private $userId;
    private $unsubscribeAction;
    private $unsubscribeCode;

    const NL_PLACEHOLDER = '%%%nl%%%';
    const TAB_PLACEHOLDER = '%%%tab%%%';
    const SPACE_PLACEHOLDER = '%%%space%%%';

    public function __construct( $userId )
    {
        parent::__construct();

        $this->userId = $userId;
    }

    public function addItem( $notification )
    {
        $this->items[] = $this->processDataInterface($notification);
        $this->unsubscribeAction = count($this->items) == 1 ? $notification['action'] : 'all';
    }

    private function processDataInterface( $item )
    {
        $data = $item['data'];

        foreach ( array('string', 'conten') as $langProperty )
        {
            if ( !empty($data[$langProperty]) && is_array($data[$langProperty]) )
            {
                $key = explode('+', $data[$langProperty]['key']);
                $vars = empty($data[$langProperty]['vars']) ? array() : $data[$langProperty]['vars'];
                $data[$langProperty] = PEEP::getLanguage()->text($key[0], $key[1], $vars);
            }
        }

        if ( !empty($data['contentImage']) )
        {
            $data['contentImage'] = is_string($data['contentImage'])
                ? array( 'src' => $data['contentImage'] )
                : $data['contentImage'];
        }
        else
        {
            $data['contentImage'] = null;
        }

        $data['content'] = empty($data['content']) ? '' : UTIL_String::truncate($data['content'], 140, '...');
        $data['string'] = empty($data['string']) ? '' : $data['string'];
        $data['avatar'] = empty($data['avatar']) ? null : $data['avatar'];
        $data['contentImage'] = empty($data['contentImage']) ? array() : $data['contentImage'];
        $data['toolbar'] = empty($data['toolbar']) ? array() : $data['toolbar'];
        $data['url'] = empty($data['url']) ? null : $data['url'];
        $data['time'] = $item['time'];

        return $data;
    }

    private function itemsPrepare()
    {
        $out = array();

        foreach ( $this->items as $item )
        {
            $date = getdate($item['time']);
            $timeKey = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
            $out[$timeKey][$item['time']] = $item;
        }

        return $out;
    }

    public function setUnsubscribeCode( $code )
    {
        $this->unsubscribeCode = $code;
    }

    private function getUnsubscribeUrl( $all = false )
    {
        return PEEP::getRouter()->urlForRoute('notifications-unsubscribe', array(
            'code' => $this->unsubscribeCode,
            'action' => $all ? "all" : $this->unsubscribeAction
        ));
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $items = $this->itemsPrepare();

        $this->assign('items', $items);
        $this->assign('userName', BOL_UserService::getInstance()->getDisplayName($this->userId));
        $this->assign('unsubscribeUrl', $this->getUnsubscribeUrl());
        $this->assign('unsubscribeAllUrl', $this->getUnsubscribeUrl(true));

        $single = $this->unsubscribeAction != 'all';
        $this->assign('single', $single);

        $this->assign('settingsUrl', PEEP::getRouter()->urlForRoute('notifications-settings'));
    }

    public function getSubject()
    {
        if ( count($this->items) == 1 )
        {
            $item = reset($this->items);

            return strip_tags($item['string']);
        }

        return PEEP::getLanguage()->text('notifications', 'email_subject');
    }

    public function getHtml()
    {
        $template = PEEP::getPluginManager()->getPlugin('notifications')->getCmpViewDir() . 'notification_html.html';
        $this->setTemplate($template);

        return parent::render();
    }

    public function getTxt()
    {
        $template = PEEP::getPluginManager()->getPlugin('notifications')->getCmpViewDir() . 'notification_txt.html';
        $this->setTemplate($template);

        $this->assign('nl', '%%%nl%%%');
        $this->assign('tab', '%%%tab%%%');
        $this->assign('space', '%%%space%%%');

        $content = parent::render();
        $search = array('%%%nl%%%', '%%%tab%%%', '%%%space%%%');
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }
}