<?php

class GOOGLELOCATION_CMP_MapUserList extends GOOGLELOCATION_CMP_MapEntityList
{
    
    public function __construct( $IdList, $lat, $lng, $backUri = null )
    {
        parent::__construct($IdList, $lat, $lng, $backUri);
        
        //if ( count($IdList) > self::DISPLAY_COUNT )
        //{
            $hash = GOOGLELOCATION_BOL_LocationService::getInstance()->saveEntityListToSession($IdList);

            $this->display = true;
            $this->url = peep::getRouter()->urlForRoute('googlelocation_user_list', array( 'lat' => $this->lat, 'lng' => $this->lng, 'hash' => $hash ) );
            $this->label = PEEP::getLanguage()->text('googlelocation', 'map_user_list_view_all_button_label', array( 'count' => count($IdList) ) );
        //}
    }
    
    protected function getListCmp()
    {
        $new = new BASE_CMP_MiniAvatarUserList(array_slice($this->IdList, 0, self::DISPLAY_COUNT));

        switch(true)
        {
            case $this->count <= 8:
                    $new->setCustomCssClass('peep_big_avatar');
                break;
            default:
                    //$new->setCustomCssClass(BASE_CMP_MiniAvatarUserList::CSS_CLASS_MINI_AVATAR);
                break;
        }

        return $new;
    }
}