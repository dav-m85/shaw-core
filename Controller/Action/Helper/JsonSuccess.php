<?php

class Shaw_Controller_Action_Helper_JsonSuccess
extends Zend_Controller_Action_Helper_Abstract
{
	/**
	 * Proxy for applink actionctronller helper
	 */
    public function direct($message = null)
    {
    	// Make sure we're inside a InitContext for JSON
    	$contextSwitch = Zend_Controller_Action_HelperBroker::getStaticHelper('contextSwitch');
    	if(! $contextSwitch){
    		throw new Exception('Cannot success without JSON context (contextSwitch even not defined) !');
    	}
    	
    	if(($ctxt = $contextSwitch->getCurrentContext()) != 'json'){
    		throw new Exception('Cannot success without JSON context (contextSwitch defined to : '.$ctxt.') !');
    	}
    	
    	// Retrieve view
    	$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
    	if(! $viewRenderer){
    		throw new Exception('No viewRenderer available !');
    	}
    	
    	$view = $viewRenderer->view;
    	
    	// Setup standard variables.
    	$view->message = $message;
    	$view->success = true;
    	$view->timestamp = time();
    	$view->action = $this->getRequest()->getActionName();
    }
}