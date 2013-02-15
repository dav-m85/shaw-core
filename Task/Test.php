<?php

class Shaw_Task_Test
extends Shaw_Task
{
	public $description = 'Test';
	public $requiredArguments = array();
	public $optionalArguments = array('exception' => 'throw an exception');

	public function execute()
	{
	    Shaw_Log::debug('test here');
	    
	    if($this->hasArgument('exception')){
	        throw new Exception('test exception');
	    }
	}
}