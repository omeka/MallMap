<!doctype html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <?php echo $this->headLink(); ?>
    <?php echo $this->headScript(); ?>
    <link href='http://fonts.googleapis.com/css?family=Raleway:300,600' rel='stylesheet' type='text/css'>
    <script type="text/javascript">
        var hackRemember = L.DomUtil.TRANSITION;
        L.DomUtil.TRANSITION = false;
    </script>
    <?php echo js_tag('leaflet.markercluster'); ?>
    <script type="text/javascript">
        L.DomUtil.TRANSITION = hackRemember;
    </script>
</head>
<body>
    <header>
        <span id="marker-count"></span>
        <a href="#" id="filter-button"><span class="screen-reader-text">Filters</span></a>
        <a href="#" id="all-button" class="on">Show All</a>
    </header>
    <?php echo link_to_home_page('<span class="screen-reader-text">Home</span>', array('id' => 'home-button')); ?>
    <a href="#" id="locate-button" class="disabled"><span class="screen-reader-text">Make me center</span></a>
    <a href="#" id="toggle-map-button" class="on" style="display: none;"><span class="screen-reader-text">Map On</span></a>
    <a href="#" class="back-button"><span class="screen-reader-text">Back to Map</span></a>
    <div id="info-panel" style="display: none;">
        <div id="info-panel-content"></div>
    </div>
    <div id="dialog"></div>
    <div id="filters">
        <h1>Select Filters</h1>
        <label for="map-coverage">Map Era</label>
        <select id="map-coverage" name="map-coverage">
            <option value="0">All Map Eras</option>
            <?php foreach ($this->map_coverages as $map_coverage): ?>
            <option value="<?php echo $map_coverage; ?>"><?php echo $map_coverage; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="item-type">Item Type</label>
        <select id="item-type" name="item-type">
            <option value="0">All Item Types</option>
            <?php foreach ($this->item_types as $item_type_id => $item_type): ?>
            <option value="<?php echo $item_type_id; ?>"><?php echo $item_type; ?></option>
            <?php endforeach; ?>
        </select>
        <div id="place-type-div" style="display: none;">
            <p>Place Types</p>
            <label class="on"><input type="checkbox" name="place-type-all" value="0" checked="checked"/> All Place Types</label><br />
            <?php foreach ($this->place_types as $place_type): ?>
            <label><input type="checkbox" name="place-type" value="<?php echo htmlspecialchars($place_type); ?>" /> <?php echo $place_type; ?></label><br />
            <?php endforeach; ?>
        </div>
        <div id="event-type-div" style="display: none;">
            <p>Event Types</p>
            <label class="on"><input type="checkbox" name="event-type-all" value="0" checked="checked"/> All Event Types</label><br />
            <?php foreach ($this->event_types as $event_type): ?>
            <label><input type="checkbox" name="event-type" value="<?php echo htmlspecialchars($event_type); ?>" /> <?php echo $event_type; ?></label><br />
            <?php endforeach; ?>
        </div>
    </div>
    <div id="map"></div>
</body>
</html> 
