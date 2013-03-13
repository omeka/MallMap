jQuery(document).ready(function () {
    var map = L.map('map').setView([38.89083, -77.02849], 15);
    var historicMapLayer;
    var geoJsonLayer;
    
    // Add the base layer.
    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    
    jQuery('#filter-button').click(function(e) {
        e.preventDefault();
        var clicks = jQuery(this).data('clicks');
        if (clicks) {
            jQuery('#filters').animate({
                left: '+=30%'
            }, 200, 'linear');
        } else {
            jQuery('#filters').animate({
                left: '-=30%'
            }, 200, 'linear');
        }
        jQuery(this).data('clicks', !clicks);
    });
    
    // Filter historic map layer.
    jQuery('#map-coverage').change(function () {
        if (historicMapLayer) {
            jQuery('#toggle-map-button').data('clicks', false);
            map.removeLayer(historicMapLayer);
        }
        if ('0' == jQuery('#map-coverage').val()) {
            jQuery('#toggle-map-button').hide();
            return;
        }
        historicMapLayer = L.tileLayer(
            'http://localhost/omeka/plugins/MallMap/' + jQuery(this).val() + '/{z}/{x}/{y}.jpg', 
            {tms: true, opacity: 1.00}
        );
        map.addLayer(historicMapLayer);
        jQuery('#toggle-map-button').show();
        
        // Set the map title as the map attribution prefix.
        map.attributionControl.setPrefix(jQuery(this).find(':selected').attr('title'));
        
        doFilters();
    });
    
    // Filter item type.
    jQuery('#item-type').change(function () {
        if ('Place' == jQuery(this).find(':selected').text()) {
            jQuery('#place-type-div').show({duration: 'fast'});
        } else {
            // Reset and hide the place type select.
            jQuery('#place-type').val('0');
            jQuery('#place-type-div').hide({duration: 'fast'});
        }
        if ('Event' == jQuery(this).find(':selected').text()) {
            jQuery('#event-type-div').show({duration: 'fast'});
        } else {
            // Reset and hide the event type select.
            jQuery('#event-type').val('0');
            jQuery('#event-type-div').hide({duration: 'fast'});
        }
        doFilters();
    });
    
    // Filter place type.
    jQuery('#place-type').change(function () {
        doFilters();
    });
    
    // Filter event type.
    jQuery('#event-type').change(function () {
        doFilters();
    });
    
    // Toggle historic map layer on and off.
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
    
    // Filter markers after every form change.
    function doFilters() {
        var mapCoverage = jQuery('#map-coverage');
        var itemType = jQuery('#item-type');
        var placeType = jQuery('#place-type');
        var eventType = jQuery('#event-type');
        
        var getData = {et: {}};
        if ('0' != mapCoverage.val()) {
            getData.et[mapCoverageElementId] = mapCoverage.val();
        }
        if ('0' != itemType.val()) {
            getData.it = itemType.val();
        }
        if ('0' != placeType.val()) {
            getData.et[placeTypeElementId] = placeType.val();
        }
        if ('0' != eventType.val()) {
            getData.et[eventTypeElementId] = eventType.val();
        }
        
        // Make the request, handle the geoJson response, and add markers.
        jQuery.get('mall-map/index/filter', getData, function (response) {
            if (geoJsonLayer) {
                map.removeLayer(geoJsonLayer);
            }
            geoJsonLayer = L.geoJson(response, {
                onEachFeature: function (feature, layer) {
                    layer.bindPopup(
                        '<a href="' + feature.properties.url + '">' + feature.properties.title + '</a><br/>' + 
                        feature.properties.description
                    );
                }
            });
            geoJsonLayer.addTo(map);
        });
    }
    
    function onMapClick(e) {
        console.log("You clicked the map at zoom " + map.getZoom() + '; ' + e.latlng);
    }
    map.on('click', onMapClick);
});

