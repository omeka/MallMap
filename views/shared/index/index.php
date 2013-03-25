<?php queue_css_file('mall-map'); ?>
<?php queue_js_file('mall-map'); ?>
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
    <a href="#" id="toggle-map-button" style="display: none;">Toggle Map</a>
    <div id="filters">
        <h1>Select Filters</h1>
        <label for="map-coverage">Map Era</label>
        <select id="map-coverage" name="map-coverage">
            <option value="0">Select below...</option>
            <?php foreach ($this->map_coverages as $map_coverage): ?>
            <option value="<?php echo $map_coverage; ?>"><?php echo $map_coverage; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="item-type">Item Type</label>
        <select id="item-type" name="item-type">
            <option value="0">Select below...</option>
            <?php foreach ($this->item_types as $item_type_id => $item_type): ?>
            <option value="<?php echo $item_type_id; ?>"><?php echo $item_type; ?></option>
            <?php endforeach; ?>
        </select>
        <div id="place-type-div" style="display: none;">
            <p>Place Types</p>
            <?php foreach ($this->place_types as $place_type): ?>
            <label><input type="checkbox" name="place-type" value="<?php echo htmlspecialchars($place_type); ?>" /> <?php echo $place_type; ?></label><br />
            <?php endforeach; ?>
        </div>
        <div id="event-type-div" style="display: none;">
            <p>Event Types</p>
            <?php foreach ($this->event_types as $event_type): ?>
            <label><input type="checkbox" name="event-type" value="<?php echo htmlspecialchars($event_type); ?>" /> <?php echo $event_type; ?></label><br />
            <?php endforeach; ?>
        </div>
    </div>
    <div id="map"></div>
</body>
</html> 
