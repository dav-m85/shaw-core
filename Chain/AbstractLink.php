<?php

class Shaw_Chain_AbstractLink
{
	/**
	 * @var Shaw_Chain
	 */
	protected $sourceCallback = null;
	protected $sinkCallback = null;
	
	/**
	 * 
	 * @param mixed $successor
	 * @return mixed Return $successor, allows nice writing $a->chain($b)->chain($c) equivalent to $a->chain($b); $b->chain($c);
	 */
	public function chain($successor, $sourceCallback = null, $sinkCallback = null)
	{
		if($successor instanceof Shaw_Chain_AbstractLink){
			$this->sourceCallback = array($successor, 'chainSource');
			$this->sinkCallback = array($successor, 'chainSink');
		}
		else{
			$this->sourceCallback = array($successor, $sourceCallback);
			$this->sinkCallback = array($successor, $sinkCallback);
		}
			
		if(! is_callable($this->sourceCallback) || ! is_callable($this->sinkCallback)){
			throw new Exception('Not callable callbacks');
		}
		
		return $successor;
	}
	
	/**
	 * Chain $successor if $flag is true.
	 * 
	 * @param Shaw_Chain_AbstractLink $successor
	 * @param bool $flag
	 */
	public function chainIf(Shaw_Chain_AbstractLink $successor, $flag)
	{
		if((bool) $flag){
			return $this->chain($successor);
		}
		return $this;
	}
	
	/**
	 * Fetch data bucket from successor
	 * 
	 * Return false if nothing left to process
	 */
	protected function source()
	{
		if(! is_callable($this->sourceCallback)){
			throw new Exception('Not callable callbacks');
		}
		return call_user_func($this->sourceCallback);
	}
	
	public function chainSource()
	{
		return $this->source();
	}
	
	/**
	 * Push data bucket to successor
	 */
	protected function sink($data)
	{
		if(! is_callable($this->sinkCallback)){
			throw new Exception('Not callable callbacks');
		}
		return call_user_func($this->sinkCallback, $data);
	}
	
	public function chainSink($data)
	{
		$this->sink($data);
	}
}

/*
// Main treatment
class Shaw_Chain_Foo 
extends Shaw_Chain_AbstractLink
{
	function chainSource()
	{
		Shaw_Log::debug('chainSource %s', get_class($this));
		$data = $this->source();
		return 'b' . $data;
	}
	
	function chainSink($data)
	{
		$this->sink($data . 'b');
	}
}

// prepend and postpend things to strings
class Shaw_Chain_Bar 
extends Shaw_Chain_AbstractLink
{
	function run()
	{
		$data = $this->source();
		$this->sink($data);
	}
}

class Shaw_Chain_Test
{
	function getNext()
	{
		return 'o';
	}
	
	function showIt($data)
	{
		echo $data . PHP_EOL;
	}
	
	function test()
	{
		
		// bar -> foo -> this
		
		
		$foo = new Shaw_Chain_Foo();
		$bar = new Shaw_Chain_Bar();
		
		$bar->chain($this, 'getNext', 'showIt');
		//$bar->chain($foo);
		
		$bar->run();
	}
}
*/