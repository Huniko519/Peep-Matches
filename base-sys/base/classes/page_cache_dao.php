<?php

abstract class BASE_CLASS_PageCacheDao extends PEEP_BaseDao
{

    protected $cachedItems = array( );

    protected function __construct()
    {
        parent::__construct();
    }

    public function findById( $id, $cacheLifeTime = 0, $tags = array( ) )
    {
        $id = intval($id);

        if ( empty($this->cachedItems[$id]) )
        {
            $this->cachedItems[$id] = parent::findById($id, $cacheLifeTime, $tags);
        }

        return $this->cachedItems[$id];
    }

    public function findByIdList( array $idList, $cacheLifeTime = 0, $tags = array( ) )
    {
        $idList = array_map('intval', $idList);

        $idsToRequire = array( );
        $result = array( );

        foreach ( $idList as $id )
        {
            if ( empty($this->cachedItems[$id]) )
            {
                $idsToRequire[] = $id;
            }
            else
            {
                $result[] = $this->cachedItems[$id];
            }
        }

        $items = array( );

        if ( !empty($idsToRequire) )
        {
            $items = parent::findByIdList($idsToRequire, $cacheLifeTime, $tags);
        }

        foreach ( $items as $item )
        {
            $result[] = $item;
            $this->cachedItems[(int) $item->getId()] = $item;
        }

        return $result;
    }

}