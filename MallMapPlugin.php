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
        'config',
        'config_form'
    );
    
    protected $_filters = array(
        'public_navigation_main', 
    );
    
    protected $_options = array(
        'mall_map_filter_tooltip' => '',
        'mall_map_tooltip_button' => 'OK'
    );
    
    /**
     * Display the plugin config form.
     */
    public function hookConfigForm()
    {
        require dirname(__FILE__) . '/config_form.php';
    }

    /**
     * Set the options from the config form input.
     */

    public function hookConfig()
    {
        set_option('mall_map_filter_tooltip', $_POST['mall_map_filter_tooltip']);
<<<<<<< HEAD
        set_option('mall_map_tooltip_button', $_POST['mall_map_tooltip_button']);
=======
>>>>>>> Start setting up first-time visitor tooltip feature.
    }
    
    public function hookDefineRoutes($args)
    {
        if (is_admin_theme()) {
            return;
        }
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
    
    public function filterPublicNavigationMain($nav)
    {
        $nav[] = array('label' => 'Map', 'uri' => url('map'));
        return $nav;
    }
}
