<?php queue_css_file('style'); ?>
<?php queue_js_file('global'); ?>
<!doctype html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.5/leaflet.css" />
    <!--[if lte IE 8]>
        <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.5/leaflet.ie.css" />
    <![endif]-->
    <?php echo head_css(); ?>
    <script src="http://cdn.leafletjs.com/leaflet-0.5/leaflet.js"></script>
    <?php echo head_js(); ?>
</head>
<body>
    <a href="#" id="filter-button">Filters</a>
    <div id="filters">
        <h1>Select Filters</h1>
        <label for="item-type">Item Type</label>
        <select id="item-type" name="item-type">
            <option>Monument</option>
            <option>Home</option>
            <option>Museum</option>
        </select>
        <label for="time-period">Time Period</label>
        <select id="time-period" name="time-period">
            <option>Select below...</option>
            <option value="1791">1791</option>
            <option value="1828">1828</option>
            <option value="1858">1858</option>
            <option value="1887">1887</option>
            <option value="1917">1917</option>
            <option value="1942">1942</option>
            <option value="1996">1996</option>
        </select>
        <label for="toggle-map">
        <input type="checkbox" id="toggle-map"/>Toggle Map
        </label>
    </div>
    <div id="map"></div>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            var map = L.map('map').setView([38.89083, -77.02849], 15);
            var historicMapLayer;
            
            // Add the base layer.
            L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            
            // Change historic map layer.
            jQuery('#time-period').change(function () {
                jQuery('#toggle-map').attr('checked', false)
                if (historicMapLayer) {
                    map.removeLayer(historicMapLayer);
                }
                historicMapLayer = L.tileLayer(
                    'http://localhost/omeka/plugins/MallMap/' + jQuery(this).val() + '/{z}/{x}/{y}.jpg', 
                    {tms: true, opacity: 0.85}
                );
                map.addLayer(historicMapLayer);
            });
            
            // Toggle historic map layer.
            jQuery('#toggle-map').change(function () {
                if (this.checked) {
                    if (historicMapLayer) {
                        map.removeLayer(historicMapLayer);
                    }
                } else {
                    map.addLayer(historicMapLayer);
                }
            });
            function onMapClick(e) {
                console.log("You clicked the map at zoom " + map.getZoom() + '; ' + e.latlng);
            }
            map.on('click', onMapClick);
        });
    </script>
</body>
</html> 
