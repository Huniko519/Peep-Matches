<?php

class CNEWS_FORMAT_ImageList extends CNEWS_CLASS_Format
{
    const LIST_LIMIT = 4;

    protected $list = array();

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $defaults = array(
            "iconClass" => null,
            "title" => '',
            "description" => '',
            "status" => null,
            "list" => null,
            "info" => null,
            "more" => null
        );

        $this->vars = array_merge($defaults, $this->vars);

        if ( empty($this->vars['list']) )
        {
            $this->setVisible(false);
            return;
        }

        // prepare image list
        foreach ( $this->vars['list'] as $id => $image )
        {
            $image['url'] = $this->getUrl($image['url']);
            $this->list[$id] = $image;
        }

        $limit = self::LIST_LIMIT;

        // prepare view more url
        if ( !empty($this->vars['more']) )
        {
            $this->vars['more']['url'] = $this->getUrl($this->vars['more']);
            if ( !empty($this->vars['more']['limit']) )
            {
                $limit = $this->vars['more']['limit'];
            }
        }

        $this->assign('list', array_slice($this->list, 0, $limit));
        $this->assign('vars', $this->vars);
    }
}
