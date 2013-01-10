<?php
/**
 *
 */
class Shaw_Benchmark_LogListener
extends Zend_Log_Writer_Abstract
{
	public function _write($event)
	{
		Shaw_Benchmark::mark('log', $event);
	}

	static public function factory($config)
	{}
}