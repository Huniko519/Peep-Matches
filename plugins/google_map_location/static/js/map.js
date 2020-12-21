
window.PEEP_GoogleMap = function($, google)
{
    return function( elementId )
    {
        var self = this;

        var geocoder;
        var map;
        var marker = {};
        var infowindow = {};
        var infowindowState = [];
        var markerCluster = null;
        var lastBounds = null;

        var mapElementId = elementId;
        this.initialize = function(options)
        {
            var params = options;

            if( !params )
            {
                var latlng = new google.maps.LatLng(0, 0);

                params = {
                    zoom: 2,
                    minZoom: 2,
                    center: latlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    disableDefaultUI: false,
                    draggable: false,
                    mapTypeControl: false,
                    overviewMapControl: false,
                    panControl: false,
                    rotateControl: false,
                    scaleControl: false,
                    scrollwheel: false,
                    streetViewControl: false,
                    zoomControl:false
                };
            }

            map = new google.maps.Map(document.getElementById(mapElementId), params);

            //geocoder = new google.maps.Geocoder();
        }

        this.getMap = function()
        {
            return map;
        }

        this.setCenter = function(lat, lon)
        {
            var latlng = new google.maps.LatLng(lat, lon);
            map.setCenter(latlng);
        }

        this.setZoom = function(zoom)
        {
            map.setZoom(zoom);
        }
        
        this.setOptions = function(options)
        {
            map.setOptions(options);
        }

        this.fitBounds = function(bounds)
        {
            lastBounds = bounds;
            map.fitBounds(bounds);
        }
        
        this.resetLastBounds = function(bounds)
        {
            if ( lastBounds )
            {
                map.fitBounds(lastBounds);
            }
        }

        this.getBounds = function()
        {
            return map.getBounds();
        }

        this.removeAllPoints = function()
        {            
            $.each(marker, function(key,m){
                if ( m )
                {
                    if ( markerCluster )
                    {
                        markerCluster.removeMarker_(m);
                    }
                    m.setMap(null);
                }
            })
            marker = {};
            infowindow = {};
            infowindowState = {};
        }

        this.addPoint = function(lat, lon, title, windowContent, isOpen, customMarkerIconUrl)
        {
            var markerParams = {
                //map: map
                //draggable: false
                //optimized: true
            }
            
            if ( customMarkerIconUrl )
            {
                markerParams.icon = customMarkerIconUrl;
            }
            
            marker[lat + ' ' + lon] = new google.maps.Marker(markerParams);

            var latlng = new google.maps.LatLng(lat, lon);
            marker[lat + ' ' + lon].setPosition(latlng);

            if ( title )
            {
                marker[lat + ' ' + lon].setTitle(title);
            }

            if ( windowContent )
            {
               infowindow[lat + ' ' + lon] = new InfoBubble({
                    content: windowContent,
                    shadowStyle: 0,
                    padding: 9,
                    backgroundColor: '#fff',
                    borderRadius: 4,
                    arrowSize: 10,
                    maxHeight: 350,
                    borderWidth: '4px',
                    borderColor: '#fff',
                    disableAutoPan: false,
                    hideCloseButton: false,
                    arrowPosition: 25,
                    arrowStyle: 0,
                    borderWidth: 0
                });

                //infowindow[lat + ' ' + lon].setContent(windowContent);

                infowindowState[lat + ' ' + lon] = false;
                
                if ( isOpen )
                {
                    infowindow[lat + ' ' + lon].open(map, marker[lat + ' ' + lon]);
                    infowindowState[lat + ' ' + lon] = true;
                }

                    google.maps.event.addListener(marker[lat + ' ' + lon], 'click', function() {
                    if( infowindowState[lat + ' ' + lon] )
                    {
                        infowindow[lat + ' ' + lon].close();
                        infowindowState[lat + ' ' + lon] = false;
                    }
                    else
                    {
                        infowindow[lat + ' ' + lon].open(map, marker[lat + ' ' + lon]);
                        infowindowState[lat + ' ' + lon] = true;
                        
                        $.each( infowindow, function( key, value ) {
                            if ( value )
                            {
                                if ( key != lat + ' ' + lon )
                                {
                                    value.close();
                                    infowindowState[key] = false;
                                }
                            }
                        } );
                    }
                });

                google.maps.event.addListener(infowindow[lat + ' ' + lon], 'closeclick', function() {
                    if( infowindowState[lat + ' ' + lon] )
                    {
                        infowindow[lat + ' ' + lon].close();
                        infowindowState[lat + ' ' + lon] = false;
                    }
                });
            }
        }

        this.resize = function()
        {
            google.maps.event.trigger(map, 'resize');
        }
        
        this.createMarkerCluster = function()
        {
            markerCluster = new MarkerClusterer(map, marker);
        }
        
        this.displaySearchInput = function()
        {

            var centerControlDiv = document.createElement('div');
            var input = $("<div class='googlelocation_map_search_input_icon'>" + 
                    "<span class='googlelocation_map_search_pin ic_googlelocation_map_search_pin'></span>" +
                "</div>" + 
            "<input type='text' class='googlelocation_map_search_input googlelocation_map_search_input_hide' />");
            
            var searchDiv = $(centerControlDiv);
            
            searchDiv.addClass("googlelocation_map_search_input_div").append(input);
            map.controls[google.maps.ControlPosition.TOP_RIGHT].push(centerControlDiv);
            
            var autocomplite = $(centerControlDiv).find("input.googlelocation_map_search_input");
            var icon_div = $(centerControlDiv).find(".googlelocation_map_search_input_icon");
            var icon_span = icon_div.find("span");
            
            var geocoder = new google.maps.Geocoder();
            
            var data = autocomplite.autocomplete({
                delay: 250,
                matchContains: true,

                source: function (request, response) {
                    icon_span.removeClass('ic_googlelocation_map_search_pin');
                    icon_span.addClass('peep_inprogress');

                    var geocoderParams = {
                        'address': request.term
                    }

                    geocoder.geocode(geocoderParams, function (results, status) {

                        icon_span.removeClass('peep_inprogress');
                        icon_span.addClass('ic_googlelocation_map_search_pin');

                        response($.map(results.slice(0, 5), function (item) {
                            return {
                                label: item.formatted_address,
                                value: item.formatted_address,
                                item: item
                            }
                        }));
                    })
                },
                select: function (event, ui) {
                    map.fitBounds(ui.item.item.geometry.viewport);
                }
            }).data("ui-autocomplete");
            
            data._resizeMenu = function() {
                    this.menu.element.outerWidth( autocomplite.outerWidth() );
                    $(this.menu.element).addClass("googlelocation_map_search_menu");
            };
            
            var func = data._renderItem;
            data._renderItem = function( ul, item ) {
                var element = func(ul, item);
                element.find("a").prepend("<span class='ic_googlelocation_menu_item_pin'>");
                return element;
            };
        }
    }
}(locationJquey, google);

var PEEP_GoogleMap = window.PEEP_GoogleMap;
