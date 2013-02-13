<?php
/**
 * Mall Map
 * 
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Mall Map plugin.
 * 
 * @package Omeka\Plugins\Mall
 */
class MallMapPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'define_routes', 
    );
    
    protected $_filters = array(
        'admin_navigation_main', 
        'public_navigation_main', 
    );
    
    public function hookDefineRoutes($args)
    {
        $args['router']->addRoute('mall_map', 
            new Zend_Controller_Router_Route('map', 
                array(
                    'module' => 'mall-map', 
                    'controller' => 'index', 
                    'action' => 'index', 
                )
            )
        );
    }
    
    public function filterAdminNavigationMain($nav)
    {
        return $this->_navigationMain($nav);
    }
    
    public function filterPublicNavigationMain($nav)
    {
        return $this->_navigationMain($nav);
    }
    
    protected function _navigationMain($nav)
    {
        $nav[] = array('label' => 'Map', 'uri' => url('map'));
        return $nav;
    }
}
