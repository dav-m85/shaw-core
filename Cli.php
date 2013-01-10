<?php

// TODO : Move this all in Shadokworks for sharing on davidmoreau.net
// Should underclass Zend_Application
// TODO : Bootstrap fails silently... should correct taht.

/** Zend_Application */
require_once 'Zend/Application.php';
require_once 'Zend/Console/Getopt.php';

class Shaw_Cli extends Zend_Application
{
    protected $_applicationOpt;
    
    protected $_initDone = false;
    
    protected $_getopt = null;
    
    protected $_task;
    
    protected $_actionRoot;
    
    protected $_verboseLevel = 6; // INFO
    
    protected $_color = true;
    
    /**
     * Constructor.
     *
     * @param array $options Options ?
     */ 
    // TODO -runhash : "use a specific unhash for this run (ignore records with this runhash");	
    /* TODO Ability to queue tasks, and deduplicate them when adding
    	$ta = new Shaw_Task();
    	$this->queue($ta, $allowDuplicate);
    	*/
    // les tasks devrait avoir un flag (executed)
    // le log devrait être redirigé
    public function __construct($options = null)
    {
        $this->_actionRoot = array('prefix' => 'Task_', 'path' => APPLICATION_PATH . '/tasks/');
        
        ini_set('html_errors', 0);
        
        try {
            require_once('Shaw/Console/Getopt.php');
            $this->_getopt = $getopt = new Shaw_Console_Getopt(array(
                'environment|e=s' => 'Environment of execution',
                'verbose|v-i' => 'Set verbose level. Default is INFO (6)',
                'color|c' => 'Color support',
            	'runhash|h=s' => 'RunHash to apply on this run (TBI)'
                // 'actionroot|a=s' => 'Set another action root. Current is '.self::$actionRoot;
            ));
            
            $getopt->parse();
            
            // Defined environnement before constructing application.
            if(!defined('APPLICATION_ENV')){
                if($env = getenv('APPLICATION_ENV')){
                    define('APPLICATION_ENV', $env);
                }
                else if($env = $getopt->getOption('environment')){
                    define('APPLICATION_ENV', $env);
                } else{
                    $env = 'production';
                    define('APPLICATION_ENV', $env);
                }
            }
            
            if($verboseLevel = $getopt->getOption('v') )
            {
                if(is_numeric($verboseLevel)
                    && $verboseLevel >= 0
                    && $verboseLevel <= 7) {
                    $this->_verboseLevel = $verboseLevel;
                }
                else{
                    throw new Exception('Verbose level must be between 0 (emergency) and 7 (debug)');
                }
            }
            $col = $getopt->getOption('c');
            if(isset($col)){
                $this->_color = true;
            }
            
            parent::__construct(APPLICATION_ENV, $options);
            
            // Get args for action configuration
            $this->_initDone = true;
        }
        catch (Zend_Console_Getopt_Exception $e) {
            echo "Some parameters are missing or wrong.\n";
            echo $e->getMessage()."\n\n";
        }
        catch(Zend_Application_Exception $e){
            echo "Cannot instantiate application.\n";
            echo $e->getMessage()."\n\n";
        }
        catch(Exception $e){
            echo "Cannot run.\n";
            echo $e->getMessage();
        }
    }
    
    //
    private function getSyntax(){
        return $syntax =
        'Frontend CLI' . PHP_EOL
        . 'php task.php command [-e <environnement>] [-v verboselevel] [args]';
    }
    
    /**
     * Describe a specific task.
     */ 
    public function describeAction(){}
    
    /**
     * List all available tasks.
     */ 
    public function listAction(){
        
        $tasks = Shaw_Task::listAll();
        
        $color = new Shaw_Cli_Colors();
        
        $arg = $this->_getopt->getRemainingArgs();
        $arf = $arg[1];
        if($arf == null){
        	$max = 0;
        	// register longest name for beautiful indentation later
            foreach($tasks as $short => $task){
            	if($max < strlen($short)){
            		$max = strlen($short);
            	}
            }
            // .. which happens here :)
            foreach($tasks as $short => $task){
            	$desc = $task->getDescription();
            	$desc = str_replace('[OBSOLETE]', $color->paint('[OBSOLETE]', 'yellow'), $desc);
            	$desc = str_replace('[TODO]', $color->paint('[TODO]', 'red'), $desc);
            	echo $color->paint(str_pad($short, $max + 4), 'light_green') . $desc . "\n";
            }
            
        }
        else{
            if(in_array($arf, array_keys($tasks))){
                echo $arf . "\t" . $tasks[$arf]->getDescription() . "\n";
                echo "Required arguments : \n";
                foreach($tasks[$arf]->getRequiredArgumentsDescriptions() as $var => $desc){
                    echo "\t" . $var . " : " . $desc . "\n";
                }
                echo "Optionnal arguments : \n";
                foreach($tasks[$arf]->getOptionalArgumentsDescriptions() as $var => $desc){
                    echo "\t" . $var . " : " . $desc . "\n";
                }
            }
            else{
                Shaw_Log::error("Unknown task $arf");
            }
        }
    }
    
    /**
     * Display help.
     */ 
    public function helpAction(){
        echo $this->getSyntax();
    }
    
    /**
     * Does actually run the cli.
     */ 
    public function run()
    {
        if(! $this->_initDone) return 1;
        
        // %priorityName% %caller% %message%
        Shaw_Log::getInstance()->removeAllWriters();
        
        // Setting up logger.
        $consoleWriter = new Shaw_Log_Writer_Echo();
        if($this->_color){
            $formatter = new Shaw_Log_Formatter_Color('%priorityName% %caller% %message%');
        }
        else{
            $formatter = new Zend_Log_Formatter_Simple('%priorityName% %caller% %message%');
        }
        $consoleWriter->setFormatter($formatter);
        Shaw_Log::getInstance()->addWriter($consoleWriter);
        
        $fileWriter = new Zend_Log_Writer_Stream(APPLICATION_PATH . "/../var/log/task.log", 'a');
        Shaw_Log::getInstance()->addWriter($fileWriter);
        
        $filter = new Zend_Log_Filter_Priority((int)$this->_verboseLevel);
        Shaw_Log::getInstance()->addFilter($filter);
        
        $exitStatus = true;
        
        // Get action to run, CLI check options.
        if($action = array_shift($this->_getopt->getRemainingArgs())){
            
            switch($action){
                case 'help':
                case 'h':
                    $this->helpAction();
                    break;
                case 'run':
                case 'ru':
                    $exitStatus = $this->runAction();
                    break;
                case 'li':
                case 'list':
                    $this->listAction();
                    break;
                default:
                    echo 'Unkown action' . PHP_EOL;
                    break;
            }
        }
        else
            echo 'Please specify action' . PHP_EOL;
        
        if($exitStatus)
            return 0;
        
        return 1;
    }
    
    /**
     * Run a specifc task.
     */ 
    public function runAction()
    {
        $foo = $this->_getopt->getRemainingArgs();
        
        $foo = $foo[1];
        
        if(empty($foo))
            throw new Exception('Please specify a task to run');
        
        // e.g. transform load-data to Load_Data
        $taskClassname = explode('-', $foo);
        $taskClassname = array_map('ucfirst', $taskClassname);
        $taskClassname = join('_',$taskClassname);
        
        $taskClassname = $this->_actionRoot['prefix'] . $taskClassname;
        
        $task = new $taskClassname($this);
        
        // Fetch parameters
        $getopt = new Shaw_Console_Getopt(array());
        $getopt->setArguments($this->_getopt->getRemainingArgs());
        
        foreach($task->getRequiredArgumentsDescriptions() as $key=>$value){
            $getopt->addRules(array($key.'=s' => $value));
        }
            
            
        foreach($task->getOptionalArgumentsDescriptions() as $key=>$value){
            $getopt->addRules(array($key.'-s' => $value));
        }
        
        try{
            $getopt->parse();
        }
        catch (Exception $e) {
            echo "Some task parameters are missing or wrong.\n";
            return;
        }
        
        foreach($task->getRequiredArguments() as $key)
            $task->addArgument($key, $getopt->getOption($key));
        
        foreach($task->getOptionalArguments() as $key)
            if($getopt->getOption($key))
                $task->addArgument($key, $getopt->getOption($key));
        
        // Execute task
        if($task->validate()){
            try{
                $old = microtime(true) * 1000;

                $task->runHash = substr(md5(time()), 0, 5);
                // echo 'runHash: '.$task->runHash . "\n";
                // Prerun here !
                
                Shaw_Log::info('Running %s (mem %s, time %s) at %s', 
                    $foo,
                    ini_get('memory_limit'),
                    Shaw_Core::format_microtime(ini_get('max_execution_time') * 1000),
                    Shaw_DateTime::now()->format('Y-m-d H:i:s')
                );
                
                //echo "memory_limit=".ini_get("memory_limit")."\n";
                
                //ini_set(, '512M');
                //set_time_limit(3600 * 2); // 2H
                
                $task->preExecute();
                $task->execute();
                $task->postExecute();
                
                $metrics = $task->getMetrics();
                if(! empty($metrics)){
                	$task->showMetrics();
                }
                
                $delta = microtime(true) * 1000 - $old;
                Shaw_Log::info('Executed in ' . Shaw_Core::format_microtime($delta) . PHP_EOL); // This PHP_EOL is ugly
                
                return true;
            }
            catch(Exception $e){
                Shaw_Log::error($e);
                Shaw_Log::notice('Aborted after '.Shaw_Core::format_microtime($delta) . PHP_EOL);
                return false;
            }
        }
        else{
            Shaw_Log::error(sprintf('"%s" missing required parameters.',$task->getTaskName()),'ERROR');
            return false;
        }
    }
}