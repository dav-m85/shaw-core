<?php
/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * Resource for initializing the locale
 *
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Shaw_Application_Resource_Task
    extends Zend_Application_Resource_ResourceAbstract
{
	/**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Log
     */
    public function init()
    {
    	$config = $this->getOptions();
    	
    	if(! isset($config['path'])){
    	    throw new Exception('Please define resource.task.path config variable.');
    	}
    	if(! isset($config['namespace'])){
    	    throw new Exception('Please define resource.task.namespace config variable.');
    	}
    	
	    // @seealso http://www.doctrine-project.org/jira/browse/DC-288
	    $moduleLoader = new Zend_Application_Module_Autoloader( array ('namespace' => '', 'basePath' => APPLICATION_PATH) );
	    $moduleLoader->addResourceType($name, $config['path'], $config['namespace']);
    }
}
