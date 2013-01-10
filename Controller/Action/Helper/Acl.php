<?php

class Shaw_Controller_Action_Helper_Acl 
	extends Zend_Controller_Action_Helper_Abstract
{
    public function direct($ressourceName = 'api-unprotected')
    {
        $acl = Model_Acl::getInstance();
        $request = $this->getRequest();
        
        if($ressourceName == 'api-unprotected')
        	Shaw_Log::debug('There is still an unprotected call to Acl helper.');
        
        // If is not allowed, fire 403
        if(! ($accessToken = $request->getParam('access_token')) && $ressourceName != 'api-unprotected')
        {
        	Shaw_Log::debug('Please provide access_token.');
        	throw new Shaw_Acl_Exception('No access_token providen.');
        }
        
        $apiUser = $this->_getApiUser($accessToken);
        if($apiUser === false)
        {
        	Shaw_Log::debug('ApiUser not found.');
        	throw new Shaw_Acl_Exception('Unknown access_token.');
        }
        
        $isAllowed = $acl->isAllowed($apiUser[0]['role'], $ressourceName);
        
        if(! $isAllowed)
        {
        	Shaw_Log::debug('Not authorized for real.');
        	throw new Shaw_Acl_Exception('Not sufficient permissions.');
        }
    }
    
    // public function 
    
    
    private function _getApiUser($token)
    {
        return Doctrine_Core::getTable('Db_ApiUser')->findBy('token', $token, Doctrine_Core::HYDRATE_ARRAY);
    }
}
