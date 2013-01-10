<?php

class Shaw_Controller_Request_Http 
	extends Zend_Controller_Request_Http
{
    private $_regionArticles = array(
        "97" => "l'",
        "98" => "l'",
        "99" => "la",
        "A1" => "la",
        "A2" => "la",
        "A3" => "le",
        "A4" => "la",
        "A5" => "la",
        "A6" => "la",
        "A7" => "la",
        "A8" => "l'",
        "A9" => "le",
        "B1" => "le",
        "B2" => "la",
        "B3" => "les",
        "B4" => "le",
        "B5" => "les",
        "B6" => "la",
        "B7" => "le",
        "B8" => "la",
        "B9" => "les",
        "C1" => "l'");
    
    protected $_geolocalisation = null;
    /**
     * Get the client's geolocalised coordinates.
     * 
     * @return array|false|null array with geo properties, or false for failure, null when cannot determine position.
     * // Should be cached / memcached
     */
    public function getGeolocalisation()
    {
        if(! $this->_geolocalisation)
        {
            $ip = $this->getClientIp();
            
            if(strpos($ip, '10.8.1') === 0 || strpos($ip, '192.168.0') === 0 || strpos($ip, '127.0.0.1') === 0){
                Shaw_Log::debug('Test IP assigned for geolocalisation');
                $ip = '82.232.100.75';
            }
            
            $geoipPath = realpath(Zend_Registry::get("config")->geoipDataFilePath);
            
            if($geoipPath == null){
                Shaw_Log::warn("Geolocalisation file not found.");
                return false;
            }
            
            try{
                include("Maxmind/geoip.inc");
                include("Maxmind/geoipcity.inc");
                include("Maxmind/geoipregionvars.php");
                
                // uncomment for Shared Memory support
                //geoip_load_shared_mem($geoipPath);
                //$gi = geoip_open($geoipPath, GEOIP_SHARED_MEMORY);
                
                $gi = geoip_open($geoipPath, GEOIP_STANDARD);
                $record = (array)geoip_record_by_addr($gi, $ip);
                geoip_close($gi);
                
                // Region lookup
                $record["region_name"] = $GEOIP_REGION_NAME[$record["country_code"]][$record["region"]];
                $record["region_article"] = $this->_regionArticles[$record["region"]];
                $this->_geolocalisation = $record;
            }
            catch(Exception $e){
                Shaw_Log::error("Geolocalisation failed.", $e);
                return false;
            }
        }
        
        return $this->_geolocalisation;
    }
    
    public function isLocalhost(){
        return false;
    }
    
    public function getSubdomain(){
        $hostname = $_SERVER['HTTP_HOST'];
        $subdomain = substr($hostname, 0, strpos($hostname, '.'));
        return $subdomain;
    }
    
    protected $_botNames = array(
                    "Teoma",                    
                    "alexa", 
                    "froogle", 
                    "inktomi", 
                    "looksmart", 
                    "URL_Spider_SQL", 
                    "Firefly", 
                    "NationalDirectory", 
                    "Ask Jeeves", 
                    "TECNOSEEK", 
                    "InfoSeek", 
                    "WebFindBot", 
                    "girafabot", 
                    "crawler", 
                    "www.galaxy.com", 
                    "Googlebot", 
                    "Scooter", 
                    "Slurp", 
                    "appie", 
                    "FAST", 
                    "WebBug", 
                    "Spade", 
                    "ZyBorg", 
                    "rabaz"); 
    
    /**
     * Tells if the current request is issued by a crawler/spider/bot
     * 
     * @return boolean true if current request is from a bot
     * @see http://www.insanevisions.com/article/214/Bot-Detection-with-PHP/
     */
    public function isBot()
    {
    	foreach($this->_botNames as $bot) { 
	      	if(stripos($bot, $_SERVER['HTTP_USER_AGENT']) !== false) { 
				return true;
    		}
    	}
    	return false;
    }
    
    /**
     * Proxy for mobile class.
     * 
     * @see http://code.google.com/p/php-mobile-detect/
     */
    public function isMobile()
    {
    	require_once 'Mobile/Detect.php';
    	
    	$mobile = new Mobile_Detect();
    	return $mobile->isMobile();
    }
}