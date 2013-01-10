<?php
// TODO may specify available controllers for translation
class Shaw_Controller_Plugin_L10n
	extends Zend_Controller_Plugin_Abstract
{
	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
	{
		if(Zend_Registry::isRegistered('language')){
			return;
		}
		
		// if googlebot AND home, forward hub.
		if($this->_isGoogleBot($request)
				&& $request->getControllerName('index')
				&& $request->getActionName('index')){
			Shaw_Log::debug('GoogleBot cloaking OK');
			return;
		}
		
        $locale = new Zend_Locale(/*Zend_Locale::BROWSER*/);
        
        Zend_Registry::set('language', $locale->getLanguage());
        
        $urlHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer')->view;
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        
        $url = $urlHelper->url(
	        array(
	        	'action' => $request->getActionName(), 
	        	'module' => $request->getModuleName(), 
	        	'controller' => $request->getControllerName()
	        )
        );
        
        $redirector->gotoUrlAndExit($url);
	}
	
	// Detect if need cloaking capability for the ongoing request
	private function _isGoogleBot($request){
		if($request->getParam('googlebot')){ // dev mode
			return true;
		}
		return false;
	}
}