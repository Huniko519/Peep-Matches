
var PEEP_GoogleMapLocation = function ($, google)
{

    return function (fieldName, addressFieldId, mapElementId)
    {
        var self = this;

        var geocoder;
        var map;
        var marker;
        var mapElement = $("#" + mapElementId);
        var bounds;

        var fieldId = addressFieldId;
        var mapElementId = mapElementId;
        var zoom = 9;

        var latitudeField;
        var longitudeField;
        var northEastLat;
        var northEastLng;
        var southWestLat;
        var southWestLng;
        var addressField;
        var jsonField;

        var countryRestriction = '';
        var removeValue;
        var region;
        var customMarkerIcon;
        
        var mapOptions = {
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
                    zoomControl: false
                };

        this.isValidValue = false;

        this.initialize = function (params)
        {
            var lat = params['lat'];
            var lng = params['lng'];
            var northEastLat = params['northEastLat'];
            var northEastLng = params['northEastLng'];
            var southWestLat = params['southWestLat'];
            var southWestLng = params['southWestLng'];
            
            if ( params['customMarkerIcon'] )
            {
                customMarkerIcon = params['customMarkerIcon'];
            }
            
            region = params['region'];

            geocoder = new google.maps.Geocoder();

            if (lat || lng)
            {
                this.isValidValue = true;

                var latlng = new google.maps.LatLng(lat, lng);

                mapElement.show();

                map = new window.PEEP_GoogleMap(mapElementId);
                map.initialize(mapOptions);
                map.setCenter(lat, lng);
                

                var northEast = null;
                if (northEastLat || northEastLng)
                {
                    northEast = new google.maps.LatLng(northEastLat, northEastLng);
                }

                var southWest = null;
                if (southWestLat || southWestLng)
                {
                    southWest = new google.maps.LatLng(southWestLat, southWestLng);
                }

                if (northEast && southWest)
                {
                    var bounds = new google.maps.LatLngBounds(southWest, northEast);
                    map.fitBounds(bounds);
                }
                
                if (latlng)
                {
                    map.addPoint(lat, lng, null, null, null, customMarkerIcon);
                    map.createMarkerCluster();
                }
            }

            $("#" + fieldId + "_delete_icon").click(function () {
                self.deleteValue();
            });

            countryRestriction = params['countryRestriction'];
        }

        $(function () {

            latitudeField = $('input[name="' + fieldName + '[latitude]"]');
            longitudeField = $('input[name="' + fieldName + '[longitude]"]');
            northEastLat = $('input[name="' + fieldName + '[northEastLat]"]');
            northEastLng = $('input[name="' + fieldName + '[northEastLng]"]');
            southWestLat = $('input[name="' + fieldName + '[southWestLat]"]');
            southWestLng = $('input[name="' + fieldName + '[southWestLng]"]');
            addressField = $('input[name="' + fieldName + '[address]"]');
            jsonField = $('input[name="' + fieldName + '[json]"]');
            removeValue = $('input[name="' + fieldName + '[remove]"]');

            if (jsonField.val())
            {
                self.isValidValue = true;
            }

            PEEP_GoogleMapLocationAutocomplete(fieldId, {
                delay: 250,
                matchContains: true,

                source: function (request, response) {

                    var icon = $('#' + fieldId + '_icon');
                    icon.removeClass('ic_googlemap_pin');
                    icon.addClass('peep_inprogress');

                    var geocoderParams = {
                        'address': request.term
                    }

                    if (countryRestriction)
                    {
                        geocoderParams.componentRestrictions = {country: countryRestriction};
                    }
                    
                    if (region)
                    {
                        geocoderParams.region = region;
                    }

                    geocoder.geocode(geocoderParams, function (results, status) {

                        icon.removeClass('peep_inprogress');
                        icon.addClass('ic_googlemap_pin');
                        
                        response($.map(results, function (item) {
                                    return {
                                        label: item.formatted_address,
                                        value: item.formatted_address,
                                        latitude: item.geometry.location.lat(),
                                        longitude: item.geometry.location.lng(),
                                        result: item
                                    }
                        }));
                    })
                },
                select: function (event, ui) {

                    mapElement.show();

                    self.setValue(ui.item.result)
                    self.isValidValue = true;
                }
            });
        });


        this.setValue = function (item)
        {
            var location = item.geometry.location;

            self.showDeleteIcon();
            
            if (!map)
            {
                map = new window.PEEP_GoogleMap(mapElementId);
                map.initialize(mapOptions);
            }
            
            var sw = new google.maps.LatLng(item.geometry.viewport.getSouthWest().lat(),item.geometry.viewport.getSouthWest().lng());
            var ne = new google.maps.LatLng(item.geometry.viewport.getNorthEast().lat(),item.geometry.viewport.getNorthEast().lng());

            var bounds = new google.maps.LatLngBounds(sw, ne);
            
            map.removeAllPoints();
            map.setCenter(item.geometry.location.lat(), item.geometry.location.lng());
            map.addPoint(item.geometry.location.lat(), item.geometry.location.lng(), null, null, null, customMarkerIcon);
            map.fitBounds(bounds);
            map.createMarkerCluster();
            
            addressField.val(item.formatted_address);
            longitudeField.val(item.geometry.location.lng());
            latitudeField.val(item.geometry.location.lat());
            northEastLat.val(item.geometry.viewport.getNorthEast().lat())
            northEastLng.val(item.geometry.viewport.getNorthEast().lng())
            southWestLat.val(item.geometry.viewport.getSouthWest().lat())
            southWestLng.val(item.geometry.viewport.getSouthWest().lng())
            removeValue.val(false);

            jsonField.val(JSON.stringify(item).replace('"', '\"'));
            self.isValidValue = true;
        }

        this.showDeleteIcon = function (item)
        {
            $("#" + mapElementId).show();
            $("#" + fieldId + "_icon").hide();
            $("#" + fieldId + "_delete_icon").css("display", "inline");
        }

        this.hideDeleteIcon = function (item)
        {
            $("#" + mapElementId).hide();
            $("#" + fieldId + "_icon").show();
            $("#" + fieldId + "_delete_icon").hide();
        }

        this.deleteValue = function (item)
        {
            self.hideDeleteIcon();

            $('#' + fieldId).val('');
            addressField.val('');
            longitudeField.val('');
            latitudeField.val('');
            northEastLat.val('');
            northEastLng.val('');
            southWestLat.val('');
            southWestLng.val('');
            removeValue.val(true);

            jsonField.val('');
            self.isValidValue = false;
        }

        //        google.maps.event.addListener(marker, 'drag', function() {
        //            geocoder.geocode({
        //                'latLng': marker.getPosition()
        //                }, function(results, status) {
        //                if (status == google.maps.GeocoderStatus.OK) {
        //                    if (results[0]) {
        //                        $(fieldId).val(results[0].formatted_address);
        //                        //$('#latitude').val(marker.getPosition().lat());
        //                        //$('#longitude').val(marker.getPosition().lng());
        //                    }
        //                }
        //            });
        //        });


    }
}(locationJquey, google)