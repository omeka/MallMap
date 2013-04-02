<?php
/**
 * Mall Map
 * 
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Mall Map controller
 * 
 * @package Omeka\Plugins\Mall
 */
class MallMap_IndexController extends Omeka_Controller_AbstractActionController
{
    /**
     * Filterable item type IDs
     */
    const ITEM_TYPE_ID_DOCUMENT     = 1;
    const ITEM_TYPE_ID_MOVING_IMAGE = 3;
    const ITEM_TYPE_ID_SOUND        = 5;
    const ITEM_TYPE_ID_STILL_IMAGE  = 6;
    const ITEM_TYPE_ID_EVENT        = 8;
    const ITEM_TYPE_ID_PLACE        = 14;
    
    /**
     * Filterable element IDs
     */
    const ELEMENT_ID_EVENT_TYPE   = 29;
    const ELEMENT_ID_MAP_COVERAGE = 38;
    const ELEMENT_ID_PLACE_TYPE   = 87;
    
    /**
     * @var array Filterable item types in display order
     */
    private $_itemTypes = array(
        self::ITEM_TYPE_ID_PLACE        => 'Place', 
        self::ITEM_TYPE_ID_EVENT        => 'Event', 
        self::ITEM_TYPE_ID_DOCUMENT     => 'Document', 
        self::ITEM_TYPE_ID_STILL_IMAGE  => 'Image', // Still Image
        self::ITEM_TYPE_ID_MOVING_IMAGE => 'Video', // Moving Image
        self::ITEM_TYPE_ID_SOUND        => 'Audio', // Sound
    );
    
    /**
     * @var array Data used when adding the historic map layer.
     */
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
    
    /**
     * Display the map.
     */
    public function indexAction()
    {
        $simpleVocabTerm = $this->_helper->db->getTable('SimpleVocabTerm');
        
        $mapCoverages = $simpleVocabTerm->findByElementId(self::ELEMENT_ID_MAP_COVERAGE);
        $placeTypes = $simpleVocabTerm->findByElementId(self::ELEMENT_ID_PLACE_TYPE);
        $eventTypes = $simpleVocabTerm->findByElementId(self::ELEMENT_ID_EVENT_TYPE);
        
        $this->view->item_types = $this->_itemTypes;
        $this->view->map_coverages = explode("\n", $mapCoverages->terms);
        $this->view->place_types = explode("\n", $placeTypes->terms);
        $this->view->event_types = explode("\n", $eventTypes->terms);
    }
    
    /**
     * Filter items that have been geolocated by the Geolocation plugin.
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
        $joins = array("$db->Item AS items ON items.id = locations.item_id");
        $wheres = array("items.public = 1");
        
        // Filter item type.
        if ($request->getParam('itemType')) {
            $wheres[] = $db->quoteInto("items.item_type_id = ?", $request->getParam('itemType'), Zend_Db::INT_TYPE);
        }
        // Filter map coverage.
        if ($request->getParam('mapCoverage')) {
            $alias = "map_coverage";
            $joins[] = "$db->ElementText AS $alias ON $alias.record_id = items.id AND $alias.record_type = 'Item' " 
                     . $db->quoteInto("AND $alias.element_id = ?", self::ELEMENT_ID_MAP_COVERAGE);
            $wheres[] = $db->quoteInto("$alias.text = ?", $request->getParam('mapCoverage'));
        }
        // Filter place types (inclusive).
        if ($request->getParam('placeTypes')) {
            $alias = "place_types";
            $joins[] = "$db->ElementText AS $alias ON $alias.record_id = items.id AND $alias.record_type = 'Item' " 
                     . $db->quoteInto("AND $alias.element_id = ?", self::ELEMENT_ID_PLACE_TYPE);
            $placeTypes = array();
            foreach ($request->getParam('placeTypes') as $text) {
                $placeTypes[] = $db->quoteInto("$alias.text = ?", $text);
            }
            $wheres[] = implode(" OR ", $placeTypes);
        // Filter event types (inclusive).
        } else if ($request->getParam('eventTypes')) {
            $alias = "event_types";
            $joins[] = "$db->ElementText AS $alias ON $alias.record_id = items.id AND $alias.record_type = 'Item' " 
                     . $db->quoteInto("AND $alias.element_id = ?", self::ELEMENT_ID_EVENT_TYPE);
            $eventTypes = array();
            foreach ($request->getParam('eventTypes') as $text) {
                $eventTypes[] = $db->quoteInto("$alias.text = ?", $text);
            }
            $wheres[] = implode(" OR ", $eventTypes);
        }
        
        // Build the SQL.
        $sql = "SELECT items.id, locations.latitude, locations.longitude\nFROM $db->Location AS locations";
        foreach ($joins as $join) {
            $sql .= "\nJOIN $join";
        }
        foreach ($wheres as $key => $where) {
            $sql .= (0 == $key) ? "\nWHERE" : "\nAND";
            $sql .= " ($where)";
        }
        $sql .= "\nGROUP BY items.id";
        
        // Build geoJSON: http://www.geojson.org/geojson-spec.html
        $data = array('type' => 'FeatureCollection', 'features' => array());
        foreach ($db->query($sql)->fetchAll() as $row) {
            $item = get_record_by_id('item', $row['id']);
            $data['features'][] = array(
                'type' => 'Feature', 
                'geometry' => array(
                    'type' => 'Point', 
                    'coordinates' => array($row['longitude'], $row['latitude']), 
                ), 
                'properties' => array(
                    'title' => metadata($item, array('Dublin Core', 'Title')), 
                    'thumbnail' => item_image('thumbnail', array(), 0, $item), 
                    'url' => url(array('module' => 'default', 
                                       'controller' => 'items', 
                                       'action' => 'show', 
                                       'id' => $item['id']), 
                                 'id'), 
                ), 
            );
            // Prevent memory leaks.
            release_object($item);
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
