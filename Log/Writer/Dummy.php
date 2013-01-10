<?php
/**
 * Display log at the end of the response.
 */
// Can and is hooked to logs.
class Shaw_Log_Writer_Dummy extends Zend_Log_Writer_Abstract
{
    protected $_logEntries = array();
    
    // Zend_Log_Abstract func.
    public function _write($event)
    {
        $this->_logEntries[] = $event;
    }
    
    public function getEntries(){
        return $this->_logEntries;
    }
    
    static public function factory($config){}
}