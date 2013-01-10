<?php
class Shaw_Controller_Plugin_Module
	extends Zend_Controller_Plugin_Abstract
{
	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
	{
		$moduleName = $request->getModuleName();
		// Set the layout directory for the loaded module
		$layoutPath = APPLICATION_PATH . '/modules/' . $moduleName . '/views/layouts/';
		Zend_Layout::getMvcInstance()->setLayoutPath($layoutPath);
		
		// Set the helper directory for controllers
		$helperPath = APPLICATION_PATH . '/modules/' . $moduleName . '/controllers/helpers';
		if(file_exists($helperPath)){
			Zend_Controller_Action_HelperBroker::addPath($helperPath ,'Controller_Helper');
		}
	}
}