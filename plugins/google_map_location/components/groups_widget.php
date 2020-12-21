<?php


class GOOGLELOCATION_CMP_GroupsWidget extends GOOGLELOCATION_CMP_MapWidget
{    
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        if ( !PEEP::getPluginManager()->isPluginActive('groups') )
        {
            $this->setVisible('false');
            return;
        }
        
        parent::__construct( $params );
        /*@var $map GOOGLELOCATION_CMP_Map*/
        $map = $this->getComponent("map");
        $map->setMapOption('minZoom', 1);
    }
    
    protected function assignList( BASE_CLASS_WidgetParameter $params )
    {
        $groupId = $params->additionalParamList['entityId'];
        
        $list = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($groupId, 'everybody');   

        
        return $list;
    }
}