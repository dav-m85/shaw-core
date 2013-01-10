<?php

class Shaw_View_Helper_Shorten
extends Zend_View_Helper_Abstract
{
    public function shorten($string, $max)
    {
        if($max < 1){
        	throw new Exception('please specify a big max');
        }
    	if(strlen($string) <= $max){
    		return $string;
    	}
        
    	// detect whitespaces
        $wis = array();
        $off = 0;
    	while(($i = stripos($string, ' ', $off)) && $off < $max){
    		if($i > $max){
    			break;
    		}
    		$wis[] = $i;
    		$off = $i + 1;
    	}
    	if(empty($wis)){
    		return substr($string, 0, $max - 3) . '...';
    	}
    	else{
    		return substr($string, 0, array_pop($wis));
    	}
    }
}