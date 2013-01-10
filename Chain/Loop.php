<?php

/**
 * Implements a loop system for curl multi calls
 * @author david
 *
 */
class Shaw_Chain_Loop
extends Shaw_Chain_AbstractLink
{
	private $_requests = array();
	
	/**
	 * @return Shaw_Bucket
	 */
	private function _findRequestByHandle($handle)
	{
		foreach($this->_requests as $req){
			if($req->client == $handle){
				return $req;
			}
		}
		return false;
	}
	
	/**
	 * @return int
	 */
	private function _findRequestIndexByHandle($handle)
	{
		$req = $this->_findRequestByHandle($handle);
		if($req === false){
			return false;
		}
		return $this->_findRequestIndex($req);
	}
	
	/**
	 * @return int
	 */
	private function _findRequestIndex($req)
	{
		return array_search($req, $this->_requests);
	}
	
	public function singleRun()
	{
		$req = $this->source();
		$req->allocate();
		$ch = $req->client;
		
        $res = curl_exec ($ch) ;
        
        $req->output = $res;
		$req->info   = curl_getinfo( $ch );
		
		if($err = curl_error($ch)){
			throw new Exception($err);
		}
		
        $req->free();
        
        $this->sink($req);
	}
	
	
	
	public function safeRun($size = 10)
	{
		$depleted = false;
		do {
		    
			$mh = curl_multi_init();
			
			// Fill request pipe
			$this->_requests = array();
			do{
				$req = $this->source();
				
				if($req === false){$depleted = true; break;}
				array_push($this->_requests, $req);
			}
			while(count($this->_requests) < ($this->_size - 1));
			
			// Add
			for($i = 0; $i < count($this->_requests); $i++){
				$this->_requests[$i]->allocate();
				// Shaw_Log::debug('> %s', $this->_requests[$i]->url);
				curl_multi_add_handle($mh, $this->_requests[$i]->client);
			}
			
			// Exec
			do {
			    $mrc = curl_multi_exec($mh, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			
			while ($active && $mrc == CURLM_OK) {
			    if (curl_multi_select($mh) != -1) {
			        do {
			            $mrc = curl_multi_exec($mh, $active);
			        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
			    }
			}
			
			// Process
			for($i = 0; $i < count($this->_requests); $i++){
				$ch = $this->_requests[$i]->client;
				$creq = $this->_findRequestByHandle($ch);
				
				$creq->output = curl_multi_getcontent ( $ch );
		    	$creq->info   = curl_getinfo( $ch );
		    	
				if($err = curl_error($ch)){
					$creq->error = $err;
				}
		    	
		    	$this->sink($creq);
			}
			
			// Remove
			for($i = 0; $i < count($this->_requests); $i++){
				curl_multi_remove_handle($mh, $this->_requests[$i]->client);
				curl_close($this->_requests[$i]->client);
			}

			curl_multi_close($mh);
		}
		while(! $depleted);
	}
	
	/*
	public function run()
	{
		$mh = curl_multi_init();
		
		// Fill request pipe
		do{
			$req = $this->_getNext();
			if($req === false){break;}
			array_push($this->_requests, $req);
		}
		while(count($this->_requests) < ($this->_size - 1));
		
		// Add pipe to master
		for($i = 0; $i < count($this->_requests); $i++){
			curl_multi_add_handle($mh, $this->_requests[$i]->client);
			// Shaw_Log::debug('Adding handle');
		}
		
		$depleted = false;
		do {
		    $status = curl_multi_exec($mh, $active);
		    $info = curl_multi_info_read($mh);
		    if (false !== $info && is_array($info)) {
		    	
		    	$ch = $info['handle'];
		    	$creq = $this->_findRequestByHandle($ch);
		    	
		    	if($info['result'] == CURLE_OK){
		    		// Shaw_Log::debug('Gotit');
		    		$creq->output = curl_multi_getcontent ( $ch );
		    		$creq->info   = curl_getinfo( $ch );
		    	}
		    	else{
		    		Shaw_Log::warn('Weird info %s', $info['result']);
		    	}
		    	
		    	if($err = curl_error($ch)){
					Shaw_Log::error($err);
					$creq->error = $err;
				}
		        
				$creq->free();
				
		        curl_multi_remove_handle($mh, $ch);
		        
		        // Shaw_Log::debug('Processing handle');
		        $creq->processResponse();
				unset($this->_requests[$this->_findRequestIndex($creq)]);
				
				// Add another handle
				if(! $depleted){
					$req = $this->_getNext();
					if($req !== false){
						array_push($this->_requests, $req);
						curl_multi_add_handle($mh, $req->client);
						// Shaw_Log::debug('Adding handle');
					}
					else{
						$depleted = true;
					}
				}
		    }
		} while ($status === CURLM_CALL_MULTI_PERFORM || $active);
		
		curl_multi_close($mh);
	}
	*/
}
