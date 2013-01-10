<?php

require_once 'Zend/Log/Formatter/Simple.php';

class Shaw_Log_Formatter_Color
extends Zend_Log_Formatter_Simple
{
    // '%timestamp% %priorityName% (%priority%): %message%'
    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
        $BLACK="\033[0;30m";
		$DARKGRAY="\033[1;30m";
		$BLUE="\033[0;34m";
		$LIGHTBLUE="\033[1;34m";
		$GREEN="\033[0;32m";
		$LIGHTGREEN="\033[1;32m";
		$CYAN="\033[0;36m";
		$LIGHTCYAN="\033[1;36m";
		$RED="\033[0;31m";
		$LIGHTRED="\033[1;31m";
		$PURPLE="\033[0;35m";
		$LIGHTPURPLE="\033[1;35m";
		$BROWN="\033[0;33m";
		$YELLOW="\033[1;33m";
		$LIGHTGRAY="\033[0;37m";
		$WHITE="\033[1;37m";
    	$RESET="\033[0m";
    	
    	$cols = array(
    		Zend_Log::DEBUG => $GREEN,
    		Zend_Log::INFO	=> $LIGHTBLUE,
    		Zend_Log::NOTICE => $CYAN,
    		Zend_Log::WARN  => $YELLOW,
    		Zend_Log::ERR	=> $RED,
    		Zend_Log::CRIT => $RED,
    		Zend_Log::ALERT => $RED,
    		Zend_Log::EMERG => $PURPLE
    	);
    	
        $output = $this->_format;
        // event[caller]
        foreach ($event as $name => $value) {
            if ((is_object($value) && !method_exists($value,'__toString'))
                || is_array($value)
            ) {
                $value = gettype($value);
            }
            
            if($name == 'priorityName'){
                $pri = $event['priority'];
                $value = $cols[$pri] . str_pad(substr($value,0,5), 5) . $RESET;
            }
            
            $output = str_replace("%$name%", $value, $output);
        }

        return $output;
    }
}