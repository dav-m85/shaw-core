<?php

/** Zend_Locale */
require_once 'Zend/Locale.php';

/** Zend_View_Helper_Abstract.php */
require_once 'Zend/View/Helper/Abstract.php';

/**
 * Translation view helper
 *
 * @category  Zend
 * @package   Zend_View
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Shaw_View_Helper_Cache extends Zend_View_Helper_Abstract
{
	protected $_manager = null;
	
	/**
	 * 
	 * 
	 * @param string $cacheName Cache instance we wanna use, most of tht ime its "block"
	 * @throws Exception
	 */
    public function cache($cacheName = 'block'){
		if ($this->_manager === null) {
			global $application;
			
			
            $front = Zend_Controller_Front::getInstance();
            if (! $this->_manager = $application->getBootstrap()->getResource('CacheManager')) {
                throw new Exception('No CacheManager defined !');
            }
		}
		
		if ($this->_manager === null) {
		    throw new Exception('No CacheManager defined !');
		}
		
		if(! $this->_manager->hasCache($cacheName)){
		    throw new Exception('No such Cache as '. $cacheName .' defined !');
		}
		
		return $this->_manager->getCache($cacheName);
	}
}