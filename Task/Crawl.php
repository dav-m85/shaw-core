<?php

/**
 * Common task to crawl/leech a website.
 * 
 * @author david
 */
abstract class Shaw_Task_Crawl
extends Shaw_Task
{
	public $requiredArguments = array();
	public $optionalArguments = array();
	
	public $metrics = array();
	
	protected $_requests = array();
	
	protected $_initStore = true;
	
	protected $_storeConfig = array();
	
	public function execute()
	{
	    $loop = new Shaw_Chain_Loop();
	    
	    if($this->_initStore){
    	    $store = new Shaw_Chain_Store($this->_storeConfig);
    	    $loop->chain($store)->chain($this, 'source', 'sink');
	    }
	    else{
	        $loop->chain($this, 'source', 'sink');
	    }
	    
		$loop->safeRun();
	} 
	
	abstract protected function process(Shaw_Chain_Bucket $req);
	
	public function appendRequest(Shaw_Chain_Bucket $request)
	{
		// Shaw_Log::debug('+ %s', $request->url);
		array_unshift($this->_requests, $request);
	}
	
	public function source()
	{
		$var = array_shift($this->_requests);
		if(! $var) return false;
		return $var;
	}
	
	public function sink(Shaw_Chain_Bucket $req)
	{
		$code = $req->info["http_code"];
		if($code && $code != 200){
			throw new Exception('Oups, looks we cannot fetch ' . $req->url);
		}
	
		$output = $req->output;
		
		// Empty response
		if(! $output){
			Shaw_Log::debug('Skipping %s', $req->url);
			return;
		}
		
		// Is it UTF8 or ISO ?
		if(stripos($output, 'charset=ISO-8859-1') !== 0)
		{
			// Shaw_Log::debug('UTF8 encoding response');
			$output = utf8_encode($output);
		}
		
		// Forward to child class here
		Shaw_Log::debug('< %s', $req->url);
		$this->process($req);
	}
}