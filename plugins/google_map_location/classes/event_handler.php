<?php

class GOOGLELOCATION_CLASS_EventHandler
{    
    public $jsLibAdded = 0;

    public function __construct()
    {
        
    }

    function onEventDelete( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( !empty($params['eventId']) )
        {
            GOOGLELOCATION_BOL_LocationService::getInstance()->deleteByEntityIdAndEntityType((int) $params['eventId'], GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_EVENT);
        }
    }

    function onUserUnregister( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( !empty($params['userId']) )
        {
            $userId = (int) $params['userId'];
            GOOGLELOCATION_BOL_LocationService::getInstance()->deleteByEntityIdAndEntityType($userId, GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_USER);
        }
    }

    function addUserListData( BASE_CLASS_EventCollector $event )
    {
        $event->add(
            array(
                'label' => PEEP::getLanguage()->text('googlelocation', 'users_map_menu_item'),
                'url' => PEEP::getRouter()->urlForRoute('googlelocation_user_map', array('list' => 'map')),
                'iconClass' => 'peep_ic_bookmark',
                'key' => 'map',
                'order' => 6
            )
        );
    }

    // -- question --

    function questionsFieldInit( PEEP_Event $e )
    {
        $params = $e->getParams();
        
        if ( $params['fieldName'] == 'googlemap_location' )
        {
            $formElement = new GOOGLELOCATION_CLASS_Location($params['fieldName']);

            if ( $params['type'] == 'search' )
            {
                $formElement = new GOOGLELOCATION_CLASS_LocationSearch($params['fieldName']);
                $formElement->setInvitation(PEEP::getLanguage()->text('googlelocation', 'googlemap_location_search_invitation'));
                $formElement->setHasInvitation(true);
                
                if ( PEEP::getUser()->isAuthenticated() && PEEP::getConfig()->getValue('googlelocation', 'auto_fill_location_on_search') )
                {
                    $data = BOL_QuestionService::getInstance()->getQuestionData(array(PEEP::getUser()->getId()), array('googlemap_location'));
                    
                    if ( !empty($data[PEEP::getUser()->getId()]['googlemap_location']['json']) )
                    {
                        $formElement->setValue($data[PEEP::getUser()->getId()]['googlemap_location']);
                    }
                }
            }

            $e->setData($formElement);
        }
    }

    function questionsSaveData( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        foreach ( $data as $key => $value )
        {
            if ( $key == 'googlemap_location' )
            {
                $element = new GOOGLELOCATION_CLASS_Location('location');
                $element->setValue($value);
                $valueList = $element->getListValue();
                    
                if ( !empty($valueList['remove']) && $valueList['remove'] == "true" )
                {
                    GOOGLELOCATION_BOL_LocationService::getInstance()->deleteByEntityIdAndEntityType($params['userId'], 'user');
                    $data[$key] = '';
                    continue;
                }
                
                if ( empty($valueList) || empty($valueList['json']) )
                {
                    unset($data[$key]);
                    continue;
                }

                $json = !empty($valueList['json']) ? json_decode($valueList['json'], true) : array();

                $countryCode = "";
                if ( !empty($json['address_components']) )
                {
                    foreach ( $json['address_components'] as $component )
                    {
                        if ( !empty($component['types']) && is_array($component['types']) && in_array('country', $component['types']) )
                        {
                            $countryCode = !empty($component['short_name']) ? $component['short_name'] : "";
                        }
                    }
                }

                $location = GOOGLELOCATION_BOL_LocationService::getInstance()->findByUserId($params['userId']);

                if ( empty($location) )
                {
                    $location = new GOOGLELOCATION_BOL_Location();
                }

                $location->entityId = (int) $params['userId'];
                $location->countryCode = $countryCode;
                $location->address = !empty($valueList['address']) ? $valueList['address'] : "";
                $location->lat = (float) $valueList['latitude'];
                $location->lng = (float) $valueList['longitude'];
                $location->northEastLat = (float) $valueList['northEastLat'];
                $location->northEastLng = (float) $valueList['northEastLng'];
                $location->southWestLat = (float) $valueList['southWestLat'];
                $location->southWestLng = (float) $valueList['southWestLng'];
                $location->json = !empty($valueList['json']) ? $valueList['json'] : "";
                $location->entityType = GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_USER;

                GOOGLELOCATION_BOL_LocationService::getInstance()->save($location);

                $data[$key] = $location->address;
            }
        }

        $e->setData($data);
    }

    function questionsFieldGetValue( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( !empty($params['fieldName']) && $params['fieldName'] == 'googlemap_location' && !empty($params['value']) )
        {
            $location = $params['value'];

            if ( !empty($location['json']) )
            {

                $userViewPresentation = PEEP::getConfig()->getValue('base', 'user_view_presentation');

                $locationDto = new GOOGLELOCATION_BOL_Location();

                $locationDto->entityId = (int) $params['userId'];
                $locationDto->address = !empty($location['address']) ? $location['address'] : "";
                $locationDto->lat = (float) $location['latitude'];
                $locationDto->lng = (float) $location['longitude'];
                $locationDto->northEastLat = (float) $location['northEastLat'];
                $locationDto->northEastLng = (float) $location['northEastLng'];
                $locationDto->southWestLat = (float) $location['southWestLat'];
                $locationDto->southWestLng = (float) $location['southWestLng'];
                $locationDto->json = !empty($location['json']) ? $location['json'] : "";
                $locationDto->entityType = GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_USER;

                $location = get_object_vars($locationDto);
 PEEP::getEventManager()->trigger(new PEEP_Event('googlelocation.add_js_lib'));
//                PEEP::getDocument()->addOnloadScript("
//                            var link = $('.peep_googlemap_location_address');
//                            link.on('click', 'a', function(event){
//                                      $('.peep_googlemap_location_map').toggle(200);
//
//                                        $('.peep_googlemap_location_address .googlemap_pin').removeClass('ic_googlemap_pin');
//                                        $('.peep_googlemap_location_address .googlemap_pin').addClass('peep_preloader');
//
//                                      var params = {
//                                        onLoad: function() {
//                                            $('.peep_googlemap_location_address .googlemap_pin').removeClass('peep_preloader');
//                                            $('.peep_googlemap_location_address .googlemap_pin').addClass('ic_googlemap_pin');
//                                            window.map['map_profile_view'].resize();
//                                        },
//                                        onComplete: function(){
//
//                                        },
//                                        onReady: function( html )
//                                        {
//                                            $('.peep_googlemap_location_map').append(html);
//                                        }
//                                      };
//
//                                      PEEP.loadComponent( 'GOOGLELOCATION_CMP_ProfileViewMap', [" . json_encode($location) . ", { mapName : 'map_profile_view' }] , params );
//                                      link.off('click', 'a');
//                                      link.on('click', 'a', function(event){ $('.peep_googlemap_location_map').toggle(200); } );
//                                    });
//                            ");

               
                $data = '<div class="peep_googlemap_location_view_presentation">
                            <div class="peep_googlemap_location_address" >
<div class="profile_adress" data-location=\''.  json_encode($location).'\' >' . $location['address'] .  '</div>
                                
                            </div>
                            <div class="peep_googlemap_location_map" style="display:none;">

                            </div>
                         </div>';
            }
        }

        $e->setData($data);
    }

    function questionsGetData( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( empty($params['fieldsList']) )
        {
            return;
        }
        
        foreach ( $data as $userId => $questions )
        {
            foreach ( $params['fieldsList'] as $key )
            {
                if ( $key == 'googlemap_location' )
                {
                    $location = GOOGLELOCATION_BOL_LocationService::getInstance()->findByUserId($userId);

                    if ( $location )
                    {
                        $data[$userId][$key] = array(
                            'address' => $location->address,
                            'latitude' => $location->lat,
                            'longitude' => $location->lng,
                            'northEastLat' => $location->northEastLat,
                            'northEastLng' => $location->northEastLng,
                            'southWestLat' => $location->southWestLat,
                            'southWestLng' => $location->southWestLng,
                            'json' => $location->json
                        );
                    }
                }
            }
        }

        $e->setData($data);
    }

// -- question --
// -- search --

    function questionSearchSql( BASE_CLASS_QueryBuilderEvent $e )
    {
        $params = $e->getParams();
        $question = !empty($params['question']) ? $params['question'] : null;
        $questionValue = !empty($params['value']) ? $params['value'] : null;

        if ( !empty($question) && $question->name == 'googlemap_location' )
        {
            if ( empty($questionValue) || empty($questionValue['json']) )
            {
                $e->addWhere(" 1 ");
                return;
            }

            $element = new GOOGLELOCATION_CLASS_Location('location');
            $element->setValue($params['value']);
            $value = $element->getListValue();

            $json = !empty($value['json']) ? json_decode($value['json'], true) : array();

            $countryCode = "";
            if ( !empty($json['address_components']) )
            {
                foreach ( $json['address_components'] as $component )
                {
                    if ( !empty($component['types']) && is_array($component['types']) && in_array('country', $component['types']) )
                    {
                        $countryCode = !empty($component['short_name']) ? $component['short_name'] : "";
                    }
                }
            }

            if ( !empty($value['distance']) && (float) $value['distance'] > 0 )
            {
                $coord = GOOGLELOCATION_BOL_LocationService::getInstance()->getNewCoordinates($value['southWestLat'], $value['southWestLng'], 225, (float) $value['distance']);
                $value['southWestLat'] = $coord['lat'];
                $value['southWestLng'] = $coord['lng'];

                $coord = GOOGLELOCATION_BOL_LocationService::getInstance()->getNewCoordinates($value['northEastLat'], $value['northEastLng'], 45, (float) $value['distance']);
                $value['northEastLat'] = $coord['lat'];
                $value['northEastLng'] = $coord['lng'];
            }

            $sql = GOOGLELOCATION_BOL_LocationService::getInstance()->getSearchInnerJoinSql('user', $value['southWestLat'], $value['southWestLng'], $value['northEastLat'], $value['northEastLng'], $countryCode);
            $e->addJoin($sql);
        }
    }

// -- search --

    function addJsLib( PEEP_Event $e )
    {
//        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl() . 'jquery.ui.widget.js', "text/javascript");
//        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl() . 'jquery.ui.menu.js', "text/javascript");
//        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl() . 'jquery.ui.autocomplete.js', "text/javascript");
//        
        if ( !$this->jsLibAdded )
        {
            $languageCode = GOOGLELOCATION_BOL_LocationService::getInstance()->getLanguageCode();

            $key = Peep::getConfig()->getValue('googlelocation', 'api_key');

            if ( !empty($key) )
            {
                $key = '&key=' . $key;
            }

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

            $baseJsDir = PEEP::getPluginManager()->getPlugin("base")->getStaticJsUrl();
            PEEP::getDocument()->addScript($baseJsDir . "jquery-ui.min.js");
            //PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl() . 'jquery-ui-1.10.3.custom.min.js');
            
            PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl() . 'jquery.js', null, GOOGLELOCATION_BOL_LocationService::JQUERY_LOAD_PRIORITY);
            PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl() . 'jquery.migrate.js', null, GOOGLELOCATION_BOL_LocationService::JQUERY_LOAD_PRIORITY);
            PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl() . 'jquery.ui.js', null, GOOGLELOCATION_BOL_LocationService::JQUERY_LOAD_PRIORITY);
            
            PEEP::getDocument()->addStyleSheet(PEEP::getPluginManager()->getPlugin('googlelocation')->getStaticCssUrl() . 'location.css');
            //PEEP::getDocument()->addStyleSheet(PEEP::getPluginManager()->getPlugin('googlelocation')->getStaticCssUrl() . 'jquery-ui.css');
            PEEP::getDocument()->addScript($protocol.'maps.google.com/maps/api/js?sensor=false' . $key . '&language=' . $languageCode);
            PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl() . 'InfoBubble.js');
            PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl() . 'markerclusterer.js');
            
            PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl().'map.js', "text/javascript", GOOGLELOCATION_BOL_LocationService::JQUERY_LOAD_PRIORITY + 1);
            PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl() . 'autocomplete.js',  "text/javascript", GOOGLELOCATION_BOL_LocationService::JQUERY_LOAD_PRIORITY + 1);
            PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('googlelocation')->getStaticJsUrl() . 'map_hint.js');
            
            $hintTemplate = new GOOGLELOCATION_CMP_MapHintTemplate();
            $template = $hintTemplate->render();
            
            
            PEEP::getDocument()->addOnloadScript('

            $("head").append('.json_encode($template).')


            if( !window.map )
            {
                window.map = {};
            }
            $( document ).ready(function() { 
                if ( !window.googlelocation_hint_init )
                {
                    GoogleMapLocationHint.LAUNCHER().init('.json_encode(GOOGLELOCATION_BOL_LocationService::getInstance()->getDefaultMarkerIcon()).');
                    window.googlelocation_hint_init = true;
                }
            });
            ');
            
            $this->jsLibAdded = 1;
        }
    }

    function addFakeQuestions( PEEP_Event $e )
    {
        $params = $e->getParams();

        if ( !empty($params['name']) && $params['name'] == 'googlemap_location' )
        {
            $e->setData(false);
        }
    }

    function getMapComponent( PEEP_Event $e )
    {

        $params = $e->getParams();

        $userIdList = !empty($params['userIdList']) ? $params['userIdList'] : array();
        $backUri = !empty($params['backUri']) ? $params['backUri'] : PEEP::getRouter()->getUri();

        $map = GOOGLELOCATION_BOL_LocationService::getInstance()->getUserListMapCmp($userIdList, $backUri);

        $e->setData($map);
    }

    function eventEditLocationInit( PEEP_Event $e )
    {
        $params = $e->getParams();

        if ( $params['name'] == 'location' )
        {
            /* @var $formElement TextField  */
            $formElement = $e->getData();
            $label = $formElement->getLabel();

            $uriParams = PEEP::getRequest()->getUriParams();

            $locationFormElement = new GOOGLELOCATION_CLASS_Location('location');
            $locationFormElement->setLabel($label);

            if ( !empty($uriParams['eventId']) )
            {
                $location = GOOGLELOCATION_BOL_LocationService::getInstance()->findByEntityIdAndEntityType((int) $uriParams['eventId'], GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_EVENT);

                if ( !empty($location) && $location instanceof GOOGLELOCATION_BOL_Location )
                {
                    $value = array(
                        'address' => $location->address,
                        'latitude' => $location->lat,
                        'longitude' => $location->lng,
                        'northEastLat' => $location->northEastLat,
                        'northEastLng' => $location->northEastLng,
                        'southWestLat' => $location->southWestLat,
                        'southWestLng' => $location->southWestLng,
                        'json' => $location->json
                    );

                    $locationFormElement->setValue($value);
                }
            }

            $e->setData($locationFormElement);
        }
    }

    function beforeEventEdit( PEEP_Event $e )
    {
        $data = $e->getData();
        $params = $e->getParams();

        if ( !empty($data['location']) && !empty($params['eventId']) )
        {
            $locationFormElement = new GOOGLELOCATION_CLASS_Location('location');
            $locationFormElement->setValue($data['location']);
            $value = $locationFormElement->getValue();

            if ( !empty($value) )
            {

                $json = !empty($value['json']) ? json_decode($value['json'], true) : array();

                $countryCode = "";
                if ( !empty($json['address_components']) )
                {
                    foreach ( $json['address_components'] as $component )
                    {
                        if ( !empty($component['types']) && is_array($component['types']) && in_array('country', $component['types']) )
                        {
                            $countryCode = !empty($component['short_name']) ? $component['short_name'] : "";
                        }
                    }
                }

                $location = GOOGLELOCATION_BOL_LocationService::getInstance()->findByEntityIdAndEntityType((int) $params['eventId'], 'event');

                if ( empty($location) )
                {
                    $location = new GOOGLELOCATION_BOL_Location();
                }

                $location->entityId = (int) $params['eventId'];
                $location->countryCode = $countryCode;
                $location->address = !empty($value['address']) ? $value['address'] : "";
                $location->lat = (float) $value['latitude'];
                $location->lng = (float) $value['longitude'];
                $location->northEastLat = (float) $value['northEastLat'];
                $location->northEastLng = (float) $value['northEastLng'];
                $location->southWestLat = (float) $value['southWestLat'];
                $location->southWestLng = (float) $value['southWestLng'];
                $location->json = !empty($value['json']) ? $value['json'] : "";
                $location->entityType = GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_EVENT;

                GOOGLELOCATION_BOL_LocationService::getInstance()->save($location);

                $data['location'] = $location->address;
            }
//            else
//            {
//                $data['location'] = "";
//            }
        }
//        else
//        {
//            $data['location'] = "";
//        }

        $e->setData($data);
    }

    function beforeEventCreate( PEEP_Event $e )
    {
        $data = $e->getData();

        if ( !empty($data['location']) )
        {
            $locationFormElement = new GOOGLELOCATION_CLASS_Location('location');
            $locationFormElement->setValue($data['location']);
            $value = $locationFormElement->getValue();

            if ( !empty($value) )
            {
                PEEP::getSession()->set('googlelocation_tmp_event_data', $value);
                $data['location'] = !empty($value['address']) ? $value['address'] : "";
            }
//            else
//            {
//                $data['location'] = "";
//            }
        }
//        else
//        {
//            $data['location'] = "";
//        }

        $e->setData($data);
    }

    function afterEventCrate( PEEP_Event $e )
    {
        $params = $e->getParams();

        if ( !empty($params['eventDto']) && $params['eventDto'] instanceof EVENT_BOL_Event )
        {
            /* @var $eventDto EVENT_BOL_Event */
            $eventDto = $params['eventDto'];

            $locationValue = PEEP::getSession()->get('googlelocation_tmp_event_data');
            PEEP::getSession()->delete('googlelocation_tmp_event_data');

            $locationFormElement = new GOOGLELOCATION_CLASS_Location('location');
            $locationFormElement->setValue($locationValue);
            $value = $locationFormElement->getValue();

            if ( !empty($value) )
            {

                $json = !empty($value['json']) ? json_decode($value['json'], true) : array();

                $countryCode = "";
                if ( !empty($json['address_components']) )
                {
                    foreach ( $json['address_components'] as $component )
                    {
                        if ( !empty($component['types']) && is_array($component['types']) && in_array('country', $component['types']) )
                        {
                            $countryCode = !empty($component['short_name']) ? $component['short_name'] : "";
                        }
                    }
                }

                $location = new GOOGLELOCATION_BOL_Location();

                $location->entityId = $eventDto->id;
                $location->countryCode = $countryCode;
                $location->address = !empty($value['address']) ? $value['address'] : "";
                $location->lat = (float) $value['latitude'];
                $location->lng = (float) $value['longitude'];
                $location->northEastLat = (float) $value['northEastLat'];
                $location->northEastLng = (float) $value['northEastLng'];
                $location->southWestLat = (float) $value['southWestLat'];
                $location->southWestLng = (float) $value['southWestLng'];
                $location->json = !empty($value['json']) ? $value['json'] : "";
                $location->entityType = GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_EVENT;

                GOOGLELOCATION_BOL_LocationService::getInstance()->save($location);
            }
        }
        PEEP::getSession()->delete('googlelocation_tmp_event_data');
    }

    function addEventContentMenuItem( BASE_CLASS_EventCollector $e )
    {
        $menuItem = new BASE_MenuItem();
        $menuItem->setKey('events_map');
        $menuItem->setUrl(PEEP::getRouter()->urlForRoute('googlelocation_event_map'));
        $menuItem->setLabel(PEEP::getLanguage()->text('googlelocation', 'events_map_label'));
        $menuItem->setIconClass('peep_ic_bookmark');
        $menuItem->setOrder(5);

        $e->add($menuItem);
    }

    function addEventMapOnViewPage( BASE_CLASS_EventCollector $e )
    {
        $uriParams = PEEP::getRequest()->getUriParams();

        if ( !empty($uriParams['eventId']) )
        {
            $location = GOOGLELOCATION_BOL_LocationService::getInstance()->findByEntityIdAndEntityType((int) $uriParams['eventId'], GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_EVENT);

            if ( !empty($location) && $location instanceof GOOGLELOCATION_BOL_Location )
            {
                $value = array(
                    'address' => $location->address,
                    'lat' => $location->lat,
                    'lng' => $location->lng,
                    'northEastLat' => $location->northEastLat,
                    'northEastLng' => $location->northEastLng,
                    'southWestLat' => $location->southWestLat,
                    'southWestLng' => $location->southWestLng,
                    'json' => $location->json
                );

                $map = new GOOGLELOCATION_CMP_Map();
                $map->setHeight('180px');
                $map->setZoom(9);
                $map->setMapOptions(array(
                    'disableDefaultUI' => "false",
                    'draggable' => "true",
                    'mapTypeControl' => "false",
                    'overviewMapControl' => "false",
                    'panControl' => "false",
                    'rotateControl' => "true",
                    'scaleControl' => "false",
                    'scrollwheel' => "true",
                    'streetViewControl' => "false",
                    'zoomControl' => "true"));
                $map->setCenter($value['lat'], $value['lng']);
                $map->setBounds($value['southWestLat'], $value['southWestLng'], $value['northEastLat'], $value['northEastLng']);
                $map->addPoint($value, $value['address']);

                $map->setBox('Location', 'peep_ic_bookmark', 'peep_std_margin clearfix');

                $mapHtml = $map->render();

                $e->add($mapHtml);
            }
        }
    }
    
    public function calcDistance( PEEP_Event $e )
    {
        $params = $e->getParams();
        
        $lat = !empty($params['lat']) ? (double)$params['lat'] : 0;
        $lon = !empty($params['lon']) ? (double)$params['lon'] : 0;
        $lat1 = !empty($params['lat1']) ? (double)$params['lat1'] : 0;
        $lon1 = !empty($params['lon1']) ? (double)$params['lon1'] : 0;
        
        $distance = GOOGLELOCATION_BOL_LocationService::getInstance()->distance($lat, $lon, $lat1, $lon1);
        $units = GOOGLELOCATION_BOL_LocationService::getInstance()->getDistanseUnits();
        
        $data = array(
            'distance' => $distance,
            'units' => $units
        );
        
        $e->setData($data);
    }
    
    public function onBeforePluginUninstall( PEEP_Event $e )
    {
        $params = $e->getParams();
        
        if ( !empty($params['pluginKey']) && $params['pluginKey'] == 'event' )
        {
            GOOGLELOCATION_BOL_LocationService::getInstance()->deleteByEntityType(GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_EVENT);
        }
    }


    public function genericInit()
    {
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregister'));
        PEEP::getEventManager()->bind('base.add_user_list', array($this, 'addUserListData'));
        PEEP::getEventManager()->bind('base.questions_field_init', array($this, 'questionsFieldInit'));
        PEEP::getEventManager()->bind('base.questions_save_data', array($this, 'questionsSaveData'));
        
        PEEP::getEventManager()->bind('base.questions_get_data', array($this, 'questionsGetData'));
        PEEP::getEventManager()->bind('base.question.search_sql', array($this, 'questionSearchSql'));

        PEEP::getEventManager()->bind('base.questions_field_add_fake_questions', array($this, 'addFakeQuestions'));
        PEEP::getEventManager()->bind('googlelocation.get_map_component', array($this, 'getMapComponent'));

        //// ----------------- Events plugin integation ------------------------------
        PEEP::getEventManager()->bind('event.event_add_form.get_element', array($this, 'eventEditLocationInit'));
        PEEP::getEventManager()->bind('events.before_event_edit', array($this, 'beforeEventEdit'));
        PEEP::getEventManager()->bind('events.before_event_create', array($this, 'beforeEventCreate'));
        PEEP::getEventManager()->bind('event_after_create_event', array($this, 'afterEventCrate'));
        PEEP::getEventManager()->bind('event.add_content_menu_item', array($this, 'addEventContentMenuItem'));
        PEEP::getEventManager()->bind('events.view.content.after_event_description', array($this, 'addEventMapOnViewPage'));
        PEEP::getEventManager()->bind('event_on_delete_event', array($this, 'onEventDelete'));
        PEEP::getEventManager()->bind('googlelocation.calc_distance', array($this, 'calcDistance'));
        
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($this, 'onBeforePluginUninstall'));
    }

    public function mobileInit()
    {
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'addJsLib'));
        PEEP::getEventManager()->bind('base.questions_field_get_value', array($this, 'questionsFieldGetValue'));
    }

    public function init()
    {
        PEEP::getEventManager()->bind('googlelocation.add_js_lib', array($this, 'addJsLib'));
        PEEP::getEventManager()->bind('base.questions_field_get_value', array($this, 'questionsFieldGetValue'));
    }
}

//// ----------------- Events plugin integation ------------------------------
//function googlelocation_event_add_location_init( PEEP_Event $e )
//{
//    $params = $e->getParams();
//}
//PEEP::getEventManager()->bind('base.questions_field_init', 'googlelocation_questions_field_init');

/* function googlelocation_questions_get_search_sql( PEEP_Event $e )
  {
  $params = $e->getParams();

  if ( !empty($params['fieldName']) && $params['fieldName'] == 'googlemap_location' && !empty($params['value']) )
  {
  if ( empty($value['json']) )
  {
  $e->setData(" 1 OR 1 ");
  return;
  }

  $element = new GOOGLELOCATION_CLASS_Location('location');
  $element->setValue($params['value']);
  $value = $element->getListValue();

  $json = !empty($value['json']) ? json_decode($value['json'], true) : array();

  $countryCode = "";
  if ( !empty($json['address_components']) )
  {
  foreach ( $json['address_components'] as $component )
  {
  if ( !empty($component['types']) && is_array($component['types']) && in_array('country', $component['types']) )
  {
  $countryCode = !empty($component['short_name']) ? $component['short_name'] : "";
  }
  }
  }

  if ( !empty($value['distance']) && (float) $value['distance'] > 0 )
  {
  $coord = GOOGLELOCATION_BOL_LocationService::getInstance()->getNewCoordinates($value['southWestLat'], $value['southWestLng'], 225, (float) $value['distance']);
  $value['southWestLat'] = $coord['lat'];
  $value['southWestLng'] = $coord['lng'];

  $coord = GOOGLELOCATION_BOL_LocationService::getInstance()->getNewCoordinates($value['northEastLat'], $value['northEastLng'], 45, (float) $value['distance']);
  $value['northEastLat'] = $coord['lat'];
  $value['northEastLng'] = $coord['lng'];
  }

  $sql = GOOGLELOCATION_BOL_LocationService::getInstance()->getSearchInnerJoinSql('user', $value['southWestLat'], $value['southWestLng'], $value['northEastLat'], $value['northEastLng'], $countryCode);
  $e->setData(" 1 ) " . mb_substr($sql, 0, -2) . "  ");
  }
  }
  PEEP::getEventManager()->bind('base.questions_get_search_sql', 'googlelocation_questions_get_search_sql'); */

//-- search
//// ----------------- Groups plugin integation ------------------------------
//
//function googlelocation_on_groups_plugin_activate( PEEP_Event $e )
//{
//    $params = $e->getParams();
//    $pluginKey = $params['pluginKey'];
//
//    if ( $pluginKey == 'groups' )
//    {
//        $widgetService = BOL_ComponentAdminService::getInstance();
//
//        $widget = $widgetService->addWidget('GOOGLELOCATION_CMP_GroupsWidget', false);
//        $placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
//        $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT, 0);
//    }
//}
//PEEP::getEventManager()->bind(PEEP_EventManager::ON_AFTER_PLUGIN_ACTIVATE, 'googlelocation_on_groups_plugin_activate');
//
//function googlelocation_on_groups_plugin_deactivate( PEEP_Event $e )
//{
//    $params = $e->getParams();
//    $pluginKey = $params['pluginKey'];
//
//    if ( $pluginKey == 'groups' )
//    {
//        BOL_ComponentAdminService::getInstance()->deleteWidget('GOOGLELOCATION_CMP_GroupsWidget');
//    }
//}
//PEEP::getEventManager()->bind(PEEP_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, 'googlelocation_on_groups_plugin_deactivate');
//


