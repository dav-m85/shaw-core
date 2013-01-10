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
		$loader = new Zend_Loader_Autoloader_Resource($config);
		$loader->addResourceType('Task', 'tasks', 'Task');
    }
}
