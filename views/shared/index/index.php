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
    <script type="text/javascript">
        var mapCoverageElementId = <?php echo $this->form_data['map_coverages']['element_id'] ?>;
        var placeTypeElementId = <?php echo $this->form_data['place_types']['element_id'] ?>;
        var eventTypeElementId = <?php echo $this->form_data['event_types']['element_id'] ?>;
    </script>
</head>
<body>
    <a href="#" id="filter-button">Filters</a>
    <a href="#" id="toggle-map-button" style="display: none;">Toggle Map</a>
    <div id="filters">
        <h1>Select Filters</h1>
        <label for="map-coverage">Map Era</label>
        <select id="map-coverage" name="map-coverage">
            <option value="0">Select below...</option>
            <?php foreach ($this->form_data['map_coverages']['texts'] as $map_coverage): ?>
            <option value="<?php echo $map_coverage['text']; ?>" title="<?php echo htmlspecialchars($map_coverage['title']); ?>"><?php echo $map_coverage['text']; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="item-type">Item Type</label>
        <select id="item-type" name="item-type">
            <option value="0">Select below...</option>
            <?php foreach ($this->form_data['item_types'] as $item_type): ?>
            <option value="<?php echo $item_type['id']; ?>"><?php echo $item_type['name']; ?></option>
            <?php endforeach; ?>
        </select>
        <div id="place-type-div" style="display: none;">
            <?php foreach ($this->form_data['place_types']['texts'] as $place_type): ?>
            <label><input type="checkbox" name="place-type" value="<?php echo htmlspecialchars($place_type['text']); ?>" /> <?php echo $place_type['text']; ?></label><br />
            <?php endforeach; ?>
        </div>
        <div id="event-type-div" style="display: none;">
            <?php foreach ($this->form_data['event_types']['texts'] as $event_type): ?>
            <label><input type="checkbox" name="event-type" value="<?php echo htmlspecialchars($event_type['text']); ?>" /> <?php echo $event_type['text']; ?></label><br />
            <?php endforeach; ?>
        </div>
    </div>
    <div id="map"></div>
</body>
</html> 
