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
            array('id' => 14, 'name' => 'Place'), 
            array('id' => 8, 'name' => 'Event'), 
            array('id' => 1, 'name' => 'Document'), 
            array('id' => 6, 'name' => 'Image'), // Still Image
            array('id' => 3, 'name' => 'Video'), // Moving Image
            array('id' => 5, 'name' => 'Audio'), // Sound
        ), 
        // "Dublin Core":Coverage
        'map_coverages' => array(
            'element_id' => 38, 
            'texts' => array(
                'Pre-1800s', 
                '1800-1829', 
                '1830-1859', 
                '1860-1889', 
                '1890-1919', 
                '1920-1949', 
                //'1950-1979', 
                '1980-1999', 
                //'2000-present', 
            ), 
        ), 
        // "Item Type Metadata":Type
        'place_types' => array(
            'element_id' => 87, 
            'texts' => array(
                'Statues and Sculpture', 
                'Monuments', 
                'Memorials', 
                'Ghost Sites', 
                'Museums', 
                'Art Galleries', 
                'Landscapes', 
                'Concert Venues', 
                'Government Offices', 
            ), 
        ), 
        // "Item Type Metadata":"Event Type"
        'event_types' => array(
            'element_id' => 29, 
            'texts' => array(
                'Marches and Rallies', 
                'Encampment', 
                'Concert', 
                'Openings and Dedications', 
                'Cultural Gathering', 
                'Remembrance', 
                'Inauguration', 
                'Environmental Disaster', 
                'D.C. History', 
                'Planning and Design', 
            ), 
        ), 
    );
    
    private $_historicMapData = array(
        'Pre-1800s' => array(
            'url' => '/mallhistory/plugins/MallMap/maps/1791/{z}/{x}/{y}.jpg', 
            'title' => 'Map by Faehtz, E.F.M.', 
        ), 
        '1800-1829' => array(
            'url' => '/mallhistory/plugins/MallMap/maps/1828/{z}/{x}/{y}.jpg', 
            'title' => 'Map by Elliot, William', 
        ), 
        '1830-1859' => array(
            'url' => '/mallhistory/plugins/MallMap/maps/1858/{z}/{x}/{y}.jpg', 
            'title' => 'Map by Boschke, A.', 
        ), 
        '1860-1889' => array(
            'url' => '/mallhistory/plugins/MallMap/maps/1887/{z}/{x}/{y}.jpg', 
            'title' => 'Map by Silversparre, Axel', 
        ), 
        '1890-1919' => array(
            'url' => '/mallhistory/plugins/MallMap/maps/1917/{z}/{x}/{y}.jpg', 
            'title' => 'Map by U.S. Public Buildings Commission', 
        ), 
        '1920-1949' => array(
            'url' => '/mallhistory/plugins/MallMap/maps/1942/{z}/{x}/{y}.jpg', 
            'title' => 'Map by General Drafting Company', 
        ), 
        //'1950-1979' => array('url' => null, 'title' => null), 
        '1980-1999' => array(
            'url' => '/mallhistory/plugins/MallMap/maps/1996/{z}/{x}/{y}.jpg', 
            'title' => 'Map by Joseph Passonneau and Partners', 
        ), 
        //'2000-present' => array('url' => null, 'title' => null), 
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
        
        $joins[] = "$db->Item AS items ON items.id = locations.item_id";
        
        // Filter items by item type.
        if ($request->getParam('it')) {
            $wheres[] = $db->quoteInto("items.item_type_id = ?", $request->getParam('it'), Zend_Db::INT_TYPE);
        }
        
        // Filter items by element texts.
        if ($request->getParam('et')) {
            $i = 1;
            foreach ($request->getParam('et') as $elementId => $texts) {
                foreach ($texts as $text) {
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
        }
        
        // Build the SQL.
        $sql = "SELECT items.id, locations.latitude, locations.longitude\nFROM $db->Location AS locations";
        foreach ($joins as $join) {
            $sql .= "\nJOIN $join";
        }
        foreach ($wheres as $key => $where) {
            $sql .= (0 == $key) ? "\nWHERE" : "\nAND";
            $sql .= " $where";
        }
        
        // Once all item IDs have been retrieved, fetch all the item data that 
        // is needed for the geoJSON response.
        $items = array();
        foreach ($db->query($sql)->fetchAll() as $row) {
            $item = get_record_by_id('item', $row['id']);
            $items[] = array(
                'id' => $row['id'], 
                'latitude' => $row['latitude'], 
                'longitude' => $row['longitude'], 
                'title' => metadata($item, array('Dublin Core', 'Title')), 
                'thumbnail' => item_image('thumbnail', array(), 0, $item), 
            );
        }
        
        // Build geoJSON: http://www.geojson.org/geojson-spec.html
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
                    'thumbnail' => $item['thumbnail'], 
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
    
    /**
     * Get data about the selected historical map.
     */
    public function historicMapDataAction()
    {
        // Process only AJAX requests.
        if (!$this->_request->isXmlHttpRequest()) {
            throw new Omeka_Controller_Exception_404;
        }
        $data = $this->_historicMapData[$this->getRequest()->getParam('text')];
        $this->_helper->json($data);
    }

}
