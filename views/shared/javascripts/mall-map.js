jQuery(document).ready(function () {
    
    var MAP_URL_TEMPLATE = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    var MAP_CENTER = [38.8891, -77.02949];
    var MAP_ZOOM = 15;
    var MAP_MIN_ZOOM = 14;
    var MAP_MAX_ZOOM = 18;
    var MAP_MAX_BOUNDS = [[38.79164, -77.17232], [38.99583, -76.90917]];
    var LOCATE_BOUNDS = [[38.87814, -77.05656], [38.90025, -77.00678]];
    
    var map;
    var historicMapLayer;
    var geoJsonLayer;
    var jqXhr;
    var locationMarker;
    
    // Set the base map layer.
    map = L.map('map', {
        center: MAP_CENTER, 
        zoom: MAP_ZOOM, 
        minZoom: MAP_MIN_ZOOM, 
        maxZoom: MAP_MAX_ZOOM, 
        maxBounds: MAP_MAX_BOUNDS, 
        zoomControl: false, 
    });
    map.addLayer(L.tileLayer(MAP_URL_TEMPLATE));
    map.addControl(L.control.zoom({position: 'bottomright'}));
    map.attributionControl.setPrefix('');
    
    // Locate the user.
    map.locate({watch: true});
    
    // Retain previous form state, if needed.
    retainFormState();
    
    // Add all markers by default, or retain previous marker state.
    doFilters();
    
    /*
     * Handle location found.
     */
    map.on('locationfound', function (e) {
        // User within location bounds. Set the location marker.
        if (L.latLngBounds(LOCATE_BOUNDS).contains(e.latlng)) {
            if (locationMarker) {
                map.removeLayer(locationMarker);
            }
            map.panTo(e.latlng).setZoom(MAP_MAX_ZOOM);
            locationMarker = L.marker(e.latlng);
            locationMarker.addTo(map).
                bindPopup("You are within " + e.accuracy / 2 + " meters from this point");
        // User outside location bounds.
        } else {
            map.stopLocate();
            var miles = Math.ceil((e.latlng.distanceTo(map.options.center) * 0.000621371) * 100) / 100;
            console.log('Location out of bounds. You are ' + miles + ' miles away.');
        }
    });
    
    /*
     * Handle location error.
     */
    map.on('locationerror', function () {
        map.stopLocate();
        console.log('Could not find your location.');
    });
    
    /*
     * Handle the filter form.
     */
    jQuery('#filter-button').click(function(e) {
        e.preventDefault();
        var clicks = jQuery(this).data('clicks');
        if (clicks) {
            jQuery(this).removeClass('on');
            jQuery(this).find('.screen-reader-text').html('Filters');
            jQuery('#filters').animate({left: '+=100%'}, 200, 'linear');
        } else {
            jQuery(this).addClass('on');
            jQuery(this).find('.screen-reader-text').html('Back to Map');
            jQuery('#filters').animate({left: '-=100%'}, 200, 'linear');
        }
        jQuery(this).data('clicks', !clicks);
    });
    
    /*
     * Revert form to default and display all markers.
     */
    jQuery('#all-button').click(function() {
        revertFormState();
    });
    
    /*
     * Filter historic map layer.
     */
    jQuery('#map-coverage').change(function () {
        if (historicMapLayer) {
            removeHistoricMapLayer();
        }
        if ('0' == jQuery('#map-coverage').val()) {
            jQuery('#toggle-map-button').hide();
        } else {
            addHistoricMapLayer();
        }
        doFilters();
    });
    
    /*
     * Filter item type.
     */
    jQuery('#item-type').change(function () {
        if ('Place' == jQuery(this).find(':selected').text()) {
            jQuery('#place-type-div').show({duration: 'fast'});
        } else {
            // Reset and hide the place type select.
            jQuery('input[name=place-type]').removeAttr('checked');
            jQuery('#place-type-div').hide({duration: 'fast'});
        }
        if ('Event' == jQuery(this).find(':selected').text()) {
            jQuery('#event-type-div').show({duration: 'fast'});
        } else {
            // Reset and hide the event type checkboxes.
            jQuery('input[name=event-type]').removeAttr('checked');
            jQuery('#event-type-div').hide({duration: 'fast'});
        }
        doFilters();
    });
    
    /*
     * Filter place type.
     */
    jQuery('input[name=place-type]').change(function () {
        // Handle all place types checkbox.
        var placeTypeAll = jQuery('input[name=place-type-all]');
        if (jQuery('input[name=place-type]:checked').length) {
            placeTypeAll.prop('checked', false).parent().removeClass('on');
        } else {
            placeTypeAll.prop('checked', true).parent().addClass('on');
        }
        doFilters();
    });
    
    /*
     * Handle the all place types checkbox.
     */
    jQuery('input[name=place-type-all]').change(function () {
        // Uncheck all place types.
        jQuery('input[name=place-type]:checked').prop('checked', false).
            parent().removeClass('on');
        doFilters();
    });
    
    /*
     * Filter event type.
     */
    jQuery('input[name=event-type]').change(function () {
        // Handle all event types checkbox.
        var eventTypeAll = jQuery('input[name=event-type-all]');
        if (jQuery('input[name=event-type]:checked').length) {
            eventTypeAll.prop('checked', false).parent().removeClass('on');
        } else {
            eventTypeAll.prop('checked', true).parent().addClass('on');
        }
        doFilters();
    });
    
    /*
     * Handle the all event types checkbox.
     */
    jQuery('input[name=event-type-all]').change(function () {
        // Uncheck all event types.
        jQuery('input[name=event-type]:checked').prop('checked', false).
            parent().removeClass('on');
        doFilters();
    });
    
    /*
     * Toggle historic map layer on and off.
     */
    jQuery('#toggle-map-button').click(function () {
        var clicks = jQuery(this).data('clicks');
        if (clicks) {
            jQuery(this).addClass('on');
            jQuery(this).find('.screen-reader-text').html('Map On');
            map.addLayer(historicMapLayer);
        } else {
            if (historicMapLayer) {
                jQuery(this).removeClass('on');
                jQuery(this).find('.screen-reader-text').html('Map Off');
                map.removeLayer(historicMapLayer);
            }
        }
        jQuery(this).data('clicks', !clicks);
    });
    
    /*
     * Toggle map filters
     */
    jQuery('#filters div label').click(function() {
        var clicks = jQuery(this).find('input[type=checkbox]').is(':checked');
        if (clicks) {
            jQuery(this).addClass('on');
        } else {
            jQuery(this).removeClass('on');
        }
    });
    
    /*
     * Filter markers.
     * 
     * This must be called on every form change.
     */
    function doFilters() {
        // Prevent concurrent filter requests.
        if (jqXhr) {
            jqXhr.abort()
        }
        
        // Remove the current markers.
        if (geoJsonLayer) {
            map.removeLayer(geoJsonLayer);
        }
        
        var mapCoverage = jQuery('#map-coverage');
        var itemType = jQuery('#item-type');
        var placeTypes = jQuery('input[name=place-type]:checked');
        var eventTypes = jQuery('input[name=event-type]:checked');
        
        // Prepare POST data object for request.
        var postData = {
            placeTypes: [], 
            eventTypes: [], 
        };
        
        // Handle each filter, adding to the POST data object.
        if ('0' != mapCoverage.val()) {
            postData['mapCoverage'] = mapCoverage.val();
        }
        if ('0' != itemType.val()) {
            postData['itemType'] = itemType.val();
        }
        if (placeTypes.length) {
            placeTypes.each(function () {
                postData.placeTypes.push(this.value);
            });
        }
        if (eventTypes.length) {
            eventTypes.each(function () {
                postData.eventTypes.push(this.value);
            });
        }
        
        // Make the POST request, handle the GeoJSON response, and add markers.
        jqXhr = jQuery.post('mall-map/index/filter', postData, function (response) {
            var item = (1 == response.features.length) ? 'item' : 'items';
            jQuery('#marker-count').text(response.features.length + " " + item);
            geoJsonLayer = L.geoJson(response, {
                onEachFeature: function (feature, layer) {
                    layer.bindPopup(
                        '<a href="' + feature.properties.url + '">' + feature.properties.title + '</a><br/>' + 
                        feature.properties.thumbnail
                    );
                }
            });
            geoJsonLayer.addTo(map);
        });
    }
    
    /*
     * Add the historic map layer.
     */
    function addHistoricMapLayer()
    {
        // Get the historic map data
        var getData = {'text': jQuery('#map-coverage').val()};
        jQuery.get('mall-map/index/historic-map-data', getData, function (response) {
            historicMapLayer = L.tileLayer(
                response.url, 
                {tms: true, opacity: 1.00}
            );
            map.addLayer(historicMapLayer);
            jQuery('#toggle-map-button').show();
            
            // Set the map title as the map attribution prefix.
            map.attributionControl.setPrefix(response.title);
        });
    }
    
    /*
     * Remove the historic map layer.
     */
    function removeHistoricMapLayer()
    {
        jQuery('#toggle-map-button').data('clicks', false).hide();
        map.removeLayer(historicMapLayer);
        map.attributionControl.setPrefix('');
    }
    
    /*
     * Revert to default (original) form state.
     */
    function revertFormState()
    {
        if (historicMapLayer) {
            removeHistoricMapLayer();
        }
        
        jQuery('#map-coverage').val('0');
        jQuery('#item-type').val('0');
        
        jQuery('#place-type-div').hide({duration: 'fast'});
        jQuery('input[name=place-type-all]').prop('checked', true).
            parent().addClass('on');
        jQuery('input[name=place-type]:checked').prop('checked', false).
            parent().removeClass('on');
        
        jQuery('#event-type-div').hide({duration: 'fast'});
        jQuery('input[name=event-type-all]').prop('checked', true).
            parent().addClass('on');
        jQuery('input[name=event-type]:checked').prop('checked', false).
            parent().removeClass('on');
        
        doFilters();
    }
    
    /*
     * Retain previous form state.
     * 
     * Acts on the assumption that all browsers will preserve the form state 
     * when navigating back to the map from another page.
     */
    function retainFormState()
    {
        if ('0' != jQuery('#map-coverage').val()) {
            addHistoricMapLayer();
        }
        if ('Place' == jQuery('#item-type').find(':selected').text()) {
            var placeTypes = jQuery('input[name=place-type]:checked');
            if (placeTypes.length) {
                jQuery('input[name=place-type-all]').parent().removeClass('on');
                placeTypes.parent().addClass('on');
            }
            jQuery('#place-type-div').show({duration: 'fast'});
        }
        if ('Event' == jQuery('#item-type').find(':selected').text()) {
            var eventTypes = jQuery('input[name=event-type]:checked');
            if (eventTypes.length) {
                jQuery('input[name=event-type-all]').parent().removeClass('on');
                eventTypes.parent().addClass('on');
            }
            jQuery('#event-type-div').show({duration: 'fast'});
        }
    }
    
    map.on('click', function (e) {
        console.log("Map clicked at zoom " + map.getZoom() + '; ' + e.latlng);
    });
});
