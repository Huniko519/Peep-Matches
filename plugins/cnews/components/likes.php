<?php

class CNEWS_CMP_Likes extends PEEP_Component
{
    private $count = 0;
    
    public function __construct( $entityType, $entityId, $likes = null )
    {
        parent::__construct();
        
        if ( $likes === null )
        {
            $likes = CNEWS_BOL_Service::getInstance()->findEntityLikes($entityType, $entityId);
        }
        
        $this->count = count($likes);
        
        if ( $this->count == 0 )
        {
            $this->setVisible(false);
            
            return;
        }
        
        $userIds = array();
        foreach ( $likes as $like )
        {
            $userIds[] = (int) $like->userId;    
        }
        
        if ( $this->count <= 3 )
        {
            $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIds);
            $urls = BOL_UserService::getInstance()->getUserUrlsForList($userIds);
            
            $langVars = array();
            
            foreach( $userIds as $i => $userId )
            {
                $langVars['user' . ($i + 1)] = '<a href="' . $urls[$userId] . '">' . $displayNames[$userId] . '</a>';
            }
            
            $string = PEEP::getLanguage()->text('cnews', 'feed_likes_' . $this->count . '_label', $langVars);
        } 
        else 
        {
            $url = "javascript: PEEP.showUsers(" . json_encode($userIds) . ")";
            $string = PEEP::getLanguage()->text('cnews', 'feed_likes_list_label', array('count' => $this->count, 'url' => $url));
        }
        
        $this->assign('string', $string);
    }
    
    public function getCount()
    {
        return $this->count;
    }
}