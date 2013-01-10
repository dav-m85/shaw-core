<?php

class Shaw_Controller_Action_Helper_JsonFail
extends Zend_Controller_Action_Helper_Abstract
{
	/**
	 * Proxy for applink actionctronller helper
	 */
    public function direct($message = null, $exception = null)
    {
    	// Make sure we're inside a InitContext for JSON
    	$contextSwitch = Zend_Controller_Action_HelperBroker::getStaticHelper('contextSwitch');
    	if(! $contextSwitch){
    		throw new Exception('Cannot fail without JSON context (contextSwitch even not defined) !');
    	}
    	
    	if(($ctxt = $contextSwitch->getCurrentContext()) != 'json'){
    		throw new Exception('Cannot fail without JSON context (contextSwitch defined to : '.$ctxt.') !');
    	}
    	
    	// Retrieve view
    	$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
    	if(! $viewRenderer){
    		throw new Exception('No viewRenderer available !');
    	}
    	
    	$view = $viewRenderer->view;
    	
    	if($message instanceof Exception){
    		list($message, $exception) = array($exception, $message); // swap
    	}
    	
    	// Setup standard variables.
    	$view->message = $message;
    	if($exception){
    		$view->exception = $exception->getMessage();
    	}
    	$view->success = false;
    	$view->timestamp = time();
    	$view->action = $this->getRequest()->getActionName();
    }
}