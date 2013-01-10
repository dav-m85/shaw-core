<?php

class Shaw_View_Filter_Inliner
{
	public function filter($string)
    {
        $this->_loadCss();
        return preg_replace_callback('/<[\\s\\w="\']+>/', array($this, 'foundTag'), $string);
    }
    
    /**
     * Assoc array with classes/ids and definitions
     * @var unknown_type
     */
    private $_classes = array();
    
    private function _loadCss()
    {
    	$css = file_get_contents(APPLICATION_PATH . '/../public/assets/css/email.css');
    	preg_match_all( '/(?ims)([a-z0-9\s\.\:#_\-@,]+)\{([^\}]*)\}/', $css, $arr);

    	for($z = 0; $z < count($arr[0]); $z++){
    		$i = trim($arr[1][$z]);
    		$x = trim($arr[2][$z]);
    		$sel = explode(',', $i);
    		foreach($sel as $s){
    			if(stripos($s, '.') === 0){
    				$s = substr($s, 1);
    			}
    			else{
    				Shaw_Log::debug('Non class selectors are not supported yet. %s wont be added to filter replacement.', $s);
    				continue;
    			}
    			
    			$this->_classes[$s] = $x;
    		}
    	}
    }
    
    public function foundTag($matches)
    {
    	$tag = $matches[0];
    	$res = preg_replace_callback('/class=["\']([\w\s-_]*)["\']/', array($this, 'foundClass'), $tag);
    	return $res;
    }
    	
    public function foundClass($matches)
    {
    	$sel = explode(' ', $matches[1]);
    	$inline = null;
    	foreach($sel as $s){
    		if(! array_key_exists($s, $this->_classes)){
    			Shaw_Log::debug('Missing class %s', $s);
    			continue;
    		}
    		
    		$inline.= $this->_classes[$s];
    	}
    	
    	return 'style="'.$inline.'"';
    }
}