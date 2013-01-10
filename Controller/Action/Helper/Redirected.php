<?php

class Shaw_Controller_Action_Helper_Redirected 
extends Zend_Controller_Action_Helper_Abstract
{
    protected $_domain = null;
    
    public function __construct($domain = null)
    {
        $this->_domain = $domain;
    }
    
    public function direct()
    {
        throw new Exception("direct call to Shaw_Controller_Action_Helper_Redirected not implemented");
    }
    
    /**
     * Capture a redirection.
     */ 
    public function capture()
    {
        if ( ($session = new Zend_Session_Namespace('Default'))
            && ($redirect = $this->getRequest()->getParam('r')) ){
            
            $redirect = urldecode($redirect);
            /*
            if(stripos($redirect, 'http') === 0)
                if(stripos($redirect, $this->_domain) === false){
                    Shaw_Log::notice('External redirect attempt to '.$redirect);
                    $session->redirect = $baseurl; // TOOO
                    return;
                }
            */
            
            if($redirect == 'false'){
                unset($session->redirectTitle);
                unset($session->redirectUrl);
            }
            else{
                $session->redirectTitle = $this->getRequest()->getParam('rt');
                $session->redirectUrl = $redirect;
                return true;
            }
        }
        return false;
    }
    
    /**
     * Perform a pending redirection if any, else return false.
     */ 
    public function perform()
    {
        // Perform redirect.
        $session = new Zend_Session_Namespace('Default');
        if( $session
            && (($redirect = $session->redirectUrl) != null) ){
            $session->redirectUrl = null;
            $session->redirectTitle = null;
            $this->getActionController()->getHelper('redirector')->gotoUrlAndExit($redirect);
        }
        return false;
    }
}