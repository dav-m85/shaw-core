<?php

class Shaw_Chain_Proxy
extends Shaw_Chain_AbstractLink
{
	protected $dbh;
	protected $_curlopt = array(
			CURLOPT_HEADER 		   => 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL			   => "http://www.rewapp.com",
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_TIMEOUT        => 5,
			CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1",
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_SSL_VERIFYHOST => 0,
	);
	
	public function __construct($dbconnection=null)
	{
		if($dbconnection!=null){		
			Doctrine_Manager::connection($dbconnection,'customconnection');
			Doctrine_Manager::getInstance()->setCurrentConnection('customconnection');
		}
		$this->dbh = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
			
	}
	
	
	function chainSource()
	{
		$req = $this->source();
		
		if($req == false){
			return $req;
		}
		
		// add proxy
		// Shaw_Log::debug('Adding proxy');
		$req->proxy = $proxy = $this->_getNiceProxy();	
		$proxyVar = array(
			CURLOPT_PROXY		   => $proxy['ip'],
			CURLOPT_PROXYUSERPWD   => $proxy['credential'],
		);
		
		$req->client = $req->client + $proxyVar;
		
		return $req;
	}
	
	function chainSink($req)
	{
		$info = $req->info;
		$code = $info["http_code"];
		$endurl = $info["url"];
		$proxy = $req->proxy;
		
		// if timeout
		if(! $req->error && stripos($req->error, 'time-out') !== false){
			Shaw_Log::warn('timedout');
			$this->sink(false);
			return;
		}
		
		$this->dbh->exec("INSERT INTO proxy_request (url, code, endurl) VALUES (\"$req->url\", $code,\"$endurl\")");
		$this->dbh->exec("UPDATE proxy_instance SET requests=requests+1 WHERE ip = '".$proxy['ip']."'");
		
		// Shaw_Log::debug('incremented %s', $proxy['ip']);
		
		$this->sink($req);
	}
	

		// Fetch it
		//CURLOPT_PROXYTYPE 	   => 4,
		//CURLOPT_FAILONERROR	   => true,
		//CURLOPT_HTTPPROXYTUNNEL=> 1,
		//CURLOPT_CAINFO => realpath(APPLICATION_PATH . '/../var') . '/fb_ca_chain_bundle.crt'
		
	
	public function pingCallback($type, $data){
		$this->_statistics = $data;
	}
	
	private $_statistics = null;
	
	public function testProxy($pr)
	{
		throw new Exception('Missing component Sme_Proxy_ICMP');
		if(isset($pr['ip']) && preg_match('/([\d\.]+):(\d+)/', $pr['ip'], $matches)){
			$ip = $matches[1];

			$icmp = new Sme_Proxy_ICMP();
			$icmp->set_callback("statistics",array($this, 'pingCallback'));
			$icmp->display = false;
			$pingreceived = $icmp->ping($ip, 3);
			
			$stats = $this->_statistics;
			if(! $stats){
				throw new Exception("No stats for $ip");
			}
						
			$active='TRUE';
			$score = (int) (1000 / $stats["avgtime"] * (33 * $pingreceived));
			if (3 == $pingreceived) {
				Shaw_Log::debug("Proxy $ip is healthy : $score");
			} elseif ($pingreceived > 0) {
				Shaw_Log::debug("Proxy $ip is lossy : $score");
			} else {
				Shaw_Log::debug("Proxy $ip is dead");
				$active='FALSE';
			}
			$this->_statistics = null;
			
			//Verification de la connexion
			/*
			$chtmp = curl_init();
			curl_setopt_array($chtmp, $this->_curlopt);
			$ret = curl_exec ($chtmp);			
			if(!curl_errno($chtmp))
			{
				$info = curl_getinfo($chtmp);					
			}				
			curl_close($chtmp);
			*/
			$this->dbh->query("UPDATE proxy_instance SET score=$score, active='$active' WHERE ip='".$pr['ip']."'");
		}
		else{
			throw new Exception('Weird proxy ip ' . $pr['ip']);
		}
	}
	
	public function testProxies()
	{
		$prxs = $this->getProxies();
		if($prxs){
			foreach($prxs as $pr){
				$this->testProxy($pr);
			}
		}
	}
	
	
	public function importFromCsv($path, $delimiter = ';', $enclosure = '"')
	{
		if(! is_readable($path)){
			throw new Exception('Unknown path');
		}
		$fh = fopen($path, 'r');
		
		$credential = null;
		
		while($proxy = fgetcsv($fh, 0, $delimiter, $enclosure)){
			$proxy = array_shift($proxy);
			if(stripos($proxy, '#') === 0){
				// username and password
				$credential = substr($proxy, 1);
				Shaw_Log::debug('Credential are %s', $credential);
				continue;
			}
			$this->addProxy($proxy, $credential);
		}
		
		fclose($fh);
	}
	
	
	public function addProxy($proxy, $credential)
	{
		$result = $this->dbh->query('SELECT ip FROM proxy_instance WHERE ip=\''.$proxy.'\';');
		if($result != false){ // new db
			$arf = $result->fetchAll();
			if(empty($arf)){
				$this->dbh->exec("INSERT INTO proxy_instance(ip, credential) VALUES ('" . $proxy . "', '" . $credential . "')");
				Shaw_Log::info('Adding proxy %s', $proxy);
			}
		}
	}
	
	
	/**
	 * Removed last used proxy from active list
	 */
	/*
	public function revokeProxy($proxy)
	{
		$db = $this->_open();
		sqlite_exec($db,"UPDATE proxy SET active=\"FALSE\" WHERE ip = '".$proxy['ip']."'");
		sqlite_close($db);
		
		$count = $this->countNiceProxies();
		
		Shaw_Log::debug('Revoked %s proxy, left %s to use', $proxy['ip'], $count);
	}
	*/
	
	public function getProxies()
	{
		$result = $this->dbh->query("SELECT * FROM proxy_instance;");
		if($result != false){
			$result = $result->fetchAll();
		}
		
		return $result;
	}
	
	private $_niceSql = 'active="TRUE" AND score > 300 AND (requests is null OR requests < 200)';
	
	public function _getNiceProxy()
	{
		$result = $this->dbh->query('select * from proxy_instance where '.$this->_niceSql.' order by requests ASC');
		
		if($result == false){
			throw new Exception('ups');
		}
		
		$arf = $result->fetch();
		if(empty($arf)){
			throw new Exception('no proxy left');
		}
		
		return $arf;
	}
	
	/*
	public function getProxy($db, $ip)
	{
		$result = sqlite_query($db,'select * from proxy_instance where ip="'.$ip.'"');
		
		if($result == false){
			throw new Exception('ups');
		}
		
		$arf = sqlite_fetch_array($result);
		if(empty($arf)){
			throw new Exception('no proxy left');
		}
		
		return $arf;
	}
	
	public function countNiceProxies()
	{
		$db = $this->_open();
		$result = sqlite_query($db,'select count(*) from proxy where '.$this->_niceSql);
		$result = sqlite_fetch_array($result);
		return $result[0];
	}
*/
	public function resetRequestCount()
	{
		$this->dbh->exec("UPDATE proxy_instance SET requests=0");
	}
	
}
