<?php

/**
 * Read phtml files as emails.
 *
 * Implements the variation between html, txt and subject :)
 *
 */
class Shaw_Email_View 
	extends Zend_View
{
	/**
	* Constructor
	* 
	* @param  array $config
	* @return void
	*/
	public function __construct($config = array())
	{
		$this->setUseStreamWrapper(true);
		$this->addHelperPath(LIBRARY_PATH.'/Shaw/View/Helper', 'Shaw_View_Helper');
		
		parent::__construct($config);
	}
	
	/**
	* Accesses a helper object from within a script.
	*
	* If the helper class has a 'view' property, sets it with the current view
	* object.
	*
	* @param string $name The helper name.
	* @param array $args The parameters for the helper.
	* @return string The result of the helper output.
	*/
	public function __call($name, $args)
	{
		if(in_array($name, array('setMetadata', 'getRenderMode'))){
			return call_user_func_array(
				array($this, $name),
				$args
			);
		}
		
		// is the helper already loaded?
		$helper = $this->getHelper($name);
	
		// call the helper method
		return call_user_func_array(
		array($helper, $name),
		$args
		);
	}
	
    protected $_metadata;
    
    public function setMetadata(array $metadata)
    {
    	$this->_metadata = $metadata;
    }
    
    public function getMetadata()
    {
    	// Render d'abord !
    	return $this->_metadata;
    }
    
	public function getRenderMode()
    {
    	return $this->_renderMode;
    }
    
    protected $_renderMode = 'html';
    
    public function setRenderMode($renderMode)
    {
    	$this->_renderMode = $renderMode;
    	return $this;
    }
    
    public function render($name, $renderMode = null)
    {
    	$this->setRenderMode($renderMode);
    	$result = parent::render($name);
    	$this->setRenderMode(null);
    	return $result;
    }
}