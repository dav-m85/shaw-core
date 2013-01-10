<?php

// @see http://pastie.org/475593, http://blog.pastie.org/

class Shaw_Controller_Plugin_Trace_Doctrine
	extends Shaw_Controller_Plugin_Trace_Abstract
{
	protected $logWriter = null;
	
	public $description = 'arf';
	
	public $title = 'Doctrine Profiler';
	
	private $_connection = null;
	
	public function init()
	{
		$this->_connection = Doctrine_Manager::connection();
	}
	
	public function render()
	{
		
		
		 
		$doctrine = 
		$profiler = $doctrine->getListener();
		if (Doctrine_Core::debug() && isset($profiler)){
			$markup .= '<table border=0 cellspacing=0 cellpading=0 >'
			. '<tr><th>Event</th><th>Query</th><th>Time</th><th>Params</th></tr>';
			$class = "even";
			$time = 0;
			foreach($profiler as $event){
				$class = $class == "odd" ? "even" : "odd";
				$markup .= sprintf('<tr class="' . $class . '"><td>%s</td><td>%s</td><td>%.3f us</td><td>%s</td></tr>',
				$event->getName(),
				$event->getQuery(),
				$time += $event->getElapsedSecs(),
				($params = $event->getParams()) ? print_r($params, true) : 'No params');
			}
			 
			$markup .= '<tr><td colspan="4"><span style="color:red">'.sprintf('%s Events in %.3f us.', $profiler->count(), $time).'</span></td></tr>'
			. '</table>';
		}
		 
		return $markup;
	}
}