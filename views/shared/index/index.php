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
            <option>1780 - 1800</option>
            <option>1800 - 1850</option>
            <option>1850 - 1900</option>
            <option>1900 - 1950</option>
            <option>1950 - present</option>
        </select>
        <label for="has-activity">
        <input type="checkbox" />Activity available?
        </label>
        
    </div>
    <div id="map"></div>
    <script type="text/javascript">
        var map = L.map('map').setView([38.88963, -77.00901], 13);
        L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    </script>
</body>
</html> 
