<?php

class BASE_CMP_TagCloud extends PEEP_Component
{
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $routeName;
    /**
     * @var array
     */
    protected $tagList;
    /**
     * @var BOL_TagService
     */
    protected $service;

    /**
     * Constructor.
     *
     * @param array<count,label> $tagList
     *
     */
    public function __construct( array $tagList = null, $url = null )
    {
        parent::__construct();

        $this->tagList = $tagList;
        $this->url = $url;
        $this->service = BOL_TagService::getInstance();
    }

    /**
     * Sets route name for tag items.
     * Route should be added to router and contain var - `tag`.
     *
     * @param string $routeName
     * @return BASE_CMP_TagCloud
     */
    public function setRouteName( $routeName )
    {
        $this->routeName = trim($routeName);
        return $this;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl( $url )
    {
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function getTagList()
    {
        return $this->tagList;
    }

    /**
     * @param array $tagList
     */
    public function setTagList( $tagList )
    {
        $this->tagList = $tagList;
    }

    /**
     * @see PEEP_Rendarable::onBeforeRender
     */
    public function onBeforeRender()
    {
        if ( $this->url === null && $this->routeName === null )
        {
            throw new LogicException();
        }

        // get font sizes from configs
        $minFontSize = $this->service->getConfig(BOL_TagService::CONFIG_MIN_FONT_SIZE);
        $maxFontSize = $this->service->getConfig(BOL_TagService::CONFIG_MAX_FONT_SIZE);

        // get min and max tag's items count
        $minCount = null;
        $maxCount = null;

        if ( !$this->tagList )
        {
            $this->setVisible(false);
            return;
        }

        foreach ( $this->tagList as $tag )
        {
            if ( $minCount === null )
            {
                $minCount = (int) $tag['count'];
                $maxCount = (int) $tag['count'];
            }

            if ( (int) $tag['count'] < $minCount )
            {
                $minCount = (int) $tag['count'];
            }

            if ( (int) $tag['count'] > $maxCount )
            {
                $maxCount = (int) $tag['count'];
            }
        }

        $tags = array();

        // prepare array to assign
        $list = empty($this->tagList) ? array() : $this->tagList;

        foreach ( $list as $key => $value )
        {
            if ( $value['label'] === null )
            {
                continue;
            }

            $tags[$key]['url'] = ($this->routeName === null) ? PEEP::getRequest()->buildUrlQueryString($this->url, array('tag' => $value['label'])) : PEEP::getRouter()->urlForRoute($this->routeName, array('tag' => $value['label']));

            $fontSize = ($maxCount === $minCount ? ($maxFontSize / 2) : floor(((int) $value['count'] - $minCount) / ($maxCount - $minCount) * ($maxFontSize - $minFontSize) + $minFontSize));

            $tags[$key]['size'] = $fontSize;
            $tags[$key]['lineHeight'] = $fontSize + 4;
            $tags[$key]['label'] = $value['label'];
        }

        $this->assign('tags', $tags);
    }
}
