<?php

/**
 * Shaw_Task
 * 
 * Abstract class used for writing Tasks, inspired from Doctrine_Task.
 * Provides a few utilities :
 * - Common task descriptions, with arguments list and description.
 * - Task run validation.
 */
abstract class Shaw_Task
{
    public $dispatcher           =   null,
           $taskName             =   null,  /*Treat as protected*/
           $description          =   null,
           $arguments            =   array(),
           $requiredArguments    =   array(),
           $metrics				 =   array(),
           $optionalArguments    =   array(),
           $runHash				 =	 null;

    /**
     * __construct
     *
     * Since this is an abstract classes that extend this must follow a patter of Doctrine_Task_{TASK_NAME}
     * This is what determines the task name for executing it.
     *
     * @return void
     */
    public function __construct($dispatcher = null)
    {
        $this->dispatcher = $dispatcher;

        $taskName = $this->getTaskName();

        //Derive the task name only if it wasn't entered at design-time
        if (! strlen($taskName)) {
            $taskName = self::deriveTaskName(get_class($this));
        }

        /*
         * All task names must be passed through Doctrine_Task::setTaskName() to make sure they're valid.  We're most
         * interested in validating manually-entered task names, which are as good as arguments.
         */
        $this->setTaskName($taskName);
        
        $this->init();
    }
    
    public function init()
    {}
    
    public function preExecute()
    {}
    
    public function postExecute()
    {}

    /**
     * Returns the name of the task the specified class _would_ implement
     * 
     * N.B. This method does not check if the specified class is actually a Doctrine Task
     * 
     * This is public so we can easily test its reactions to fully-qualified class names, without having to add
     * PHP 5.3-specific test code
     * 
     * @param string $className
     * @return string|bool
     */
    public static function deriveTaskName($className)
    {
        $nameParts = explode('\\', $className);

        foreach ($nameParts as &$namePart) {
            $prefix = __CLASS__ . '_';
            $baseName = strpos($namePart, $prefix) === 0 ? substr($namePart, strlen($prefix)) : $namePart;
            $namePart = str_replace('_', '-', strtolower($baseName));
        }

        return implode('-', $nameParts);
    }
	
    /*
    
    Copyright (c) 2010, dealnews.com, Inc.
    All rights reserved.
    
    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:
    
    * Redistributions of source code must retain the above copyright notice,
    this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
    notice, this list of conditions and the following disclaimer in the
    documentation and/or other materials provided with the distribution.
    * Neither the name of dealnews.com, Inc. nor the names of its contributors
    may be used to endorse or promote products derived from this software
    without specific prior written permission.
    
    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
    AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
    IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
    ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
    LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
    CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
    		SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
    		INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
    POSSIBILITY OF SUCH DAMAGE.
    
    */
    
    /**
     * show a status bar in the console
    *
    * <code>
    * for($x=1;$x<=100;$x++){
    *
    *     show_status($x, 100);
    *
    *     usleep(100000);
    *
    * }
    * </code>
    *
    * @param   int     $done   how many items are completed
    * @param   int     $total  how many items are to be done total
    * @param   int     $size   optional size of the status bar
    * @return  void
    *
    */
    function show_status($done, $total, $size=30) {
    	static $start_time;
    
    	// if we go over our bound, just ignore it
    	if($done > $total) return;
    
    	if(empty($start_time)) $start_time=time();
    	$now = time();
    
    	$perc=(double)($done/$total);
    
    	$bar=floor($perc*$size);
    
    	$status_bar="\r[";
    	$status_bar.=str_repeat("=", $bar);
    	if($bar<$size){
    		$status_bar.=">";
    		$status_bar.=str_repeat(" ", $size-$bar);
    	} else {
    		$status_bar.="=";
    	}
    
    	$disp=number_format($perc*100, 0);
    
    	$status_bar.="] $disp%  $done/$total";
    
    	$rate = ($now-$start_time)/$done;
    	$left = $total - $done;
    	$eta = round($rate * $left, 2);
    
    	$elapsed = $now - $start_time;
    
    	$status_bar.= sprintf(" remaining: %s  elapsed: %s  peak: %s",
    			Shaw_Core::format_second($eta),
    			Shaw_Core::format_second($elapsed),
    			Shaw_Core::format_bytes(memory_get_peak_usage())
    	);
    	
    	echo "$status_bar      ";
    
    	flush();
    
    	// when done, send a newline
    	if($done == $total) {
    	echo "\n";
    	}
    }
    
    /**
     * notify
     *
     * @param string $notification 
     * @return void
     */
    // TODO should be rather a Log call...
    public function notify($notification = null)
    {
        if (is_object($this->dispatcher) && method_exists($this->dispatcher, 'notify')) {
            $args = func_get_args();
            
            return call_user_func_array(array($this->dispatcher, 'notify'), $args);
        } else if ( $notification !== null ) {
            return $notification;
        } else {
            return false;
        }
    }

    /**
     * ask
     *
     * @return void
     */
    public function ask()
    {
        $args = func_get_args();
        
        call_user_func_array(array($this, 'notify'), $args);
        
        $answer = strtolower(trim(fgets(STDIN)));
        
        return $answer;
    }

    /**
     * execute
     *
     * Override with each task class
     *
     * @return void
     * @abstract
     */
    abstract function execute();

    /**
     * validate
     *
     * Validates that all required fields are present
     *
     * @return bool true
     */
    public function validate()
    {
        $requiredArguments = $this->getRequiredArguments();
        
        foreach ($requiredArguments as $arg) {
            if ( ! isset($this->arguments[$arg])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * addArgument
     *
     * @param string $name 
     * @param string $value 
     * @return void
     */
    public function addArgument($name, $value)
    {
        $this->arguments[$name] = $value;
    }

    /**
     * getArgument
     *
     * @param string $name 
     * @param string $default 
     * @return mixed
     */
    public function getArgument($name, $default = null)
    {
        if (isset($this->arguments[$name]) && $this->arguments[$name] !== null) {
            return $this->arguments[$name];
        } else {
            return $default;
        }
    }

    /**
     * getArguments
     *
     * @return array $arguments
     */
    public function getArguments()
    {
        return $this->arguments;
    }
    
    public function hasArgument($name){
        return (isset($this->arguments[$name]) && $this->arguments[$name] !== null);
    }
    
    /**
     * setArguments
     *
     * @param array $args 
     * @return void
     */
    public function setArguments(array $args)
    {
        $this->arguments = $args;
    }

    protected static $_actionRoot = array();

    /**
     * List all available functions insde specific folder.
     */ 
    public static function listAll($folder = null){
        self::$_actionRoot = array('prefix' => 'Task_', 'path' => APPLICATION_PATH . '/tasks/');
        $results = array();
        
        if(! $folder)
            $folder = realpath(self::$_actionRoot['path']);
            
        $classFiles = Shaw_Core::scandirr($folder, array('php'));
        
        foreach($classFiles as $classFile){
            // e.g. transform Load/Data.php to Load_Data
            $classFile = str_replace('.php', null, $classFile);
            $taskParts = explode('/', $classFile);
            $taskClass = self::$_actionRoot['prefix'] . join('_',$taskParts);
            $taskShort = strtolower(join('-',$taskParts));
            
            
            
            if(class_exists($taskClass)){ // Autoloader called here :/
                $class = new ReflectionClass($taskClass);
                if(! $class->isAbstract()){
                    $task = new $taskClass();
                    if($task instanceof Shaw_Task)
                        $results[$taskShort] = $task;
                    else
                        Shaw_Log::debug("$taskShort does not implement Shaw_Task, thats bad !");
                }
            }
            else
                Shaw_Log::debug("$taskShort does not exists but files here !");
        }
        
        return $results;
    }

    /**
     * Returns TRUE if the specified task name is valid, or FALSE otherwise
     * 
     * @param string $taskName
     * @return bool
     */
    protected static function validateTaskName($taskName)
    {
        /*
         * This follows the _apparent_ naming convention.  The key thing is to prevent the use of characters that would
         * break a command string - we definitely can't allow spaces, for example.
         */
        return (bool) preg_match('/^[a-z0-9][a-z0-9\-]*$/', $taskName);
    }

    /**
     * Sets the name of the task, the name that's used to invoke it through a CLI
     *
     * @param string $taskName
     * @throws InvalidArgumentException If the task name is invalid
     */
    protected function setTaskName($taskName)
    {
        if (! self::validateTaskName($taskName)) {
            throw new InvalidArgumentException(
                sprintf('The task name "%s", in %s, is invalid', $taskName, get_class($this))
            );
        }

        $this->taskName = $taskName;
    }

    /**
     * getTaskName
     *
     * @return string $taskName
     */
    public function getTaskName()
    {
        return $this->taskName;
    }
    
    // TODO should be added to toolset
    protected function executeCommand($command){
        return Shaw_Core::executeCommand($command);
    }
    
    /**
     * getDescription
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * getRequiredArguments
     *
     * @return array $requiredArguments
     */
    public function getRequiredArguments()
    {
        return array_keys($this->requiredArguments);
    }
    
    public function getMetrics()
    {
        return array_keys($this->metrics);
    }
    
	public function getMetricsDescriptions()
    {
        return $this->metrics;
    }
    
    private $_metricsCount = array();
    
    public function one($name, $count = 1)
    {
    	if(! array_key_exists($name, $this->metrics)){
    		throw new Exception('No such metric : ' . $name);
    	}
    	$this->_metricsCount[$name] += $count;
    }
	
    public function showMetrics()
    {
    	foreach($this->getMetricsDescriptions() as $name => $desc){
    		if(! isset($this->_metricsCount[$name])){
    			continue;
    		}
    		Shaw_Log::info($desc, $this->_metricsCount[$name]);
    	}
    }
    
    /**
     * getOptionalArguments
     *
     * @return array $optionalArguments
     */
    public function getOptionalArguments()
    {
        return array_keys($this->optionalArguments);
    }

    /**
     * getRequiredArgumentsDescriptions
     *
     * @return array $requiredArgumentsDescriptions
     */
    public function getRequiredArgumentsDescriptions()
    {
        return $this->requiredArguments;
    }

    /**
     * getOptionalArgumentsDescriptions
     *
     * @return array $optionalArgumentsDescriptions
     */
    public function getOptionalArgumentsDescriptions()
    {
        return $this->optionalArguments;
    }
}
