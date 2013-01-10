<?php

/**
 * Remove layout and viewRenderer for specific actionController
 */
class Shaw_Controller_Plugin_Apification
    extends Zend_Controller_Plugin_Abstract
{
	protected $_controllers = null;
	
	protected $_enabled = false;
	
	public function __construct(array $controllers = array())
	{
		$this->_controllers = $controllers;
	}
	
	public function isEnabled()
	{
		return $this->_enabled;
	}
	
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	$currentModule = $request->getModuleName();
    	$currentController = $request->getControllerName();
    	
    	foreach($this->_controllers as $module => $controllers)
    	{
    		if($currentModule != $module)
    			continue;
    		
    		if($controllers == '*' || in_array('*', $controllers) || in_array($currentController, $controllers))
    		{
    			$layout = Zend_Controller_Action_HelperBroker::getStaticHelper('layout');
    			$layout->disableLayout();
    			$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
				$viewRenderer->setNeverRender(true);
    			
    			$response = $this->getResponse();
    			$response->setHeader('Content-Type', 'application/json');
    			
    			$this->_enabled = true;
    			
    			break;
    		}
    	}
    }
    
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
    	if($this->isEnabled()){
	    	$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
	    	$view = $viewRenderer->view;
	    	if ($view instanceof Zend_View_Interface) {
	    		/**
	    		 * @see Zend_Json
	    		 */
	    		if(method_exists($view, 'getVars')) {
	    			require_once 'Zend/Json.php';
	    			$vars = $view->getVars();
	    			//$vars['httpResponseCode'] = $this->getResponse()->getHttpResponseCode();
	    			$vars = Zend_Json::encode($vars);
	    			
	    			$this->getResponse()->setBody(Zend_Json::prettyPrint($vars, array('indent' => "   ")));
	    		} else {
	    			require_once 'Zend/Controller/Action/Exception.php';
	    			throw new Zend_Controller_Action_Exception('View does not implement the getVars() method needed to encode the view into JSON');
	    		}
	    	}
    	}
    }
}