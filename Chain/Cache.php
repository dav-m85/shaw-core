<?php

class Shaw_Chain_Cache
extends Shaw_Chain_AbstractLink
{
	protected $_cache = null;
	protected $_force;
	
	public function __construct(Zend_Cache_Core $cache, $force = false)
	{
		$this->_force = $force;
	    $this->_cache = $cache;
	}
	
	// Source data as long as its in cache
	function chainSource()
	{
		do{
			$req = $this->source();
			if($req === false){ // depleted
				return $req;
			}
			$hash = $req->getHash();
			
			Shaw_Log::debug('Seek %s', $hash);
			
			if ( $this->_cache->test($hash) && ! $this->_force ){
				Shaw_Log::debug('cache hit');
				$req->output = $this->_cache->load($hash);
				
				$this->sink($req);
			}
			else{
				break;
			}
		}
		while($req);

		return $req;
	}
	
	function chainSink($req)
	{
		if($req->output != null){
			$hash = $req->getHash();
			$this->_cache->save($req->output, $hash);
		}
		$this->sink($req);
	}
}