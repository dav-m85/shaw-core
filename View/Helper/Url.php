<?php

/** Zend_View_Helper_Abstract.php */
require_once 'Zend/View/Helper/Abstract.php';

/**
 * Helper for making easy links and getting urls that depend on the routes and router
 *
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Shaw_View_Helper_Url extends Zend_View_Helper_Abstract
{
    /**
     * Generates an url given the name of a route.
     *
     * @access public
     *
     * @param  array $urlOptions Options passed to the assemble method of the Route object.
     * @param  mixed $name The name of a Route to use. If null it will use the current Route
     * @param  bool $reset Whether or not to reset the route defaults with those provided
     * @return string Url for the link href attribute.
     *
     * Call syntax :
     *   - string action, string controller, array(additionnal options), reset
     *
     * 
     */
    public function url($action = array(), $controller = null, $name = null, $reset = false)
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        if(! is_array($action) && ! empty($action)){
            $urlOptions = array(
                'action' => $action,
                'controller' => $controller ? $controller : Zend_Controller_Front::getInstance()->getRequest()->getControllerName(),
                'module' => Zend_Controller_Front::getInstance()->getRequest()->getModuleName(),
            );
            
            if(is_array($name)){
                $urlOptions = array_merge($urlOptions, $name);
            }
            
            if(! is_string($name)){
                $name = 'default';
                // $reset = true;
            }
        }
        else{
            $urlOptions = $action;
            $name = $controller;
            /*if($name == 'default'){
                Shaw_Log::debug('There is still a default url route call here...');
            }*/
        }
        
        $url = $router->assemble($urlOptions, $name, $reset);
        
        if (Zend_Registry::isRegistered('language')) {
            $url = '/' . Zend_Registry::get('language') . $url;
        }
        
        return $url;
    }
}
