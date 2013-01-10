<?php

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * Helper for creating URLs for redirects and other tasks
 *
 * @uses       Zend_Controller_Action_Helper_Abstract
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Shaw_Controller_Action_Helper_Url extends Zend_Controller_Action_Helper_Url
{
    /**
     * Create URL based on default route
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module
     * @param  array  $params
     * @return string
     */
    public function simple($action, $controller = null, $module = null, array $params = null)
    {
        $url = parent::simple($action, $controller, $module, $params);

        if (Zend_Registry::isRegistered('language')) {
        	$url = '/' . Zend_Registry::get('language') . $url;
        }

        return $url;
    }

    /**
     * Assembles a URL based on a given route
     *
     * This method will typically be used for more complex operations, as it
     * ties into the route objects registered with the router.
     *
     * @param  array   $urlOptions Options passed to the assemble method of the Route object.
     * @param  mixed   $name       The name of a Route to use. If null it will use the current Route
     * @param  boolean $reset
     * @param  boolean $encode
     * @return string Url for the link href attribute.
     */
    public function url($urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
    	$url = parent::url($urlOptions, $name, $reset, $encode);
    	
    	if (Zend_Registry::isRegistered('language')) {
    		$url = '/' . Zend_Registry::get('language') . $url;
    	}
    	
    	return $url;
    }

    /**
     * Perform helper when called as $this->_helper->url() from an action controller
     *
     * Proxies to {@link simple()}
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module
     * @param  array  $params
     * @return string
     */
    public function direct($action, $controller = null, $module = null, array $params = null)
    {
    	$url = parent::direct($action, $controller, $module, $params);
    	
        if (Zend_Registry::isRegistered('language')) {
        	$url = '/' . Zend_Registry::get('language') . $url;
        }

        return $url;
    }
}
