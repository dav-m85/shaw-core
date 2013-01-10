<?php

/**
 * Multi purpose benchmarking class.
 *
 * highly inspired by PEAR::Benchmark, but rewritten for singleton
 * and lightweight structure.
 */
/*
 Comme le logger,
 est singleton
 poss�de un view helper
 d�coupl� du reste
 poss�de une ressource fa�on Shaw
 configurable via la ressource dans le config
 
*/
class Shaw_Benchmark
{    
    /********* SINGLETON SECTION ***********/
    
    /**
     *
     */
    private static $_instance = null;
    
    /**
     * Retrieve singleton instance.
     */
    static function getInstance(){
        if(self::$_instance == null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Set singleton instance.
     */ 
    public static function setInstance(Shaw_Benchmark $log)
    {
        self::$_instance = $log;
    }
    
    /**
     * Clone magic function, blocked for singleton.
     */
    public function __clone()
    {
        trigger_error('Unauthorized cloning', E_USER_ERROR);
    }
    
    /********* /SINGLETON SECTION ***********/
    
    /**
     * Contains all profiling/benchmarking data.
     * [0] => [type, name, time, metadata]
     */
    private $_marks = array();
    
    /**
     * marks type.
     */
    const START = 1;
    const STOP = 2;
    const MARK = 3;
    
    public static function reset(){
        self::getInstance()->_marks = array();
    }
    
    /**
     * start a section and/or the benchmarking.
     */ 
    public static function start($name = 'Global', $metadata = null, $isTrivial = false)
    {
        self::getInstance()->_setMarker($name, self::START, $metadata);
    }
    
    /**
     * stop a section
     */ 
    public static function stop($name = 'Global')
    {
        self::getInstance()->_setMarker($name, self::STOP, null);
    }
    
    /**
     * Signal an event.
     */ 
    // SQL query for example
    public static function mark($name, $metadata = null)
    {
        self::getInstance()->_setMarker($name, self::MARK, $metadata);
    }
    
    /**
     * Set marker.
     *
     * @param string $name Name of the marker to be set.
     *
     * Stored
     *      array[x]['name']  = name of marker x
     *      array[x]['type']  = type of the marker, const
     *      array[x]['time']  = time index of marker x
     *      array[x]['meta']  = metadata for marker x
     *
     * Computed by compute
     *      array[x]['diff']  = execution time from start to stop marker
     *      array[x]['at']    = total execution time up to marker x
     *      array[x]['calls'] = when merging, number of times called, only for sections
     *      array[x]['level'] = 
     * 
     * @see    start(), stop()
     * @access public
     * @return void
     */
    private function _setMarker($name, $type, $metadata) 
    {
        $this->_marks[] = array(
            'name' => $name,
            'type' => $type,
            'time' => $this->_getMicrotime(),
            'metadata' => $metadata,
            'id'   => count($this->_marks)
        );
    }
    
    /**
     * Merge eligible marks.
     */
    private function _merge(array $marks)
    {
        $merged = array();
        
        $previous = null;
        while($current = array_shift($marks)){
            if($previous != null){
                $previousIndex = count($merged) - 1;
                // SQL Merge
                if(isset($previous['metadata']['sql'])
                   && isset($current['metadata']['sql'])
                   && $previous['metadata']['query'] == $current['metadata']['query']){
                    // Updating previous.
                    $merged[$previousIndex]['duration'] += $current['duration'];
                    if($params = $current['metadata']['params']) $merged[$previousIndex]['metadata']['params'] = $params;
                    $merged[$previousIndex]['sql'] += $current['sql'];
                    $merged[$previousIndex]['calls']++;
                    $merged[$previousIndex]['callSteps'][$current['metadata']['sql']]++;
                    $previous = $current;
                    continue;
                }
            }
            $previous = $current;
            $merged[] = $current;
        }
        
        return $merged;
    }
    
    /**
     * Add computed data to all marks. Ice on cake.
     */
    private function _ice(array $marks)
    {
        $marks = $this->_marks;
        
        // Compute diff, total, level
        $first = reset($marks);
        $count = count($marks) - 1;
        
        for($i = 0; $i <= $count; $i++){
            $time = $marks[$i]['time'];
            
            if (extension_loaded('bcmath')) {
                $at  = bcsub($time, $first['time'], 6); //$total = bcadd($total, $diff, 6);
            } else {
                $at  = $time - $first['time']; //$total = $total + $diff;
            }
            
            $marks[$i]['at'] = $at;
        }
        
        $last = end($marks);
        $stack = array();
        
        // Stack coherence, compute duration, share
        for($i = $level = 0; $i <= $count; $i++){
            $time = $marks[$i]['time'];
            
            if($marks[$i]['type'] == self::START){
                $marks[$i]['level'] = $level;
                $level++;
                array_push($stack, $marks[$i]);
            }
            
            if($marks[$i]['type'] == self::STOP){
                $level--;
                $marks[$i]['level'] = $level;
                $current = array_pop($stack);
                
                // Check
                if($current['name'] != $marks[$i]['name']){
                    return "ERR: Closing {$current['name']} not ok!";
                }
                
                if (extension_loaded('bcmath')) {
                    $duration = bcsub($time, $current['time'], 6);
                } else {
                    $duration = $time - $current['time'];
                }
                
                $marks[$current['id']]['duration'] = $duration;
                $marks[$current['id']]['share']    = $duration / $last['at'] * 100;
            }
            
            if($marks[$i]['type'] == self::MARK){
                $marks[$i]['level'] = $level;
                if($duration = $marks[$i]['metadata']['duration'])
                    $marks[$i]['duration'] = $duration;
            }
        }
        
        return $marks;
    }
    
    
    // save stack, call, callers
    
    /**
     * Wrapper for microtime().
     *
     * @return float
     * @access private
     * @since  1.3.0
     */
    private function _getMicrotime() 
    {
        $microtime = explode(' ', microtime());
        return $microtime[1] . substr($microtime[0], 1);
    }
    
    /**
     * @return array:
     */
	public function getMarks()
	{
		return $this->_merge($this->_ice($this->_marks));
	}
    
    // TODO implement please
    public function getElapsedTime(){
    	$marks = $this->_marks;
    	$count = count($marks) - 1;
        $first = reset($marks);
        if($count >= 1){
        	return $this->_getMicrotime() - $first['time'];
        }
        else{
        	return 0;
        }
    }
}