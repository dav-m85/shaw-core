<?php

class Shaw_Controller_Plugin_Trace_Log
	extends Shaw_Controller_Plugin_Trace_Abstract
{
	private $_logWriter = null;
	
	public $description = 'what the logger said';
	
	public $title = 'Log';
	
	public function init()
	{
		// Add log listener.
		$this->_logWriter = new Shaw_Log_Writer_Dummy();
		Shaw_Log::getInstance()->addWriter($this->_logWriter);
	}
	
	public function render()
	{
		$markup .= '<table class="shaw-trace-table">'
		. '<thead><tr><th>Time</th><th>Priority</th><th>Message</th></tr></thead><tbody>';
		$class = "even";
		foreach($this->_logWriter->getEntries() as $entry) {
			$class = $class == "odd" ? "even" : "odd";
			$markup .= '<tr class="' . $class . '">'
			. '<td class="shaw-trace-20p">' . $entry['timestamp'] . '</td>'
			. '<td class="shaw-trace-20p shaw-trace-loglevel'.$entry['priority'].'">' . $entry['priorityName'] . '</td>'
			. '<td>' . $entry['message'] . '</td>'
			. '</tr>' . "\n";
		}
		$markup .= '</tbody></table>';
		return $markup;
	}
}