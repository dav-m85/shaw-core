<?php

class Shaw_Controller_Plugin_Trace_Cookies
	extends Shaw_Controller_Plugin_Trace_Abstract
{
	protected $logWriter = null;
	
	public $description = 'arf';
	
	public $title = 'Cookies';
	
	public function render()
	{
		$markup .= $this->_renderItemTable($_COOKIE);
        return $markup;
	}
}