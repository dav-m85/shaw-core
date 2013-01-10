<?php

/**
 * Log listener that redirect log entries to a class implementing.
 */ 
class Shaw_Log_Writer_Echo 
	extends Zend_Log_Writer_Abstract
{
    public function __construct()
    {
        $this->_formatter = new Zend_Log_Formatter_Simple();
    }
 
    public static function factory($config)
    { 
        return new self();
    }
    
    /**
     * Write a message to the log.
     *
     * @param  array  $event  log data event
     * @return void
     */
    protected function _write($event)
    {
    	if(isset($this->_formatter)){
    	    $msg = $this->_formatter->format($event);
    	}
    	else{
    	    $msg = $event['message'];
    	}
    	
        echo $msg . PHP_EOL;
        flush();
    }
}