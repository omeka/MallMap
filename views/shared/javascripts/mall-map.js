jQuery(document).ready(function () {
    var map;
    var historicMapLayer;
    var geoJsonLayer;
    var jqXhr;
    
    // Set the base layer.
    map = L.map('map').
        setView([38.89083, -77.02849], 15).
        addLayer(L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'));
    map.attributionControl.setPrefix('');
    
    /*
     * Handle the filter form.
     */
    jQuery('#filter-button').click(function(e) {
        e.preventDefault();
        var clicks = jQuery(this).data('clicks');
        if (clicks) {
            jQuery('#filters').animate({
                left: '+=100%'
            }, 200, 'linear');
        } else {
            jQuery('#filters').animate({
                left: '-=100%'
            }, 200, 'linear');
        }
        jQuery(this).data('clicks', !clicks);
    });
    
    /*
     * Filter historic map layer.
     */
    jQuery('#map-coverage').change(function () {
        if (historicMapLayer) {
            jQuery('#toggle-map-button').data('clicks', false);
            map.removeLayer(historicMapLayer);
            map.attributionControl.setPrefix('');
        }
        if ('0' == jQuery('#map-coverage').val()) {
            jQuery('#toggle-map-button').hide();
        } else {
            // Get the map data and set the historic map layer.
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
        doFilters();
    });
    
    /*
     * Filter event type.
     */
    jQuery('input[name=event-type]').change(function () {
        doFilters();
    });
    
    /*
     * Toggle historic map layer on and off.
     */
    jQuery('#toggle-map-button').click(function () {
        var clicks = jQuery(this).data('clicks');
        if (clicks) {
            map.addLayer(historicMapLayer);
        } else {
            if (historicMapLayer) {
                map.removeLayer(historicMapLayer);
            }
        }
        jQuery(this).data('clicks', !clicks);
    });
    
    /*
     * Filter markers. This must be called on every form change.
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
        
        // Handle each filter, adding to the GET data object.
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
        
        // Filter markers only if the GET data has changed. Otherwise the 
        // request will return all markers.
        if (!postData.mapCoverage && !postData.itemType && 
            !postData.placeTypes.length && !postData.eventTypes.length) {
            return;
        }
        
        // Make the POST request, handle the GeoJSON response, and add markers.
        jqXhr = jQuery.post('mall-map/index/filter', postData, function (response) {
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
    
    map.on('click', function (e) {
        console.log("Map clicked at zoom " + map.getZoom() + '; ' + e.latlng);
    });
});
