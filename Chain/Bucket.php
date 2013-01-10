<?php

class Shaw_Chain_Bucket
{
	public $client = array(
		CURLOPT_HEADER 		   => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_TIMEOUT        => 5,
		CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1",
		CURLOPT_FOLLOWLOCATION => 1,
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_SSL_VERIFYHOST => 0,
	);
	public $output; // retour curl
	public $info; // curl_info
	public $url; // c toi qui le rmpluit
	public $error; // en cas derreur, c rempli
	public $proxy; // instance du proxy peuplé par shaw_chain_proxy
	public $options; // user defined
	public $item;
	
	public function __construct($url = null, $options = array())
	{
		if($url){
			$this->url = $url;
			$this->client[CURLOPT_URL] = $url;
		}
		$this->options = $options;
	}
	
	public function getHash()
	{
		return md5(join(':',$this->options['params']));
	}
	
	public function allocate()
	{
		$ch = curl_init();
		curl_setopt_array($ch, $this->client);
		
		$this->client = $ch;
	}
	
	public function free()
	{
		if($this->client){
			curl_close($this->client);
		}
	}
}