<?php

class Shaw_Controller_Plugin_Trace_Flash
	extends Shaw_Controller_Plugin_Trace_Abstract
{
	protected $logWriter = null;
	
	public $description = 'arf';
	
	public $title = 'Flash';
	
	public function render()
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		
		$markup .= '
		<script type="text/javascript"> 
		//<![CDATA[
			jQuery.addLog = function(message){
				$("#flashLog").append(message + "<br \>" + "\n");
			}
		//]]>
		</script>
		<div id="flashLog"></div>'; 
		
		return $markup;
	}
}