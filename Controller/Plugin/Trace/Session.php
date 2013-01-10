<?php

class Shaw_Controller_Plugin_Trace_Session
	extends Shaw_Controller_Plugin_Trace_Abstract
{
	protected $logWriter = null;
	
	public $description = 'arf';
	
	public $title = 'Session';
	
	public function render()
	{
		if(Zend_Session::sessionExists()){
			foreach(Zend_Session::getIterator() as $namespaceName) {
	            $namespace = new Zend_Session_Namespace($namespaceName);
	            
	            //foreach($namespace as $index => $item) {
	            //	$vars[$namespaceName][$index] = var_export($item, true);
	            //}
	            
	            $markup .= '<h5>'.$namespaceName.'</h5>';
	            $markup .= $this->_renderItemTable($namespace, array('Model_Flow'));
	        }
		}
		else {
			$markup = 'No session started.';
		}
        return $markup;
	}
}