<?php

class BASE_CLASS_CacheBackendMysql implements PEEP_ICacheBackend
{
    /**
     * @var array
     */
    private $loadedItems;

    /**
     * @var PEEP_Database
     */
    private $dbo;

    public function __construct( PEEP_Database $dbo )
    {
        $this->dbo = $dbo;
        $result = $this->dbo->queryForList("SELECT `key`, `content` FROM `" . $this->getCacheTableName() . "` WHERE `expireTimestamp` >= :ct AND `instantLoad` = 1", array('ct' => time()));
        $this->loadedItems = array();

        foreach ( $result as $item )
        {
            $this->loadedItems[$item['key']] = $item['content'];
        }
    }

    public function clean( array $tags, $mode )
    {
        if ( !$tags || !$mode )
        {
            return false;
        }

        switch ( $mode )
        {
            case PEEP_CacheManager::CLEAN_ALL:
                $this->dbo->query("DELETE FROM `" . $this->getCacheTableName() . "`");
                $this->dbo->query("DELETE FROM `" . $this->getTagsTableName() . "`");
                break;

            case PEEP_CacheManager::CLEAN_MATCH_ANY_TAG:
                $this->dbo->delete("DELETE `c` FROM `" . $this->getCacheTableName() . "` AS `c` INNER JOIN `" . $this->getTagsTableName() . "` AS `t` ON ( `c`.`id` = `t`.`cacheId` ) WHERE `t`.`tag` IN ( " . $this->dbo->mergeInClause($tags) . " ) ");
                break;

            case PEEP_CacheManager::CLEAN_MATCH_TAGS:
                throw new LogicException("CLEAN_MATCH_TAGS hasn't been implemeted yet");
                //$cacheIds = $this->dbo->queryForColumnList("SELECT `` ");
                break;

            case PEEP_CacheManager::CLEAN_NOT_MATCH_TAGS:
                $this->dbo->delete("DELETE `c` FROM `" . $this->getCacheTableName() . "` AS `c` LEFT JOIN `" . $this->getTagsTableName() . "` AS `t` ON ( `c`.`id` = `t`.`cacheId` ) WHERE `t`.`tag` IS NULL OR `t`.`tag` NOT IN ( " . $this->dbo->mergeInClause($tags) . " ) ");
                break;

            case PEEP_CacheManager::CLEAN_OLD:
                $this->dbo->query("DELETE FROM `" . $this->getCacheTableName() . "` WHERE `expireTimestamp` < :ctime", array('ctime' => time()));
                break;
        }

        $this->dbo->query("DELETE `t` FROM `" . $this->getTagsTableName() . "` AS `t` LEFT JOIN `" . $this->getCacheTableName() . "`  AS `c` on (`t`.`cacheId` = `c`.`id`) WHERE `c`.`id` IS NULL");
    }

    public function load( $key )
    {
        if ( isset($this->loadedItems[$key]) )
        {
            return $this->loadedItems[$key];
        }

        $result = $this->dbo->queryForColumn("SELECT `content` FROM `" . $this->getCacheTableName() . "` WHERE `key` = :key AND `expireTimestamp` >= :ts", array('key' => $key, 'ts' => time()));

        if ( $result )
        {
            return $result;
        }

        return null;
    }

    public function remove( $key )
    {
        if ( !$key )
        {
            return;
        }

        $result = $this->dbo->queryForColumn("SELECT `id` FROM `" . $this->getCacheTableName() . "` WHERE `key` = :key", array('key' => $key));

        if ( $result )
        {
            $result = intval($result);
        }
        else
        {
            return;
        }

        $this->dbo->query("DELETE FROM `" . $this->getTagsTableName() . "` WHERE `cacheId` = :cacheId", array('cacheId' => $result));
        $this->dbo->query("DELETE FROM `" . $this->getCacheTableName() . "` WHERE `id` = :id", array('id' => $result));
    }

    public function save( $data, $key, array $tags = array(), $lifeTime )
    {
        if ( empty($key) || empty($data) || empty($lifeTime) )
        {
            return;
        }

        $tags = array_unique($tags);
        $instantLoad = false;

        $optionIndex = array_search(PEEP_CacheManager::TAG_OPTION_INSTANT_LOAD, $tags);

        if ( $optionIndex !== false )
        {
            $instantLoad = true;
            unset($tags[$optionIndex]);
        }

        $expTime = time() + $lifeTime;

        $oldEntryId = $this->dbo->queryForColumn("SELECT `id` FROM `" . $this->getCacheTableName() . "` WHERE `key` = :key", array('key' => $key));

        if ( $oldEntryId !== null )
        {
            $this->dbo->query("DELETE FROM `" . $this->getCacheTableName() . "` WHERE `id` = :id", array('id' => $oldEntryId));
            $this->dbo->query("DELETE FROM `" . $this->getTagsTableName() . "` WHERE `cacheId` = :cacheId", array('cacheId' => $oldEntryId));
        }

        $this->dbo->query("INSERT INTO `" . $this->getCacheTableName() . "` (`key`, `content`, `expireTimestamp`, `instantLoad`) VALUES (:key, :content, :ts, :il)", array('key' => $key, 'content' => $data, 'ts' => $expTime, 'il' => $instantLoad));

        if ( $tags )
        {
            $cacheId = $this->dbo->getInsertId();
            foreach ( $tags as $tag )
            {
                $this->dbo->query("INSERT INTO `" . $this->getTagsTableName() . "` (`tag`, `cacheId`) VALUES (:tag, :cacheId)", array('tag' => $tag, 'cacheId' => $cacheId));
            }
        }
    }

    public function test( $key )
    {
        return $this->dbo->queryForColumn("SELECT `id` FROM `" . $this->getCacheTableName() . "` WHERE `key` = :key AND `expireTimestamp` >= :ts", array('key' => $key, 'ts' => time())) ? true : false;
    }

    private function getCacheTableName()
    {
        return PEEP_DB_PREFIX . 'base_cache';
    }

    private function getTagsTableName()
    {
        return PEEP_DB_PREFIX . 'base_cache_tag';
    }
}