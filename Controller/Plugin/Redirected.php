<?php
// TODO may specify available controllers for translation
class Shaw_Controller_Plugin_L10n
	extends Zend_Controller_Plugin_Abstract
{
	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
	{
	    if($request->getParam('r') === 'false'){
	        $session = new Zend_Session_Namespace('Default');
	        if( $session ){
	            $session->redirectUrl = null;
	            $session->redirectTitle = null;
	            Shaw_Log::debug('redirectClean');
	        }
	    }
	}
}