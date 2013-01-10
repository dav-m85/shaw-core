<?php

class Shaw_View_Helper_Alert extends Zend_View_Helper_Abstract
{
    public function alert()
    {
    	return $this;
    }
    
    private function _shiftMessages()
    {
    	$flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
    	
    	$messages = array_merge($flash->getCurrentMessages(), $flash->getMessages());
        $flash->clearMessages(); $flash->clearCurrentMessages();
        
        $result = array();
        foreach($messages as $message){
        	if(! is_array($message)){
        		$result['info'][] = $message;
            }
            else{
            	$result[$message['type']][] = $message['message'];
            }
        }
        return $result;
    }
    
    public function render()
    {
    	$msgs = $this->_shiftMessages();
    	$markup = '';
    	
    	foreach($msgs as $type => $ms){
    		$class = ($type == 'alert' ? '' : 'alert-'.$type);
    		foreach($ms as $m){
    			$markup .= <<<EOF
<div class="alert $class">
  <button class="close" data-dismiss="alert">×</button>
  $m
</div>    
EOF;
    		}
    	}
    	return $markup;
    }
    
    public function renderPartial($partial)
    {
    	$msgs = $this->_shiftMessages();
    	return $this->view->partial($partial, null, $msgs);
    }
    
    public function __toString()
    {
    	return $this->render();
    }
}