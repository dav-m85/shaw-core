<?php

class Shaw_Controller_Plugin_Abstract
extends Zend_Controller_Plugin_Abstract
{
	protected $_options;
	
	protected $_optionsKey = null;
	
	/**
	 * Get options from application using frontcontroller.
	 */
	protected function getOptions()
	{
		if(! $this->_options){
			if(! $this->_optionsKey){
				throw new Exception('Please set protected $_optionsKey inside Plugin class.');
			}
			
			$front = Zend_Controller_Front::getInstance();
			$options = $front->getParam('bootstrap')->getApplication()->getOptions();
			$this->_options = $options[$this->_optionsKey];
		}
		
		return $this->_options;
	}
}