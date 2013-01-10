<?php

class Shaw_Chain_CleanResponse
extends Shaw_Chain_AbstractLink
{
	function chainSource()
	{
		return $this->source();
	}
	
	function chainSink($req)
	{
		$code = $req->info["http_code"];
		$output = $req->output;
		
		if($code && $code != 200){
			//throw new Exception('Oups, looks we cannot fetch ' . $req->url);
			Shaw_Log::debug('Cannot fetch due to http error : %s', $code);
			if($output){
				Shaw_Log::debug($output);
			}
			return;
		}
		
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
		
		$this->sink($req);
	}
}