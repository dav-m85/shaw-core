<?php

class Shaw_Email
	extends Zend_Mail
{
	protected $_baseUrl = null;
	
	protected $_filterPaths = null;
	
	protected $_helperPaths = array();
	
	protected $_layout = 'default'; // TODO move default conf elsewhere
	
	protected $_layoutPath = null;
	
	/**
	 * Hold Email configuration values. Nothing to do with view vars :)
	 * @var array
	 */
	protected $_options = array();
	
	protected $_reservedKeys = array('layout', 'script', 'baseurl');
	
	protected $_script = null;
	
	protected $_scriptPath = null;
	
	/**
	* Strict variables flag; when on, undefined variables accessed in the view
	* scripts will trigger notices
	* @var boolean
	*/
	private $_strictVars = false;
	
	/**
	* Constructor.
	*/
	// @todo Ca devrait être implémenté différemment
	public function __construct($script = null, $options = null, $charset = 'utf-8')
	{
		$this->setViewPath(APPLICATION_PATH . '/emails');
		$this->addFilterPath('Shaw/View/Filter', 'Shaw_View_Filter_');
		$this->_script = $script;
		
		$this->_baseUrl = null;
		
		if(is_array($options))
			$this->setOptions($options);
	
		$this->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
	
		parent::__construct($charset);
		
		$this->_init();
	}
	
	/**
	* Prevent E_NOTICE for nonexistent values
	*
	* If {@link strictVars()} is on, raises a notice.
	*
	* @param  string $key
	* @return null
	*/
	public function __get($key)
	{
		if ($this->_strictVars) {
			trigger_error('Key "' . $key . '" does not exist', E_USER_NOTICE);
		}
	
		return null;
	}
	
	protected function _init()
	{}
	
	/**
	 * Allows testing with empty() and isset() to work inside
	 * templates.
	 *
	 * @param  string $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		if (! $this->_isReserved($key)) {
			return isset($this->$key);
		}
	
		return false;
	}
	
	/**
	 * Directly assigns a variable to the view script.
	 *
	 * Checks first to ensure that the caller is not attempting to set a
	 * protected or private member (by checking for a prefixed underscore); if
	 * not, the public member is set; otherwise, an exception is raised.
	 *
	 * @param string $key The variable name.
	 * @param mixed $val The variable value.
	 * @return void
	 * @throws Zend_View_Exception if an attempt to set a private or protected
	 * member is detected
	 */
	public function __set($key, $val)
	{
		if (! $this->_isReserved($key)) {
			$this->$key = $val;
			return;
		}
	
		require_once 'Zend/View/Exception.php';
		$e = new Zend_View_Exception('Setting private, protected or reserved class members is not allowed');
		$e->setView($this);
		throw $e;
	}
	
	/**
	 * Allows unset() on object properties to work
	 *
	 * @param string $key
	 * @return void
	 */
	public function __unset($key)
	{
		if (! $this->_isReserved($key) && isset($this->$key)) {
			unset($this->$key);
		}
	}
	
	private function _isReserved($key)
	{
		return ('_' == substr($key, 0, 1) 
			|| in_array($key, $this->_reservedKeys)
			|| $key == 'options');
	}
	
	public function addHelperPath($path, $classPrefix) // TODO dans les options !!!!
	{
		$this->_helperPaths[$classPrefix] = $path;
	}
	
	public function addFilterPath($path, $classPrefix) // TODO dans les options !!!!
	{
		$this->_filterPaths[$classPrefix] = $path;
	}
	
	public function addOption($key, $value){
		$this->addOptions(array($key => $value));
		return $this;
	}
	
	public function addOptions(array $options){
		$this->_options = array_merge($this->_options, $options);
		return $this;
	}
	
	/**
	* Assigns variables to the view script via differing strategies.
	*
	* Zend_View::assign('name', $value) assigns a variable called 'name'
	* with the corresponding $value.
	*
	* Zend_View::assign($array) assigns the array keys as variable
	* names (with the corresponding array values).
	*
	* @see    __set()
	* @param  string|array The assignment strategy to use.
	* @param  mixed (Optional) If assigning a named variable, use this
	* as the value.
	* @return Shaw_Email Fluent interface
	* @throws Exception if $spec is neither a string nor an array,
	* or if an attempt to set a private or protected member is detected
	*/
	public function assign($spec, $value = null)
	{
		// which strategy to use?
		if (is_string($spec)) {
			// assign by name and value
			if ($this->_isReserved($key)) {
				$e = new Exception('Setting private, protected or reserved class members is not allowed');
				throw $e;
			}
			$this->$spec = $value;
		} elseif (is_array($spec)) {
			// assign from associative array
			$error = false;
			foreach ($spec as $key => $val) {
				if ($this->_isReserved($key)) {
					$error = true;
					break;
				}
				$this->$key = $val;
			}
			if ($error) {
				$e = new Exception('Setting private, protected or reserved class members is not allowed');
				throw $e;
			}
		} else {
			$e = new Exception('assign() expects a string or array, received ' . gettype($spec));
			throw $e;
		}
	
		return $this;
	}
	
	public function attachImage($url, $name)
	{
		$at = $this->createAttachment(file_get_contents(Shaw_Core::absolutePath($url)));
		$at->type        = 'image/jpeg';
		$at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
		$at->encoding    = Zend_Mime::ENCODING_BASE64;
		$at->filename    = $name;
		return $this;
	}
	
	public function attachPdf($binaryString, $name)
	{
		$at = $this->createAttachment($binaryString);
		$at->type        = 'application/pdf';
		$at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
		$at->encoding    = Zend_Mime::ENCODING_BASE64;
		$at->filename    = $name;
		return $this;
	}
	
	public function getLayout()
	{
		return $this->_layout;
	}
	
	/**
	 * Set the layout script's name to be used.
	 * @param string $script
	 * @return Shaw_Email Fluent interface.
	 */
	public function setLayout($layout)
	{
		$this->_layout = $layout;
		return $this;
	}
	
	/**
	* Executes immediately before Zend_Mail::send().
	*/
	public function preSend()
	{		
	}
	
	/**
	 * Executes immediately after Zend_Mail::send().
	 */
	public function postSend()
	{}
	
	public function setScriptPath($path)
	{
		$this->_scriptPath = $path;
		return $this;
	}
	
	public function getScriptPath()
	{
		return $this->_scriptPath;
	}
	
	public function setLayoutPath($path)
	{
		$this->_layoutPath = $path;
		return $this;
	}
	
	public function getLayoutPath()
	{
		return $this->_layoutPath;
	}
	
	/**
	 * Set object state from options array
	 *
	 * @param  array $options
	 * @return Zend_Form_Element
	 */
	public function setOptions(array $options)
	{
		/* // Not set for this one
		 if (isset($options['prefixPath'])) {
		$this->addPrefixPaths($options['prefixPath']);
		unset($options['prefixPath']);
		}
		*/
		unset($options['options']);
		unset($options['config']);
	
		foreach ($options as $key => $value) {
			$method = 'set' . ucfirst($key);
				
			// These shall be objects...
			/*
			if (in_array($method, array('setTranslator', 'setPluginLoader', 'setView'))) {
				if (! is_object($value)) {
					continue;
				}
			}
			*/
			
			// Look for setters
			if (method_exists($this, $method)) {
				$this->$method($value); // Setter exists; use it
			} else {
				$this->$key = $value;  // Assume it's a view var
			}
		}
		return $this;
	}
	
	/**
	* Set the script's name to be used.
	* @param string $script
	* @return Shaw_Email Fluent interface.
	*/
	public function setScript($script)
	{
		$this->_script = $script;
		return $this;
	}
	
	public function getOption($key)
	{
		return $this->_options[$key];
	}
	
	public function getOptions()
	{
		// include all reserved keywords !
		foreach($this->_reservedKeys as $key){
			$method = 'get' . ucfirst($key);
			if (method_exists($this, $method))
				$this->_options[$key] = $this->$method(); // Getter exists; use it
		}
		return $this->_options;
	}
	
	public function getScript()
	{
		return $this->_script;
	}
	
	// TODO
	public function renderSubject(){
		//throw new Exception('arf');		
	/*	$layout = new Shaw_Email_Layout();
		$layout->setView(new Shaw_Email_View());
		$layout->setLayoutPath($this->getLayoutPath());
		$layout->getView()->renderingMode = "subject";
		$subject = trim($layout->getView()->render($this->_templateScript.'.phtml'));
		unset($layout->getView()->renderingMode);
		
		$this->getView()->renderingMode = "subject";
		$subject = trim($this->getView()->render($this->_templateScript.'.phtml'));
		unset($this->getView()->renderingMode);
		return $subject;
		*/		
	}
	
	public function send($transport = null)
	{		
		$this->preSend();		
		parent::send($transport);
		$this->postSend();
		return $this; // Fluent interface.
	}
	
	public function setBodyHtml()
	{
		throw new Exception('Not supported with Shaw_Email');
	}
	
	public function setBodyText()
	{
		throw new Exception('Not supported with Shaw_Email');
	}
	
	public function setBaseUrl($url)
	{
		$this->_baseUrl = $url;
		return $this;
	}
	
	public function getBaseUrl()
	{
		return $this->_baseUrl;
	}
	
	public function getBodyHtml($htmlOnly = false)
	{
		if($this->_bodyHtml === false){ // generate body
			$vars = $this->getVars();
			
			$layout = new Shaw_Email_Layout();
			$layout->setView(new Shaw_Email_View());
			$layout->setLayoutPath($this->getLayoutPath());
			
			$view = new Shaw_Email_View();
			$view->setScriptPath($this->getScriptPath());
			
			foreach($this->_helperPaths as $classPrefix => $path){
				$view->addHelperPath($path, $classPrefix);
				$layout->getView()->addHelperPath($path, $classPrefix);
			}
			
			foreach($this->_filterPaths as $classPrefix => $path){
				$view->addFilterPath($path, $classPrefix);
				$layout->getView()->addFilterPath($path, $classPrefix);
			}
			
			// @todo Find this piece of code man !
			$view->addFilter('Inliner');
			$layout->getView()->addFilter('Inliner');
			
			$view->assign($vars);
			$result = $view->render($this->getScript() . '.phtml', 'html');
			
			$layout->getView()->assign($vars);
			$layout->assign("content", trim($result));
			
			parent::setBodyHtml($layout->render($this->getLayout(), 'html'));
		}
		
		return parent::getBodyHtml($htmlOnly);
	}
	
	
	/**
	* Retrieve element attribute
	*
	* @param  string $name
	* @return string
	*/
	public function getVar($name)
	{
		$name = (string) $name;
		if (isset($this->$name)) {
			return $this->$name;
		}
	
		return null;
	}
	
    /**
    * Return list of all assigned variables
    *
    * Returns all public properties of the object. Reflection is not used
    * here as testing reflection properties for visibility is buggy.
    * 
    * Adds also all internal options to the "options" var.
    *
    * @return array
    */
    public function getVars()
    {
    	$vars   = get_object_vars($this);
    	foreach ($vars as $key => $value) {
    		if ($this->_isReserved($key)) {
    			unset($vars[$key]);
    		}
    	}
    	
    	unset($vars['hasAttachments']);
    	
    	// Add reserved keywords :)
    	$vars = array_merge($vars, $this->getOptions());
    	
    	return $vars;
    }
    
	public function getBodyText($textOnly = false)
	{
		if($this->_bodyText === null){ // generate text
			
			parent::setBodyText(null);
		}
		return parent::getBodyText($textOnly);
	}
	/*
	public function setSubject($subject)
	{
		
	}
	*/
	/**
	* Set email variables
	*
	* @param  string $name
	* @param  mixed $value
	* @return Zend_Form_Element
	* @throws Zend_Form_Exception for invalid $name values
	*/
	public function setVar($name, $value)
	{
		$name = (string) $name;
		if (null === $value) {
			unset($this->$name);
		} else {
			$this->$name = $value;
		}
	
		return $this;
	}
	
	/**
	 * Set multiple vars at once
	 *
	 * @param  array $attribs
	 * @return Zend_Form_Element
	 */
	public function setVars(array $vars)
	{
		foreach ($vars as $key => $value) {
			$this->setVar($key, $value);
		}
	
		return $this;
	}
	
	/**
	 * Set both scriptPath and layoutPath.
	 *
	 * Uses the classic folder structure emails/layouts and emails/scripts
	 * @param string $path
	 */
	public function setViewPath($path)
	{
		$this->setLayoutPath($path . '/layouts');
		$this->setScriptPath($path . '/scripts');
		$this->addHelperPath($path . '/helpers', 'Email_Helper_');
		$this->addFilterPath($path . '/filters', 'Email_Filter_');
		return $this;
	}
}