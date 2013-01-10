<?php


/**
 *
 */ 
class Shaw_Core{
    /**
     * Execute a system command.
     *
     * @return bool Return value of the command.
     */ 
    public static function executeCommand($command){
        if(self::getPHPMajorVersion() <= 5.2 && self::getCurrentOS() == self::WINDOWS){
            Shaw_Log::debug("Windows mode execution");
            $command = str_replace('"','', $command);
            $command = '"'.$command.'"';
        }
        Shaw_Log::debug(sprintf('EXEC:%s', $command));
        exec($command, $output, $return_value);
        foreach($output as $line)
            Shaw_Log::warn(sprintf('&nbsp;&nbsp;&nbsp;&nbsp;%s', $line));
        Shaw_Log::debug(sprintf('RETURN:%s', $return_value));
        return $return_value;
    }
    
    
    // TODO : Pull images from iTunes, clean unused images !
    // PHP5.2
    /*
    if(function_exists('lcfirst') === false) {
        function lcfirst($str) {
            $str[0] = strtolower($str[0]);
            return $str;
        }
    }
    */
    
    public static function array_to_object($tab)
    {
    	$data = new stdClass ;
    	if(is_array($tab) && !empty($tab))
    	{
    		foreach($tab as $key => $val)
    		{
    			if(is_array($val))
    				$data->$key = self::array_to_object($val);
    			else
    				$data->$key = $val ;
    		}
    	}
    	return $data ;
    }
    
    /**
     * Special function that return current stack level in a nice format!
     */
    public static function probe()
    {
    	// Do we have a server root ?
    	$root = realpath(APPLICATION_PATH . '/../'); 
    	// Backtrace display
        $bkt = debug_backtrace(false);
        $lns = array();
        for($i = 0; $i <= count($bkt) - 1; $i++)
        {
        	$file = $bkt[$i]['file'];
        	if($root && ($ex = stripos($file, $root)) === 0){$file = substr($file, strlen($root) + 1);}
        	$file = strtr($file, '\\', '/');
        	$class = (isset($bkt[$i]['class']) ? $bkt[$i]['class'].$bkt[$i]['type'] : '');
        	$ar = array();
        	if(isset($bkt[$i]['args']) && ! empty($bkt[$i]['args'])){
        		foreach($bkt[$i]['args'] as $arg){
        			if(is_numeric($arg) || is_string($arg)){
        				$ar[] = substr((string)$arg, 0 , 1024);
        			}
        			else if(is_bool($arg)){
        				$ar[] = $arg ? 'true' : 'false';
        			}
        			else if(is_object($arg)){
        				$ar[] = get_class($arg);
        			}
        			else{
        				$ar[] = gettype($arg);
        			}
        		}
        	}
        	$func = $bkt[$i]['function'] . '('.join(', ', $ar).')';
        	
        	$lns[] = '#' . $i . ' '. $file . '(' . $bkt[$i]['line'] . ') : ' . $class . $func;
        }
        
        return $lns;
    }
    
    public static function dump($value,$level=0)
    {
        if ($level==-1)
        {
            $trans[' ']='&there4;';
            $trans["\t"]='&rArr;';
            $trans["\n"]='&para;;';
            $trans["\r"]='&lArr;';
            $trans["\0"]='&oplus;';
            return strtr(htmlspecialchars($value),$trans);
        }
        if ($level==0) echo '<pre>';
        $type= gettype($value);
        echo $type;
        if ($type=='string')
        {
            echo '('.strlen($value).')';
            $value= self::dump($value,-1);
        }
        elseif ($type=='boolean') $value= ($value?'true':'false');
        elseif ($type=='object')
        {
            $props= get_class_vars(get_class($value));
            if(method_exists($value, '__toString')){$str=$value->__toString();}
            echo '('.count($props).') <u>'.get_class($value).'</u>' . ' ' . $str;
            foreach($props as $key=>$val)
            {
                echo "\n".str_repeat("\t",$level+1).$key.' => ';
                self::dump($value->$key,$level+1);
            }
            $value= '';
        }
        elseif ($type=='array')
        {
            echo '('.count($value).')';
            foreach($value as $key=>$val)
            {
                echo "\n".str_repeat("\t",$level+1).self::dump($key,-1).' => ';
                self::dump($val,$level+1);
            }
            $value= '';
        }
        echo " <b>$value</b>";
        if ($level==0) echo '</pre>';
    }
    
    public static function lcfirst($str)
    {
        $str[0] = strtolower($str[0]);
        return $str;
    }
    
    public static function ucfirst($str)
    {
        $str[0] = strtoupper($str[0]);
        return $str;
    }
    
    public static function getPHPMajorVersion(){
        return (float)PHP_VERSION;
    }
    
    public static function format_bytes($size)
    {
    	$units = array('B', 'K', 'M', 'G', 'T');
    	for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    	return round($size, 2).$units[$i];
    }
    
    public static function getMicrotime()
    {
        $microtime = explode(' ', microtime());
        return $microtime[1] . substr($microtime[0], 1);
    }
    
    // Format time in ms
    public static function format_microtime($time, $shorten = 5)
    {
        $time = (float)$time;
    	if($time == 0.0) return 'no time';
        $pers = array("day"=> 3600000 * 24, "hour" => 3600000, "min" => 60000, "s" => 1000, "ms" => 1);
        $st = array();
        foreach ($pers as $p => $dur){
            $d = floor($time / (float)$dur);
            $time = $time % (float)$dur;
            if ( $d == 0 || $shorten <= 0 ) continue;
            $st[] = "{$d}{$p}";
            $shorten--;
        }
        return implode(' ', $st);
    }
    
	// Format time in ms
    public static function format_second($time, $shorten = 5)
    {
        $time = (float)$time;
    	if($time == 0.0) return 'no time';
        $pers = array("day"=> 3600 * 24, "hour" => 3600, "min" => 60, "s" => 1);
        $st = array();
        foreach ($pers as $p => $dur){
            $d = floor($time / (float)$dur);
            $time = $time % (float)$dur;
            if ( $d == 0 || $shorten <= 0 ) continue;
            $st[] = "{$d}{$p}";
            $shorten--;
        }
        return implode(' ', $st);
    }
    
    /*
    ($seconds, $max_periods)
    {
        $periods = array("year" => 31536000, "month" => 2419200, "week" => 604800, "day" => 86400, "hour" => 3600, "minute" => 60, "second" => 1);
        $i = 1;
        foreach ( $periods as $period => $period_seconds )
        {
            $period_duration = floor($seconds / $period_seconds);
            $seconds = $seconds % $period_seconds;
            if ( $period_duration == 0 ) continue;
            $duration[] = "{$period_duration} {$period}" . ($period_duration > 1 ? 's' : '');
            $i++;
            if ( $i >  $max_periods ) break;
        }
        return implode(' ', $duration);
    }
    */
    
    public static function loadFile($sFilename){
        if (floatval(phpversion()) >= 4.3) {
            
        } else {
            if (!file_exists($sFilename)) throw new Exception("File does not exists");
            $rHandle = fopen($sFilename, 'rb');
            if (!$rHandle) throw new Exception("Cannot open file");
            
            $sData = '';
            while(!feof($rHandle))
                $sData .= fread($rHandle, filesize($sFilename));
            fclose($rHandle);
        }
        
        return $sData;
    }
    
    /**
     * Extract constants from a class using a prefix.
     * 
     * @param mixed $object Anything recognized by ReflectionClass constructor, please check phpdoc.
     * @param string $prefix Prefix for constant to look for.
     * @param string $stripPrefix Remove prefix from returned result ? Default false.
     */
    public static function getConstantsLookup($object, $prefix = null, $stripPrefix = false)
    {
    	$r = new ReflectionClass($object);
    	$constants = $r->getConstants();
    	
    	if($prefix && is_string($prefix) && is_array($constants)){
    		$prefix = strtoupper($prefix);
    		foreach($constants as $key => $value){
    			if(strpos($key, $prefix) === 0){
    				if($stripPrefix){
    					$key = substr($key, strlen($prefix)); // strip one more caracter
    				}
    				$result[$key] = $value;
    			}
    		}
    	}
    	else
    		$result = $constants;
    	
    	// Shoudl throw error if array_flip throws an alert
    	$result = array_flip($result);
    	
    	return $result;
    }
    
    public static function getConstantsComments($object, $prefix = null, $stripPrefix = false)
    {
    	$al = Zend_Loader_Autoloader::getInstance();
    	$cal = $al->getClassAutoloaders($object);
    	if(! empty($cal)){
    		$cal0 = $cal[0];
    		$source = file_get_contents($cal0->getClassPath($object));
    		$tokens = token_get_all($source);
    		
    		// Parse tokens for const comments
    		$lastcom = null;
    		$capture = false;
    		$results = array();
	    	foreach ($tokens as $token) {
			    if (is_string($token)) {
			   		// reset when crossing a codeline ending.
			       	if($token == ';'){
			       		$lastcom = null;
    					$capture = false;
			       	}
			    } 
			    else {
			        // token array
			        list($id, $text, $line) = $token;
			
                    switch ($id) { 
                        // catpure comments
                        case T_DOC_COMMENT:
							$arr = explode(PHP_EOL, $text);
							$arr2 = array('summary' => null);
							for($i = 0; $i <=  count($arr) - 1; $i++){
								$t = $arr[$i];
								if(preg_match('!^[/*\s]*$!', $t)){
									continue;
								}
								$t = trim($t);
								$t = trim($t, ' *');
								if(preg_match('!^(@\w*)\s+(.*)!', $t, $matches)){
									$arr2[$matches[1]] = $matches[2];
									continue;
								}
								if($arr2['summary'] == null){
									$arr2['summary'] = $t;
									continue;
								}
								$arr2['description'].= $t;
							}
                           	$lastcom = $arr2;
                           	break;
                       // reset when crossing a function
                       case T_FUNCTION:
                           $lastcom = null;
                           $capture = false;
                           break;
                       // waow, that's a constant we got a comment for.
                       case T_CONST:
                           if($lastcom){
                               $capture = true;
                           }
                           break;
                       case T_STRING: 
                           if($capture){
                               $capture = false;
                               $results[$text] = $lastcom;
                           }
                           break;
			        }
			    }
			}
    		
			// Cleanup const names regarding providen filters.
			$results2 = array();
    		if($prefix && is_string($prefix) && is_array($results2)){
	    		$prefix = strtoupper($prefix);
	    		foreach($results as $key => $value){
	    			if(strpos($key, $prefix) === 0){
	    				if($stripPrefix){
	    					$key = substr($key, strlen($prefix)); // strip one more caracter
	    				}
	    				$results2[$key] = $value;
	    			}
	    		}
	    	}
	    	else{
	    		$results2 = $results;
	    	}
	    	
	    	return $results2;
	    }
    }
    
    const LINUX = 1;
    const WINDOWS = 2;
    
    public static function getCurrentOS(){
        if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            return self::WINDOWS;
        else
            return self::LINUX;
    }
    
    /*
    public static function format_bytes($size) {
        $units = array(' B', ' KiB', ' MeB', ' GiB', ' TeB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
        return round($size, 2).$units[$i];
    }
    */
    public static function getSize($path){
        return self::format_bytes(filesize($path));
    }
    
    public static function absolutePath($url = null){
    	throw new Exception('Not implemented');
        $publicPath = null; //realpath(Zend_Registry::get('config')->publicPath);
        
        $filePath = $publicPath . $url;
        
        $filePath = realpath($filePath);
        
        if(! file_exists($filePath))
            throw new Exception("$publicPath$url doesn't exist.");

        if (($pos = strripos($filePath, $publicPath)) !== false) {
            $fileRelativePath = substr($filePath, strlen($publicPath));
        }
        else
            throw new Exception("$filePath isn't inside the public folder.");
        
        return str_replace('\\','/',$filePath);
    }

    public static function transformToRelativePath($file = null){
        throw new Exception('Not implemented');
        $publicPath = null; //realpath(Zend_Registry::get('config')->publicPath);
        
        if(! file_exists($file))
            throw new Exception("$file doesn't exist.");
            
        $filePath = realpath($file);
        
        if (($pos = strripos($filePath, $publicPath)) !== false) {
            $fileRelativePath = substr($filePath, strlen($publicPath));
        }
        else
            throw new Exception("$filePath isn't inside the public folder.");
        
        return str_replace('\\','/',$fileRelativePath);
    }
    
    protected static $_normalizeChars = array(
    		'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
    		'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
    		'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
    		'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
    		'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
    		'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
    		'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f'
    );
    
    function stripAccents($stripAccents){
    	return strtr();
    	//return strtr($stripAccents,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }
    
    public static function metaname($toClean){
    	$toClean = self::str_encode_utf8($toClean);
    	// Shaw_Log::debug('utf: %s', $toClean);
    	/*
    	
    	$name = self::stripAccents($name);
    	
    	//$name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
    	$name = mb_strtolower($name,'UTF-8');
    	$name = preg_replace('/[^a-z0-9\s]/i', '', $name);
        $name = preg_replace('/\s/', '-', $name);
        $name = preg_replace('/--/', '-', $name);
        */
    	$toClean     =     str_replace('&', '-and-', $toClean);
    	$toClean     =     strtr($toClean, self::$_normalizeChars);
    	$toClean     =     trim(preg_replace('/[^\w\d_ -]/si', '', $toClean)); //remove all illegal chars    	
    	$toClean 	 = 	   mb_strtolower($toClean,'UTF-8');
    	$toClean     =     str_replace(' ', '-', $toClean);
    	$toClean     =     str_replace('--', '-', $toClean);
        // Shaw_Log::debug('strip: %s', $toClean);
        return $toClean;
    }
    
    static function str_encode_utf8($string) {
    	if (mb_detect_encoding($string, 'UTF-8', true) === FALSE) {
    		$string = utf8_encode($string);
    	}
    	return $string;
    }
    
    static function str_decode_utf8($string) {
    	if (mb_detect_encoding($string, 'UTF-8', true) === TRUE) {
    		$string = utf8_decode($string);
    	}
    	return $string;
    }
    
    // TODO
    public static function listFiles($dir){
        /*$files = scandir($dir);
        
        // Execute compilation on each of them.
        foreach($files as $file){
            $info = pathinfo($dir.$file);
            Shaw_Log::debug("Scanning $dir.$file");
            if($info['extension'] != 'swf') continue;
            
            $arf = new Model_Task_Banner_Compile(Shaw_Log::getInstance());
            $arf->addArgument('path', $dir.$file);
            $arf->execute();
        }*/
    }
    
    // Found on http://www.php.net/manual/fr/function.array-merge-recursive.php.
    // TODO :Is it used ???
    public static function array_merge_recursive_distinct () {
        $arrays = func_get_args();
        $base = array_shift($arrays);
        if(!is_array($base)) $base = empty($base) ? array() : array($base);
        foreach($arrays as $append) {
          if(!is_array($append)) $append = array($append);
          foreach($append as $key => $value) {
            if(!array_key_exists($key, $base) and !is_numeric($key)) {
              $base[$key] = $append[$key];
              continue;
            }
            if(is_array($value) or is_array($base[$key])) {
              $base[$key] = self::array_merge_recursive_distinct($base[$key], $append[$key]);
            } else if(is_numeric($key)) {
              if(!in_array($value, $base)) $base[] = $value;
            } else {
              $base[$key] = $value;
            }
          }
        }
        return $base;
      }
      
    /**
     * Scan all files inside directory recursively.
     * 
     * @param string $directory path where to look for
     * @param array $filter if specified, will keep only these extensions. ex: array('doc', 'phtml')
     */ 
    public static function scandirr($directory, $filter = array(), $relpath = null)
    {
        $files = scandir($directory);
        $result = array();
        foreach($files as $file){
            if( $file == '.' || $file == '..' || strpos($file,'.') === 0 ) continue;
            if( is_dir  ($directory . '/' . $file) ) $result = array_merge($result, self::scandirr(realpath($directory.'/'.$file), $filter, ($relpath ? $relpath.'/' : null).$file));
            if( is_file ($directory . '/' . $file) ){
                $extp = strrpos($file,'.');
                if($extp != false && !empty($filter)){
                    $ext = substr($file, $extp + 1);
                    if(! in_array($ext, $filter))
                        continue;
                }
                $result[] = ($relpath ? $relpath.'/' : null).$file;
            }
        }
        return $result;
    }
    
    // Remove all content from a public folder.
    private function _purgeFolder($folderPath){
        $path = $folderPath;
        
        $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);
        
        for ($dir->rewind(); $dir->valid(); $dir->next()) {
            
            if(!strpos($dir->getPathname(), '.svn'))
                if ($dir->isDir()) {
                    rmdir($dir->getPathname());
                    
                } else {
                    unlink($dir->getPathname());
                }
        }
        //rmdir($path);
    }
    
    public static function tempnam($dir , $prefix, $suffix = null){
		if($dir == null || ! file_exists($dir))
		    //$dir = sys_get_temp_dir();
		    $dir = realpath(APPLICATION_PATH . '/../var/tmp'); // TODO : mmmmmhh
		
		do{
		    $rand = substr(md5(rand()), 10);
		    $file = $dir . '/' . $prefix . $rand . $suffix;
		}while(file_exists($file));
		
		touch($file);
		return $file;
    }
    
    function diePixel()
    {
        $fp = fopen(realpath(APPLICATION_PATH . "/../public/images/1px.png"),"r") ;
        if($fp === false)
            throw new Exception("Cannot open $fp");
            
        while (! feof($fp)) {
               $buff = fread($fp,4096);
               print $buff; 
        }
        fclose($fp);
        die;
    }
    
    static $gc = false;
    
    
    public static function mediaManage($groupName, Zend_View $view)
    {
    	throw new Exception('Not implemented');
        $publicPath = null; //realpath(Zend_Registry::get('config')->publicPath);
        $minify = null; //Zend_Registry::get('config')->minify;
        if (false === self::$gc) {
            self::$gc = (require APPLICATION_PATH . '/../public/min/groupsConfig.php');
        }
        
        $gtype = substr($groupName, 0, 2);
        if(! array_key_exists($groupName, self::$gc))
            throw new Exception('unknown groupName');
        
	if($minify){
            if($gtype == 'js') $view->headScript()->appendFile(self::Minify($groupName)/*'/min/?g=' . $groupName*/);
	    if($gtype == 'cs') $view->headLink()->appendStylesheet(self::Minify($groupName)/*'/min/?g=' . $groupName*/);
	}
	else{
	    foreach(self::$gc[$groupName] as $url){
		if($gtype == 'js') $view->headScript()->appendFile(substr($url, 1).'?'.time());
		if($gtype == 'cs') $view->headLink()->appendStylesheet(substr($url, 1).'?'.time());
	    }
	}
    }
    
    /**
     * Fill an array with incrementing date, from startdate to enddate.
     *
     * Proves usefull for presenting date and hydrate it.
     * 
     * @todo Could be solved as a custom Hydrator for Doctrine.
     */ 
    public static function stuffTable($startdate, $enddate)
    {
        // Build empty table.
        $data = array();
        $indexdate = clone $startdate;
        while($indexdate < $enddate){
            $data[$indexdate->format('d/m/Y')] = array();
            $indexdate->modify('+1 day');
        }
        return $data;
    }
    
    public static function Minify($group)
    {
        require_once('Minify/Build.php');
        static $builds = array();
        if (false === self::$gc) {
            self::$gc = (require APPLICATION_PATH . '/../public/min/groupsConfig.php');
        }
        
        if (! isset($builds[$group])) {
            $builds[$group] = new Minify_Build(self::$gc[$group]);
        }
        return $builds[$group]->uri('/min/?g='.$group);
    }
    
    public static function base64url_encode($data) { 
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
    } 
      
    public static function base64url_decode($data) { 
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
    } 
    
	static function compress($data){
        $serialized = serialize($data);
        
        $filter = new Zend_Filter_Compress('Bz2');
        $filter->setBlocksize(8);
        $compressedData = $filter->filter($serialized);
        
        return $compressedData;
    }
    
	function diff($old, $new){
		foreach($old as $oindex => $ovalue){
			$nkeys = array_keys($new, $ovalue);
			foreach($nkeys as $nindex){
				$matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
					$matrix[$oindex - 1][$nindex - 1] + 1 : 1;
				if($matrix[$oindex][$nindex] > $maxlen){
					$maxlen = $matrix[$oindex][$nindex];
					$omax = $oindex + 1 - $maxlen;
					$nmax = $nindex + 1 - $maxlen;
				}
			}	
		}
		if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
		return array_merge(
			diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
			array_slice($new, $nmax, $maxlen),
			diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
	}

	function htmlDiff($old, $new){
		$diff = diff(explode(' ', $old), explode(' ', $new));
		foreach($diff as $k){
			if(is_array($k))
				$ret .= (!empty($k['d'])?"<del>".implode(' ',$k['d'])."</del> ":'').
					(!empty($k['i'])?"<ins>".implode(' ',$k['i'])."</ins> ":'');
			else $ret .= $k . ' ';
		}
		return $ret;
	}
}

