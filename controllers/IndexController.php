<?php
/**
 * Mall Map
 * 
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Mall Map controller.
 * 
 * @package Omeka\Plugins\Mall
 */
class MallMap_IndexController extends Omeka_Controller_AbstractActionController
{
    private $_formData = array(
        'item_types' => array(
            array('id' => 1, 'name' => 'Place'), 
            array('id' => 2, 'name' => 'Event'), 
            array('id' => 3, 'name' => 'Document'), 
            array('id' => 4, 'name' => 'Image'), // Still Image
            array('id' => 5, 'name' => 'Video'), // Moving Image
            array('id' => 6, 'name' => 'Audio'), // Sound
        ), 
        // "Dublin Core":Coverage
        'map_coverages' => array(
            'element_id' => 1, 
            'texts' => array(
                array('text' => 1791, 'title' => ''), 
                array('text' => 1828, 'title' => ''), 
                array('text' => 1858, 'title' => ''), 
                array('text' => 1887, 'title' => ''), 
                array('text' => 1917, 'title' => ''), 
                array('text' => 1942, 'title' => ''), 
                array('text' => 1996, 'title' => ''), 
            ), 
        ), 
        // "Item Type Metadata":Type
        'place_types' => array(
            'element_id' => 12, 
            'texts' => array(
                array('text' => 'Statues and Sculpture'), 
                array('text' => 'Monuments'), 
                array('text' => 'Memorials'), 
                array('text' => 'Ghost Sites'), 
                array('text' => 'Museums'), 
                array('text' => 'Art Galleries'), 
                array('text' => 'Landscapes'), 
                array('text' => 'Concert Venues'), 
                array('text' => 'Government Offices'), 
            ), 
        ), 
        // "Item Type Metadata":"Event Type"
        'event_types' => array(
            'element_id' => 123, 
            'texts' => array(
                array('text' => 'Marches and Rallies'), 
                array('text' => 'Encampment'), 
                array('text' => 'Concert'), 
                array('text' => 'Openings and Dedications'), 
                array('text' => 'Cultural Gathering'), 
                array('text' => 'Remembrance'), 
                array('text' => 'Inauguration'), 
                array('text' => 'Environmental Disaster'), 
                array('text' => 'D.C. History'), 
                array('text' => 'Planning and Design'), 
            ), 
        ), 
    );
    
    public function indexAction()
    {
        $this->view->form_data = $this->_formData;
    }
    
    /**
     * Filter items that have been geolocated by the geolocation plugin.
     * 
     * Since this is mobile-first, optimized SQL queries are preferable to using 
     * the Omeka API.
     */
    public function filterAction()
    {
        // Process only AJAX requests.
        if (!$this->_request->isXmlHttpRequest()) {
            throw new Omeka_Controller_Exception_404;
        }
        
        $db = $this->_helper->db->getDb();
        $request = $this->getRequest();
        $joins = array();
        $wheres = array();
        
        $joins[] = "$db->Location AS locations ON items.id = locations.item_id";
        
        // Filter items by item type.
        if ($request->getParam('it')) {
            $wheres[] = $db->quoteInto("items.item_type_id = ?", $request->getParam('it'), Zend_Db::INT_TYPE);
        }
        
        // Filter items by element texts.
        if ($request->getParam('et')) {
            $i = 1;
            foreach ($request->getParam('et') as $elementId => $text) {
                $alias = "et$i";
                $joins[] = $db->quoteInto(
                    "$db->ElementText AS $alias ON $alias.record_id = items.id " . 
                    "AND $alias.record_type = 'Item' " . 
                    "AND $alias.element_id = ?", 
                    $elementId
                );
                $wheres[] = $db->quoteInto("$alias.text = ?", $text);
                $i++;
            }
        }
        
        // Build the SQL.
        $sql = "SELECT items.id, locations.latitude, locations.longitude FROM $db->Item AS items";
        foreach ($joins as $join) {
            $sql .= "\nJOIN $join";
        }
        foreach ($wheres as $key => $where) {
            $sql .= (0 == $key) ? "\nWHERE" : "\nAND";
            $sql .= " $where";
        }
        
//exit($sql);
        
        // Once all item IDs have been retrieved, fetch all the item data that 
        // is needed for the geoJSON response.
        //$items = $db->query($sql);
        
        // Dummy data for testing.
        $items = array(
            array('id' => 1, 'latitude' => 38.89768, 'longitude' => -77.03656, 'title' => 'The White House', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quam lacus, sagittis quis consequat et, porttitor dictum ante. Donec sed porttitor nisi. Vestibulum ut varius massa.'), 
            array('id' => 2, 'latitude' => 38.88981, 'longitude' => -77.00905, 'title' => 'United States Capitol', 'description' => 'Nunc nec sem sed dolor feugiat consectetur at a eros. Mauris semper, lectus nec pharetra adipiscing, risus dui ultricies est, a suscipit lorem nulla eget felis.'), 
            array('id' => 3, 'latitude' => 38.88947, 'longitude' => -77.03525, 'title' => 'Washington Monument', 'description' => 'In lobortis nibh eget odio imperdiet pretium. Vivamus sollicitudin sollicitudin aliquet. Fusce congue mi eget justo aliquam non posuere purus tincidunt. Curabitur eu magna risus, ut eleifend mauris.'), 
            array('id' => 4, 'latitude' => 38.88877, 'longitude' => -77.02597, 'title' => 'Smithsonian Institution', 'description' => 'Duis consectetur elit quis lacus hendrerit rutrum. Morbi sem elit, ornare at sollicitudin sed, facilisis vitae neque. Etiam gravida interdum gravida. In hac habitasse platea dictumst.'), 
        );
        
        // Build geoJSON
        // http://www.geojson.org/geojson-spec.html
        // http://leafletjs.com/reference.html#geojson
        // http://leafletjs.com/examples/geojson.html
        $data = array('type' => 'FeatureCollection', 'features' => array());
        foreach ($items as $item) {
            $data['features'][] = array(
                'type' => 'Feature', 
                'geometry' => array(
                    'type' => 'Point', 
                    'coordinates' => array($item['longitude'], $item['latitude']), 
                ), 
                'properties' => array(
                    'title' => $item['title'], 
                    'description' => $item['description'], 
                    'url' => url(array('module' => 'default', 
                                       'controller' => 'items', 
                                       'action' => 'show', 
                                       'id' => $item['id']), 
                                 'id'), 
                ), 
            );
        }
        
        $this->_helper->json($data);
    }

}
