<?php

class Shaw_Controller_Action_Helper_Alert extends Zend_Controller_Action_Helper_Abstract
{
    public function direct($messageKey)
    {
        throw new Exception('No direct call to Alert are available');
    }
    
    public function alert(){
    	$params = func_get_args();
        $this->_alert('alert', $params);
    }
    
	public function error(){
    	$params = func_get_args();
        $this->_alert('error', $params);
    }
    
	public function success(){
    	$params = func_get_args();
        $this->_alert('success', $params);
    }
    
    public function info(){
    	$params = func_get_args();
        $this->_alert('info', $params);
    }
    
	private function _alert($type, $params)
    {
        if(count($params) == 1){
            $message = $params[0];
        	if($message instanceof Exception){
                $exception = $message;
            }
            else if(is_object($message) || is_array($message)){
                $message = var_export($message, true);
            }
            
        }
        else if(count($params) > 1){
            if($params[0] instanceof Exception){
                $message = $params[1];
                $exception = $params[0];
            }
            else if($params[1] instanceof Exception){
                $message = $params[0];
                $exception = $params[1];
            } else {
            	$striMode = true;
                for($i = 0; $i < count($params); $i++){
                	if(! is_string($params[$i]) && ! is_numeric($params[$i])){
                		$params[$i] = var_export($params[$i], true);
                		$striMode = false;
                	}
                }
            	
            	if($striMode){
            		$message = call_user_func_array('sprintf', $params);
            	}
            	else{
            		$message = join('<br />', $params);
            	}
            }
        }
        $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        $flash->addMessage(array('type' => $type, 'message' => $message));
    }
}