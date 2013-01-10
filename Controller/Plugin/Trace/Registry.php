<?php

class Shaw_Controller_Plugin_Trace_Registry
	extends Shaw_Controller_Plugin_Trace_Abstract
{
	protected $logWriter = null;
	
	public $description = 'arf';
	
	public $title = 'Registry';
	
	public function render()
	{
		$markup .= $this->_renderItemTable(Zend_Registry::getInstance(), array('config',
            'Zend_View_Helper_Placeholder_Registry',
            'Zend_View_Helper_Doctype',
            'Zend_Currency',
            'Model_Flow',
            'Zend_Mail_Transport_Smtp',
            'Zend_Translate',
            'Zend_Log'));
        return $markup;
	}
}