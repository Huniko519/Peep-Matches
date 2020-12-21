<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CLASS_EventHandler
{

    public function init()
    {
        $eventManager = PEEP::getEventManager();
        $eventManager->bind('admin.disable_fields_on_edit_profile_question', array($this, 'onGetDisableActionList'));
        $eventManager->bind('admin.disable_fields_on_edit_profile_question', array($this, 'onGetJoinStampDisableActionList'), 999);
        
    }

    public function onGetDisableActionList( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( !empty($params['questionDto']) && $params['questionDto'] instanceof BOL_Question && $params['questionDto']->name != 'joinStamp' )
        {
            $dto = $params['questionDto'];

            foreach ( $data as $key => $value )
            {
                switch($key)
                {
                    case 'disable_account_type' :
                        
                        if ( $dto->base == 1 )
                        {
                            $data['disable_account_type'] = true;
                        }
                        
                        break;
                    case 'disable_answer_type' :

                        if ( $dto->base == 1 )
                        {
                            $data['disable_answer_type'] = true;
                        }
                        
                        break;
                    case 'disable_presentation' :

                        if ( $dto->base == 1 )
                        {
                            $data['disable_presentation'] = true;
                        }
                        
                        break;
                    case 'disable_column_count' :
                                                
                        if ( !empty($dto->parent) )
                        {
                            $data['disable_column_count'] = true;
                        }
                        
                        break;
                        
                    case 'disable_possible_values' :
                        
                        if ( !empty($dto->parent) )
                        {
                            $data['disable_possible_values'] = true;
                        }
                        
                        break;
                    
                    case 'disable_display_config' :

                        if ( $dto->name == 'joinStamp' )
                        {
                            $data['disable_display_config'] = true;
                        }

                        break;
                    case 'disable_required' :
                        
                        if ( $dto->base == 1 )
                        {
                            $data['disable_required'] = true;
                        }

                        
                        break;
                    case 'disable_on_join' :

                        if ( in_array($dto->name, array('password') ) || $dto->base == 1 )
                        {
                            $data['disable_on_join'] = true;
                        }

                        break;
                    case 'disable_on_view' :
                        if ( in_array($dto->name, array('password') ) )
                        {
                            $data['disable_on_view'] = true;
                        }
                        break;
                    case 'disable_on_search' :
                        if ( in_array($dto->name, array('password') ) )
                        {
                            $data['disable_on_search'] = true;
                        }
                        break;
                    case 'disable_on_edit' :
                        if ( in_array($dto->name, array('password') ) )
                        {
                            $data['disable_on_edit'] = true;
                        }
                        break;
                }
            }
        }

        $e->setData($data);
    }
    
    function onGetJoinStampDisableActionList( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( !empty($params['questionDto']) && $params['questionDto'] instanceof BOL_Question && $params['questionDto']->name == 'joinStamp' )
        {
            $disableActionList = array(
                'disable_account_type' => true,
                'disable_answer_type' => true,
                'disable_presentation' => true,
                'disable_column_count' => true,
                'disable_display_config' => true,
                'disable_possible_values' => true,
                'disable_required' => true,
                'disable_on_join' => true,
                'disable_on_view' => false,
                'disable_on_search' => true,
                'disable_on_edit' => true
            );

            $e->setData($disableActionList);
        }
    }
}
