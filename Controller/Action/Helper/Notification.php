<?php

/**
 *
 */
// TODO Manage transaltion here / not here
class Shaw_Controller_Action_Helper_Notification extends Zend_Controller_Action_Helper_Abstract
{
    static private $_messages = array(
        'formInvalid' => array('message' => 'Il y a des erreurs dans le formulaire.', 'type' => 'error'),
        'error' => array('message' => 'Le site a rencontré une erreur en traitant votre demande.', 'type' => 'error')
    );
    
    public function direct($messageKey){
        
        $dic = self::$_messages;
        
        if(! array_key_exists($messageKey, $dic))
            throw new Exception ('No such message '.$messageKey);
        
        $flash = $this->getFlashMessenger();
        $flash->addMessage($dic[$messageKey]);
    }
    
    public function addSuccess($message){
        if(func_num_args() > 1){
            $args = func_get_args();
            $message = call_user_func_array('sprintf', $args);
        }
        
        $flash = $this->getFlashMessenger();
        $flash->addMessage(array('type' => 'success', 'message' => $message));
    }
    
    public function addInfo($message){
        if(func_num_args() > 1){
            $args = func_get_args();
            $message = call_user_func_array('sprintf', $args);
        }
        
        $flash = $this->getFlashMessenger();
        $flash->addMessage(array('type' => 'info', 'message' => $message));
    }
    
    public function addWarning($message){
        if(func_num_args() > 1){
            $args = func_get_args();
            $message = call_user_func_array('sprintf', $args);
        }
        
        $flash = $this->getFlashMessenger();
        $flash->addMessage(array('type' => 'warning', 'message' => $message));
    }
    
    public function addError($message){
        if(func_num_args() > 1){
            $args = func_get_args();
            $message = call_user_func_array('sprintf', $args);
        }
        
        $flash = $this->getFlashMessenger();
        $flash->addMessage(array('type' => 'error', 'message' => $message));
    }
    
    public function addDebug($message){
        if(func_num_args() > 1){
            $args = func_get_args();
            $message = call_user_func_array('sprintf', $args);
        }
        
        $flash = $this->getFlashMessenger();
        $flash->addMessage(array('type' => 'debug', 'message' => $message));
    }
    
    protected function getFlashMessenger(){
        return Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
    }
}