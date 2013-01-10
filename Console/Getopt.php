<?php

class Shaw_Console_Getopt
{

    /**
     * The options for a given application can be in multiple formats.
     * modeGnu is for traditional 'ab:c:' style getopt format.
     * modeZend is for a more structured format.
     */
    const MODE_ZEND                         = 'zend';
    const MODE_GNU                          = 'gnu';

    /**
     * Constant tokens for various symbols used in the mode_zend
     * rule format.
     */
    const PARAM_REQUIRED                    = '=';
    const PARAM_OPTIONAL                    = '-';
    const TYPE_STRING                       = 's';
    const TYPE_WORD                         = 'w';
    const TYPE_INTEGER                      = 'i';

    /**
     * These are constants for optional behavior of this class.
     * ruleMode is either 'zend' or 'gnu' or a user-defined mode.
     * dashDash is true if '--' signifies the end of command-line options.
     * ignoreCase is true if '--opt' and '--OPT' are implicitly synonyms.
     * parseAll is true if all options on the command line should be parsed, regardless of
     * whether an argument appears before them.
     */
    const CONFIG_RULEMODE                   = 'ruleMode';
    const CONFIG_DASHDASH                   = 'dashDash';
    const CONFIG_IGNORECASE                 = 'ignoreCase';
    const CONFIG_PARSEALL                   = 'parseAll';
    const CONFIG_IGNOREUNKNOWN              = 'ignoreUnknown';
    const CONFIG_PARAMETER_SEPARATOR        = 'paramSeparator'; // added
    const CONFIG_CUMULATIVE_PARAMETERS      = 'cumulativeParameters'; // added
    const CONFIG_CUMULATIVE_FLAGS           = 'cumulativeFlags'; // added
    /**
     * Defaults for getopt configuration are:
     * ruleMode is 'zend' format,
     * dashDash (--) token is enabled,
     * ignoreCase is not enabled,
     * parseAll is enabled.
     */
    protected $_getoptConfig = array(
        self::CONFIG_RULEMODE   => self::MODE_ZEND,
        self::CONFIG_DASHDASH   => true,
        self::CONFIG_IGNORECASE => false,
        self::CONFIG_PARSEALL   => true,
        self::CONFIG_IGNOREUNKNOWN    => true,
        self::CONFIG_PARAMETER_SEPARATOR => ',',
        self::CONFIG_CUMULATIVE_FLAGS => false
    );

    /**
     * Stores the command-line arguments for the calling applicaion.
     *
     * @var array
     */
    protected $_argv = array();

    /**
     * Stores the name of the calling applicaion.
     *
     * @var string
     */
    protected $_progname = '';

    /**
     * Stores the list of legal options for this application.
     *
     * @var array
     */
    protected $_rules = array();

    /**
     * Stores alternate spellings of legal options.
     *
     * @var array
     */
    protected $_ruleMap = array();

    /**
     * Stores options given by the user in the current invocation
     * of the application, as well as parameters given in options.
     *
     * @var array
     */
    public $_options = array();
    
    /**
     * Stores the command-line arguments other than options.
     *
     * @var array
     */
    protected $_remainingArgs = array();

    /**
     * State of the options: parsed or not yet parsed?
     *
     * @var boolean
     */
    protected $_parsed = false;

    /**
     * The constructor takes one to three parameters.
     *
     * The first parameter is $rules, which may be a string for
     * gnu-style format, or a structured array for Zend-style format.
     *
     * The second parameter is $argv, and it is optional.  If not
     * specified, $argv is inferred from the global argv. If provided,
     * GetOpt will retrieve arguments through it. You can specify another
     * GetOpt instance here, for chaining purpose.
     *
     * The third parameter is an array of configuration parameters
     * to control the behavior of this instance of Getopt; it is optional.
     *
     * @param  array $rules
     * @param  array|Shaw_Console_Getopt $argv
     * @param  array $getoptConfig
     * @return void
     */
    public function __construct($rules, $argv = null, $getoptConfig = array())
    {
        if (!isset($_SERVER['argv'])) {
            if (ini_get('register_argc_argv') == false) {
                throw new Exception("argv is not available, because ini option 'register_argc_argv' is set Off");
            } else {
                throw new Exception('$_SERVER["argv"] is not set, but Shaw_Console_Getopt cannot work without this information.');
            }
        }
        
        $this->_progname = $_SERVER['argv'][0];
        $this->setOptions($getoptConfig);
        $this->addRules($rules);
        
        if ($argv instanceof Shaw_Console_Getopt){
            $argv = $argv->getRemainingArgs();
        }
        else if (!is_array($argv)) {
            $argv = array_slice($_SERVER['argv'], 1);
        }
        
        if (isset($argv)) {
            $this->setArguments((array)$argv);
        }
    }

    /**
     * Return the state of the option seen on the command line of the
     * current application invocation.  This function returns true, or the
     * parameter to the option, if any.  If the option was not given,
     * this function returns null.
     *
     * The magic __get method works in the context of naming the option
     * as a virtual member of this class.
     *
     * @param  string $key
     * @return string
     */
    public function __get($key)
    {
        return $this->getOption($key);
    }

    /**
     * Test whether a given option has been seen.
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset($key)
    {
        $this->parse();
        if (isset($this->_ruleMap[$key])) {
            $key = $this->_ruleMap[$key];
            return isset($this->_options[$key]);
        }
        return false;
    }

    /**
     * Set the value for a given option.
     *
     * @param  string $key
     * @param  string $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->parse();
        if (isset($this->_ruleMap[$key])) {
            $key = $this->_ruleMap[$key];
            $this->_options[$key] = $value;
        }
    }

    /**
     * Return the current set of options and parameters seen as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Unset an option.
     *
     * @param  string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->parse();
        if (isset($this->_ruleMap[$key])) {
            $key = $this->_ruleMap[$key];
            unset($this->_options[$key]);
        }
    }

    /**
     * Define additional command-line arguments.
     * These are appended to those defined when the constructor was called.
     *
     * @param  array $argv
     * @throws Zend_Console_Getopt_Exception When not given an array as parameter
     * @return Zend_Console_Getopt Provides a fluent interface
     */
    public function addArguments($argv)
    {
        if(!is_array($argv)) {
            require_once 'Zend/Console/Getopt/Exception.php';
            throw new Zend_Console_Getopt_Exception(
                "Parameter #1 to addArguments should be an array");
        }
        $this->_argv = array_merge($this->_argv, $argv);
        $this->_parsed = false;
        return $this;
    }

    /**
     * Define full set of command-line arguments.
     * These replace any currently defined.
     *
     * @param  array $argv
     * @throws Zend_Console_Getopt_Exception When not given an array as parameter
     * @return Zend_Console_Getopt Provides a fluent interface
     */
    public function setArguments($argv)
    {
        if(!is_array($argv)) {
            require_once 'Zend/Console/Getopt/Exception.php';
            throw new Zend_Console_Getopt_Exception(
                "Parameter #1 to setArguments should be an array");
        }
        $this->_argv = $argv;
        $this->_parsed = false;
        return $this;
    }

    /**
     * Define multiple configuration options from an associative array.
     * These are not program options, but properties to configure
     * the behavior of Zend_Console_Getopt.
     *
     * @param  array $getoptConfig
     * @return Zend_Console_Getopt Provides a fluent interface
     */
    public function setOptions($getoptConfig)
    {
        if (isset($getoptConfig)) {
            foreach ($getoptConfig as $key => $value) {
                $this->setOption($key, $value);
            }
        }
        return $this;
    }

    /**
     * Define one configuration option as a key/value pair.
     * These are not program options, but properties to configure
     * the behavior of Zend_Console_Getopt.
     *
     * @param  string $configKey
     * @param  string $configValue
     * @return Zend_Console_Getopt Provides a fluent interface
     */
    public function setOption($configKey, $configValue)
    {
        if ($configKey !== null) {
            $this->_getoptConfig[$configKey] = $configValue;
        }
        return $this;
    }

    /**
     * Define additional option rules.
     * These are appended to the rules defined when the constructor was called.
     *
     * @param  array $rules
     * @return Zend_Console_Getopt Provides a fluent interface
     */
    public function addRules($rules)
    {
        $ruleMode = $this->_getoptConfig['ruleMode'];
        switch ($this->_getoptConfig['ruleMode']) {
            case self::MODE_ZEND:
                if (is_array($rules)) {
                    $this->_addRulesModeZend($rules);
                    break;
                }
                // intentional fallthrough
            case self::MODE_GNU:
                $this->_addRulesModeGnu($rules);
                break;
            default:
                /**
                 * Call addRulesModeFoo() for ruleMode 'foo'.
                 * The developer should subclass Getopt and
                 * provide this method.
                 */
                $method = '_addRulesMode' . ucfirst($ruleMode);
                $this->$method($rules);
        }
        $this->_parsed = false;
        return $this;
    }

    /**
     * Return the current set of options and parameters seen as a string.
     *
     * @return string
     */
    public function toString()
    {
        $this->parse();
        $s = array();
        foreach ($this->_options as $flag => $value) {
            $s[] = $flag . '=' . ($value === true ? 'true' : $value);
        }
        return implode(' ', $s);
    }

    /**
     * Return the current set of options and parameters seen
     * as an array of canonical options and parameters.
     *
     * Clusters have been expanded, and option aliases
     * have been mapped to their primary option names.
     *
     * @return array
     */
    public function toArray()
    {
        $this->parse();
        $s = array();
        foreach ($this->_options as $flag => $value) {
            $s[] = $flag;
            if ($value !== true) {
                $s[] = $value;
            }
        }
        return $s;
    }

    /**
     * Return the current set of options and parameters seen in Json format.
     *
     * @return string
     */
    public function toJson()
    {
        $this->parse();
        $j = array();
        foreach ($this->_options as $flag => $value) {
            $j['options'][] = array(
                'option' => array(
                    'flag' => $flag,
                    'parameter' => $value
                )
            );
        }

        /**
         * @see Zend_Json
         */
        require_once 'Zend/Json.php';
        $json = Zend_Json::encode($j);

        return $json;
    }

    /**
     * Return the current set of options and parameters seen in XML format.
     *
     * @return string
     */
    public function toXml()
    {
        $this->parse();
        $doc = new DomDocument('1.0', 'utf-8');
        $optionsNode = $doc->createElement('options');
        $doc->appendChild($optionsNode);
        foreach ($this->_options as $flag => $value) {
            $optionNode = $doc->createElement('option');
            $optionNode->setAttribute('flag', utf8_encode($flag));
            if ($value !== true) {
                $optionNode->setAttribute('parameter', utf8_encode($value));
            }
            $optionsNode->appendChild($optionNode);
        }
        $xml = $doc->saveXML();
        return $xml;
    }

    /**
     * Return a list of options that have been seen in the current argv.
     *
     * @return array
     */
    public function getOptions()
    {
        $this->parse();
        return array_keys($this->_options);
    }

    /**
     * Return the state of the option seen on the command line of the
     * current application invocation.
     *
     * This function returns true, or the parameter value to the option, if any.
     * If the option was not given, this function returns false.
     *
     * @param  string $flag
     * @return mixed
     */
    public function getOption($flag)
    {
        $this->parse();
        if ($this->_getoptConfig[self::CONFIG_IGNORECASE]) {
            $flag = strtolower($flag);
        }
        if (isset($this->_ruleMap[$flag])) {
            $flag = $this->_ruleMap[$flag];
            if (isset($this->_options[$flag])) {
                return $this->_options[$flag];
            }
        }
        return null;
    }

    /**
     * Return the arguments from the command-line following all options found.
     *
     * @return array
     */
    public function getRemainingArgs()
    {
        $this->parse();
        return $this->_remainingArgs;
    }

    /**
     * Return a useful option reference, formatted for display in an
     * error message.
     *
     * Note that this usage information is provided in most Exceptions
     * generated by this class.
     *
     * @return string
     */
    public function getUsageMessage()
    {
        $usage = "Usage: {$this->_progname} [ options ]\n";
        $maxLen = 20;
        $lines = array();
        foreach ($this->_rules as $rule) {
            $flags = array();
            if (is_array($rule['alias'])) {
                foreach ($rule['alias'] as $flag) {
                    $flags[] = (strlen($flag) == 1 ? '-' : '--') . $flag;
                }
            }
            $linepart['name'] = implode('|', $flags);
            if (isset($rule['param']) && $rule['param'] != 'none') {
                $linepart['name'] .= ' ';
                switch ($rule['param']) {
                    case 'optional':
                        $linepart['name'] .= "[ <{$rule['paramType']}> ]";
                        break;
                    case 'required':
                        $linepart['name'] .= "<{$rule['paramType']}>";
                        break;
                }
            }
            if (strlen($linepart['name']) > $maxLen) {
                $maxLen = strlen($linepart['name']);
            }
            $linepart['help'] = '';
            if (isset($rule['help'])) {
                $linepart['help'] .= $rule['help'];
            }
            $lines[] = $linepart;
        }
        foreach ($lines as $linepart) {
            $usage .= sprintf("%s %s\n",
            str_pad($linepart['name'], $maxLen),
            $linepart['help']);
        }
        return $usage;
    }

    /**
     * Define aliases for options.
     *
     * The parameter $aliasMap is an associative array
     * mapping option name (short or long) to an alias.
     *
     * @param  array $aliasMap
     * @throws Zend_Console_Getopt_Exception
     * @return Zend_Console_Getopt Provides a fluent interface
     */
    public function setAliases($aliasMap)
    {
        foreach ($aliasMap as $flag => $alias)
        {
            if ($this->_getoptConfig[self::CONFIG_IGNORECASE]) {
                $flag = strtolower($flag);
                $alias = strtolower($alias);
            }
            if (!isset($this->_ruleMap[$flag])) {
                continue;
            }
            $flag = $this->_ruleMap[$flag];
            if (isset($this->_rules[$alias]) || isset($this->_ruleMap[$alias])) {
                $o = (strlen($alias) == 1 ? '-' : '--') . $alias;
                require_once 'Zend/Console/Getopt/Exception.php';
                throw new Zend_Console_Getopt_Exception(
                    "Option \"$o\" is being defined more than once.");
            }
            $this->_rules[$flag]['alias'][] = $alias;
            $this->_ruleMap[$alias] = $flag;
        }
        return $this;
    }

    /**
     * Define help messages for options.
     *
     * The parameter $help_map is an associative array
     * mapping option name (short or long) to the help string.
     *
     * @param  array $helpMap
     * @return Zend_Console_Getopt Provides a fluent interface
     */
    public function setHelp($helpMap)
    {
        foreach ($helpMap as $flag => $help)
        {
            if (!isset($this->_ruleMap[$flag])) {
                continue;
            }
            $flag = $this->_ruleMap[$flag];
            $this->_rules[$flag]['help'] = $help;
        }
        return $this;
    }

    /**
     * Parse command-line arguments and find both long and short
     * options.
     *
     * Also find option parameters, and remaining arguments after
     * all options have been parsed.
     *
     * @return Zend_Console_Getopt|null Provides a fluent interface
     */
    public function parse()
    {
        if ($this->_parsed === true)
            return;
        
        $argv = $this->_argv; // Copy args !
        
        $this->_options = array();
        $this->_remainingArgs = array();
        
        while (count($argv) > 0) {
            // finishing dashdash
            if ($argv[0] == '--') { 
                array_shift($argv);
                if ($this->_getoptConfig[self::CONFIG_DASHDASH]) {
                    $this->_remainingArgs = array_merge($this->_remainingArgs, $argv);
                    break;
                }
            }
            
            // strip = if present
            if(($eqpos = strpos($argv[0],'=')) !== false){
                $flag = array_shift($argv);
                array_unshift($argv, substr($flag, $eqpos + 1));
                array_unshift($argv, substr($flag, 0, $eqpos));
            }
            
            $optLength = strspn($argv[0], '-');
            
            // sanitize more, but revert cluster order :/
            if($optLength == 1 && strlen($argv[0]) > 2){
                $flags = array_shift($argv);
                $flags = str_split(ltrim($flags, '-'));
                foreach($flags as $flag)
                    array_unshift($argv, '-' . $flag);
            }
            
            // options to parse !
            if( $optLength == 1 || $optLength == 2 ){
                $flag = ltrim($argv[0], '-');
                if ($this->_getoptConfig[self::CONFIG_IGNORECASE]) // Case.
                    $flag = strtolower($flag);
                
                // Rule exist for this flag !
                if (isset($this->_ruleMap[$flag])) {
                    array_shift($argv);
                    $this->_parseSingleOption($this->_ruleMap[$flag], $argv);
                }
                else {
                    if (! $this->_getoptConfig[self::CONFIG_IGNOREUNKNOWN])
                        throw new Exception("Option \"$flag\" is not recognized.");
                    else{
                        $this->_remainingArgs[] = array_shift($argv);
                    }
                }
            }
            // remainingArgs
            else if($this->_getoptConfig[self::CONFIG_PARSEALL]) {
                $this->_remainingArgs[] = array_shift($argv);
            }
            else {
                // We should put all other arguments in _remainingArgs and stop parsing since CONFIG_PARSEALL is false.
                $this->_remainingArgs = array_merge($this->_remainingArgs, $argv);
                break; // break while loop
            }
        }
        
        $this->_parsed = true;
        return $this;
    }
    
    //
    private function _parseSingleOption($realFlag, &$argv){
        switch ($this->_rules[$realFlag]['param']) {
            case 'required':
                if (count($argv) > 0) {
                    $param = array_shift($argv);
                    $this->_checkParameterType($realFlag, $param);
                } else 
                    throw new Exception("Option \"$realFlag\" requires a parameter.");
                
                break;
            case 'optional':
                if (count($argv) > 0 && substr($argv[0], 0, 1) != '-') {
                    $param = array_shift($argv);
                    $this->_checkParameterType($realFlag, $param);
                } else {
                    if($this->_getoptConfig[self::CONFIG_CUMULATIVE_FLAGS])
                        $this->_options[$realFlag]++;
                    else
                        $this->_options[$realFlag] = true;
                }
                break;
            default:
                if($this->_getoptConfig[self::CONFIG_CUMULATIVE_FLAGS])
                    $this->_options[$realFlag]++;
                else
                    $this->_options[$realFlag] = true;
        }
    }
    
    /**
     * Return true if the parameter is in a valid format for
     * the option $flag.
     * Throw an exception in most other cases.
     *
     * @param  string $flag
     * @param  string $param
     * @throws Zend_Console_Getopt_Exception
     * @return bool
     */
    protected function _checkParameterType($flag, $param){
        $type = 'string';
        if (isset($this->_rules[$flag]['paramType'])) {
            $type = $this->_rules[$flag]['paramType'];
        }
        
        $separator = $this->_getoptConfig[self::CONFIG_PARAMETER_SEPARATOR];
        
        switch ($type) {
            case 'word':
                
                if(! empty($separator)){
                    if (! preg_match('/^[\w\Q'.$separator.'\E]*$/', $param))
                        throw new Exception("Option \"$flag\" requires single-word parameters separated by \"$separator\", but was given \"$param\".");
                    $param = explode($separator, $param);
                    if(count($param) == 1)
                        $param = $param[0];
                }
                else
                    if (! preg_match('/^[\w]*$/', $param))
                        throw new Exception("Option \"$flag\" requires a single-word parameter, but was given \"$param\".");
                
                if($this->_getoptConfig[self::CONFIG_CUMULATIVE_PARAMETERS]
                   && !empty($this->_options[$flag]))
                    $this->_options[$flag] = array_merge((array)$this->_options[$flag], (array)$param);
                else
                    $this->_options[$flag] = $param;
                
                break;
            case 'integer':
                if(! empty($separator)){
                    if (! preg_match('/^[\d\Q'.$separator.'\E]*$/', $param))
                        throw new Exception("Option \"$flag\" requires integer parameters separated by \"$separator\", but was given \"$param\".");
                    $param = explode($separator, $param);
                    if(count($param) == 1)
                        $param = $param[0];
                }
                else
                    if (! preg_match('/^[\d]*$/', $param))
                        throw new Exception("Option \"$flag\" requires an integer parameter, but was given \"$param\".");
                
                if($this->_getoptConfig[self::CONFIG_CUMULATIVE_PARAMETERS]
                   && !empty($this->_options[$flag]))
                    $this->_options[$flag] = array_merge((array)$this->_options[$flag], (array)$param);
                else
                    $this->_options[$flag] = $param;
                
                break;
            case 'string':
            default:
                $this->_options[$flag] = $param;
                break;
        }
        return true;
    }

    /**
     * Define legal options using the gnu-style format.
     *
     * @param  string $rules
     * @return void
     */
    protected function _addRulesModeGnu($rules){
        $ruleArray = array();

        /**
         * Options may be single alphanumeric characters.
         * Options may have a ':' which indicates a required string parameter.
         * No long options or option aliases are supported in GNU style.
         */
        preg_match_all('/([a-zA-Z0-9]:?)/', $rules, $ruleArray);
        foreach ($ruleArray[1] as $rule) {
            $r = array();
            $flag = substr($rule, 0, 1);
            if ($this->_getoptConfig[self::CONFIG_IGNORECASE]) {
                $flag = strtolower($flag);
            }
            $r['alias'][] = $flag;
            if (substr($rule, 1, 1) == ':') {
                $r['param'] = 'required';
                $r['paramType'] = 'string';
            } else {
                $r['param'] = 'none';
            }
            $this->_rules[$flag] = $r;
            $this->_ruleMap[$flag] = $flag;
        }
    }

    /**
     * Define legal options using the Zend-style format.
     *
     * @param  array $rules
     * @throws Zend_Console_Getopt_Exception
     * @return void
     */
    protected function _addRulesModeZend(array $rules){
        foreach ($rules as $ruleCode => $helpMessage)
        {
            // this may have to translate the long parm type if there
            // are any complaints that =string will not work (even though that use
            // case is not documented)
            if (in_array(substr($ruleCode, -2, 1), array('-', '='))) {
                $flagList  = substr($ruleCode, 0, -2);
                $delimiter = substr($ruleCode, -2, 1);
                $paramType = substr($ruleCode, -1);
            } else {
                $flagList = $ruleCode;
                $delimiter = $paramType = null;
            }
            if ($this->_getoptConfig[self::CONFIG_IGNORECASE]) {
                $flagList = strtolower($flagList);
            }
            $flags = explode('|', $flagList);
            $rule = array();
            $mainFlag = $flags[0];
            foreach ($flags as $flag) {
                if (empty($flag)) {
                    require_once 'Zend/Console/Getopt/Exception.php';
                    throw new Zend_Console_Getopt_Exception(
                        "Blank flag not allowed in rule \"$ruleCode\".");
                }
                if (strlen($flag) == 1) {
                    if (isset($this->_ruleMap[$flag])) {
                        require_once 'Zend/Console/Getopt/Exception.php';
                        throw new Zend_Console_Getopt_Exception(
                            "Option \"-$flag\" is being defined more than once.");
                    }
                    $this->_ruleMap[$flag] = $mainFlag;
                    $rule['alias'][] = $flag;
                } else {
                    if (isset($this->_rules[$flag]) || isset($this->_ruleMap[$flag])) {
                        require_once 'Zend/Console/Getopt/Exception.php';
                        throw new Zend_Console_Getopt_Exception(
                            "Option \"--$flag\" is being defined more than once.");
                    }
                    $this->_ruleMap[$flag] = $mainFlag;
                    $rule['alias'][] = $flag;
                }
            }
            if (isset($delimiter)) {
                switch ($delimiter) {
                    case self::PARAM_REQUIRED:
                        $rule['param'] = 'required';
                        break;
                    case self::PARAM_OPTIONAL:
                    default:
                        $rule['param'] = 'optional';
                }
                switch (substr($paramType, 0, 1)) {
                    case self::TYPE_WORD:
                        $rule['paramType'] = 'word';
                        break;
                    case self::TYPE_INTEGER:
                        $rule['paramType'] = 'integer';
                        break;
                    case self::TYPE_STRING:
                    default:
                        $rule['paramType'] = 'string';
                }
            } else {
                $rule['param'] = 'none';
            }
            $rule['help'] = $helpMessage;
            $this->_rules[$mainFlag] = $rule;
        }
    }

}
