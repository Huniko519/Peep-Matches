<?php

class BASE_CMP_EntityTagCloud extends BASE_CMP_TagCloud
{
    /**
     * @var integer
     */
    protected $entityId;
    /**
     * @var string
     */
    protected $entityType;
    /**
     * @var integer
     */
    protected $tagsCount;
    /**
     * @var BOL_TagService
     */
    protected $service;

    /**
     * Constructor.
     *
     * @param string $entityType
     * @param string $url
     * @param integer $tagsCount
     */
    public function __construct( $entityType, $url = null, $tagsCount = null )
    {
        parent::__construct();
        $this->service = BOL_TagService::getInstance();
        $this->entityType = trim($entityType);
        $this->url = trim($url);
        $this->tagsCount = $tagsCount;

        $this->setTemplate(PEEP::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'tag_cloud.html');
    }

    /**
     * Sets entity id for tag selection.
     * If set only entity item's tags are displayed.
     *
     * @param integer $entityId
     * @return BASE_CMP_EntityTagCloud
     */
    public function setEntityId( $entityId )
    {
        $this->entityId = (int) $entityId;
        return $this;
    }

    /**
     * @return integer
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return integer
     */
    public function getTagsCount()
    {
        return $this->tagsCount;
    }

    /**
     * @param integer $tagsCount
     * @return BASE_CMP_EntityTagCloud
     */
    public function setTagsCount( $tagsCount )
    {
        $this->tagsCount = $tagsCount;
        return $this;
    }

    /**
     * @see PEEP_Rendarable::onBeforeRender
     */
    public function onBeforeRender()
    {
        if ( $this->entityId !== null )
        {
            $this->tagList = $this->service->findEntityTagsWithPopularity($this->entityId, $this->entityType);
        }
        else
        {
            if ( $this->tagsCount === null )
            {
                $this->tagsCount = $this->service->getConfig(BOL_TagService::CONFIG_DEFAULT_TAGS_COUNT);
            }

            $this->tagList = $this->service->findMostPopularTags($this->entityType, $this->tagsCount);
        }

        parent::onBeforeRender();
    }
}