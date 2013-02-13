<!doctype html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.5/leaflet.css" />
    <!--[if lte IE 8]>
        <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.5/leaflet.ie.css" />
    <![endif]-->
    <script src="http://cdn.leafletjs.com/leaflet-0.5/leaflet.js"></script>
    <style type="text/css">
        body {
            padding: 0;
            margin: 0;
        }
        html, body, #map {
            height: 100%;
        }
    </style>
</head>
<body>
    <div id="map"></div>
    <script type="text/javascript">
        var map = L.map('map').setView([38.88963, -77.00901], 13);
        L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    </script>
</body>
</html> 
