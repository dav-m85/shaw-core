<?php

/**
 * Log helper as a singleton.
 */
// TODO write a log formatter for exceptions.
// TODO should call an existing zend log, or create it.
// TODO require once Zend_Log
class Shaw_Log extends Zend_Log
{
    private static $_instance = null;

    /**
     *
     */
    public static function getInstance() 
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
            
            if ($bootstrap && $bootstrap->hasPluginResource('log')) {
                self::$_instance = $bootstrap->getPluginResource('log')->getLog();
            }
            else{
                // Define console logger and thats it !
                throw new Exception('No logger has been defined.');            
            }
        }
        return self::$_instance;
    }
    
    public static function setInstance(Zend_Log $log){
        self::$_instance = $log;
    }
    
    public function __clone(){
        trigger_error('Unauthorized cloning', E_USER_ERROR);
    }
    
    /**
     * Log a debug message.
     * @param mixed $object Any combination of string, sprintf args, Exception
     */
    public static function debug($object){
        $params = func_get_args();
        self::_write(Zend_Log::DEBUG, $params);
    }
    
    public static function info($object){
        $params = func_get_args();
        self::_write(Zend_Log::INFO, $params);
    }
    
    public static function notice($object){
        $params = func_get_args();
        self::_write(Zend_Log::NOTICE, $params);
    }
    
    public static function warn($object){
        $params = func_get_args();
        self::_write(Zend_Log::WARN, $params);
    }
    
    public static function error($object){
        $params = func_get_args();
        self::_write(Zend_Log::ERR, $params);
    }
    
    public static function critical($object){
        $params = func_get_args();
        self::_write(Zend_Log::CRIT, $params);
    }
    
    public static function alert($object){
        $params = func_get_args();
        self::_write(Zend_Log::ALERT, $params);
    }
    
    public static function emergency($object){
        $params = func_get_args();
        self::_write(Zend_Log::EMERG, $params);
    }
    
    const EXCEPTION_EOL = PHP_EOL; //'<br>';
    
    /**
     * Factory to construct the logger and one or more writers
     * based on the configuration array
     *
     * @param  array|Zend_Config Array or instance of Zend_Config
     * @return Zend_Log
     * @throws Zend_Log_Exception
     */
    static public function factory($config = array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
    
        if (!is_array($config) || empty($config)) {
            /** @see Zend_Log_Exception */
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception('Configuration must be an array or instance of Zend_Config');
        }
    
        $log = new self;
    
        if (array_key_exists('timestampFormat', $config)) {
            if (null != $config['timestampFormat'] && '' != $config['timestampFormat']) {
                $log->setTimestampFormat($config['timestampFormat']);
            }
            unset($config['timestampFormat']);
        }
    
        if (!is_array(current($config))) {
            $log->addWriter(current($config));
        } else {
            foreach($config as $writer) {
                $log->addWriter($writer);
            }
        }
    
        return $log;
    }
    
    /**
     * Params may be
     *  - string
     *  - strings, first being a sprintf
     *  - string, exception
     *  - exception
     *  - object
     * 
     * @param unknown_type $level
     * @param array $params
     */
    private static function _write($level, $params)
    {
        if(count($params) == 1){
            $message = $params[0];
        	if($message instanceof Exception){
                $exception = $message;
            }
            else if(is_object($message) || is_array($message)){
                $message = var_export($message, true);
            }
            
        }
        else if(count($params) > 1){
            if($params[0] instanceof Exception){
                $message = $params[1];
                $exception = $params[0];
            }
            else if($params[1] instanceof Exception){
                $message = $params[0];
                $exception = $params[1];
            } else {
            	$striMode = true;
                for($i = 0; $i < count($params); $i++){
                	if(! is_string($params[$i]) && ! is_numeric($params[$i]) && ! is_null($params[$i])){
                		$params[$i] = var_export($params[$i], true);
                		$striMode = false;
                	}
                }
            	
            	if($striMode){
            		$message = call_user_func_array('sprintf', $params);
            	}
            	else{
            		$message = join(PHP_EOL, $params);
            	}
            }
        }
        
        // Formating exception
        if($exception){
            $stackTrace = '';
            $parameters = array();
            
            foreach($exception->getTrace() as $tracEntry){
                if(!empty($tracEntry['args']))
                foreach($tracEntry['args'] as $en)
                    $parameters[] = (is_object($en)) ? get_class($en) : gettype($en);
                    
                $stackTrace .= sprintf('at %s:%s %s(%s)'. self::EXCEPTION_EOL,
                $tracEntry['file'],
                $tracEntry['line'],
                $tracEntry['function'],
                (!empty($parameters)) ? join(', ',$parameters) : '');
            }
            
            if(! empty($message))
                $message .= " => ";
            
            $message .= sprintf('Exception : %s (%s:%s),' . self::EXCEPTION_EOL . '%s',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $stackTrace);
        }
        
        // Backtrace display
        $backtrace = debug_backtrace(false);
        $failsafe = 5;
        do{
        	if(empty($backtrace)){
        		break;
        	}
        	$caller = array_shift($backtrace);
        } 
        while($caller["class"] == "Shaw_Log" && ! empty($backtrace) && --$failsafe >= 0);
        
        self::getInstance()->log($message, $level, array('caller' => $caller["class"]));
    }
    
    /**
     * Look for a specific instance of writer and remove it.
     */ 
    public function removeWriter($writer)
    {
        if(($arf = array_search($writer, $this->_writers, true) !== false)){
        	$writer->shutdown();
            unset($this->_writers[$arf]);
        }
    }
    
    /**
     * Removes all writers
     */
    public function removeAllWriters()
    {
    	foreach($this->_writers as $writer){$writer->shutdown();}
        $this->_writers = array();
    }
}