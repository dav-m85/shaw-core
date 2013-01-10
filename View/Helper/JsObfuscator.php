<?php

/** Zend_View_Helper_Placeholder_Registry */
require_once 'Zend/View/Helper/Placeholder/Registry.php';

/** Zend_View_Helper_Abstract.php */
require_once 'Zend/View/Helper/Abstract.php';

/**
 * Helper for passing data between otherwise segregated Views. It's called
 * Placeholder to make its typical usage obvious, but can be used just as easily
 * for non-Placeholder things. That said, the support for this is only
 * guaranteed to effect subsequently rendered templates, and of course Layouts.
 *
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Shaw_View_Helper_JsObfuscator extends Zend_View_Helper_Placeholder
{
	
	protected $_containerName = 'jsObfuscator';
	
	/**
	 * Placeholder helper
	 *
	 * @param  string $name
	 * @return Zend_View_Helper_Placeholder_Container_Abstract
	 */
	public function jsObfuscator()
	{
		return $this;
	}
	
	public function start()
	{
		$container = new Shaw_View_Helper_JsObfuscator_Container;
		$this->_registry->setContainer($this->_containerName, $container);
		$container->captureStart();
	}
	
	public function end()
	{
		$container = $this->_registry->getContainer($this->_containerName);
		$container->captureEnd();
		return $container->toString();
	}
}