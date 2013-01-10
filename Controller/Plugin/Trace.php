<?php
/**
 * Display various debugging purpose informations, highly configurable.
 * 
 * If logging enabled, add a listener to it for itself.
 * 
 * In application config, setup thanks to :
 * shaw.trace.enabled  = bool  : enable/disable trace
 * // shaw.trace.display = bool  : show/hide trace, maintain some hidden functionnalities (not implemented)
 * shaw.trace.sections = list : activates specific sections of the trace, in the specified order
 */
require_once 'Zend/Loader/PluginLoader.php';

class Shaw_Controller_Plugin_Trace 
	extends Zend_Controller_Plugin_Abstract
{
    protected $_plugins = array();

    protected $_pluginLoader = null;
    
    /**
     * @return Zend_Loader_PluginLoader
     */
    private function _getPluginLoader()
    {
    	return $this->_pluginLoader;
    }
    
	/**
     * Retrieve a plugin object
     *
     * @param  string $name
     * @return Shaw_Controller_Plugin_Trace_Abstract
     */
    private function _getPlugin($name)
    {
        if (! isset($this->_plugins[$name])) {
            $class = $this->_getPluginLoader()->load($name);
            $plugin = new $class();
            $plugin->setRequest($this->getRequest());
            $plugin->setResponse($this->getResponse());
            $plugin->init();
            $this->_plugins[$name] = $plugin;
        }
        
        return $this->_plugins[$name];
    }
    
    private function _getPlugins()
    {
    	return $this->_plugins;
    }
    
    /**
    * Add a prefixPath for a plugin type
    *
    * @param  string $type
    * @param  string $classPrefix
    * @param  array $paths
    * @return Zend_View_Abstract
    */
    private function _addPluginPath($classPrefix, array $paths)
    {
    	$loader = $this->_getPluginLoader();
    	foreach ($paths as $path) {
    		$loader->addPrefixPath($classPrefix, $path);
    	}
    	return $this;
    }
    
    public function __construct()
    {
    	$this->_pluginLoader = new Zend_Loader_PluginLoader(array(
    		'Shaw_Controller_Plugin_Trace' => 'Shaw/Controller/Plugin/Trace',
    	));
    }
    
    private $_options = null;
    
    /**
     * Fetch options from front :)
     */
    private function _getOptions()
    {
    	if(! $this->_options){
    		$front = Zend_Controller_Front::getInstance();
        	$this->_options = $front->getParam('bootstrap')->getApplication()->getOptions();
    	}
    	return $this->_options;
    }
    

    private function _loadPlugins()
    {
    	$config = $this->_getOptions();
    	$config = $config['shaw']['trace'];
    	if(isset($config['sections'])){
            foreach($config['sections'] as $section)
            {
            	$this->_getPlugin($section);
            }
        }
    }
    
    private $_enabled = true;
    
    /**
     * Enabled until false :)
     * @return boolean
     */
    private function _isEnabled()
    {
    	// If its not enabled, then its definitive !
     	if(! $this->_enabled){
     		return $this->_enabled;
     	}
    	
    	$config = $this->_getOptions();
        if(! isset($config['shaw']['trace'])){
        	return $this->_enabled = false;
        }
        
        $reqUri = $this->getRequest()->getRequestUri();
        if( strpos($reqUri, '?notrace') !== false
        	|| strpos($reqUri, '&notrace') !== false){
        	return $this->_enabled = false;
        }
        
    	if(Zend_Registry::isRegistered('notrace')){ 
        	return $this->_enabled = false;
        }
        
        $config = $config['shaw']['trace'];
        
        if(! (isset($config['enabled']) && $config['enabled'] == true)){
            return $this->_enabled = false;
        }
        
        if(isset($config['ondemand']) && $config['ondemand'] == true){
	        if( ! (strpos($reqUri, '?trace') !== false
	        	|| strpos($reqUri, '&trace') !== false)){
	        	return $this->_enabled = false;
	        }
        }
          
        // No display for None html response.
        $headers = $this->getResponse()->getHeaders();
        foreach($headers as $header){
         	if($header['value'] != 'text/html'){
         		return $this->_enabled = false;
         	}
		}
            
        return true;
    }
    
    private function _render()
    {
        // Render markup
        $markup  = '<html><head>';
        $markup .= '<style>'.file_get_contents(dirname(__FILE__) . '/Trace/trace.css').'</style>';
        $markup .= '<script>'.file_get_contents(dirname(__FILE__) . '/Trace/trace.js').'</script></head><body>';
        $markup .= '<div id="shaw-trace">' . PHP_EOL;
        if($this->_plugins){
	        foreach($this->_plugins as $plugin)
	        {
	        	$markup .= '<div class="shaw-trace-section"><div class="shaw-trace-section-title">'.$plugin->title.'</div>';
	        	$markup .= $plugin->render();
	        	$markup .= '</div>';
	        	$plugin->finish();
	        }
        }
        $markup .= '</div></body></html>';
        $this->getResponse()->appendBody($markup);
    }
    
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
    	if( $this->_isEnabled() ){
	    	foreach($this->_getPlugins() as $name => $plugin)
	        {
	        	$plugin->dispatchLoopStartup($request);
	        }
        }
    }
    
	public function dispatchLoopShutdown()
    {
    	if( $this->_isEnabled() ){
    		foreach($this->_getPlugins() as $name => $plugin) {
	        	$plugin->dispatchLoopShutdown();
	        } 
	        
	        $this->_render();
        }
    }
    
	public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
    	if( $this->_isEnabled() ){
    		// Load if enabled...
        	$this->_loadPlugins();
        	
    		foreach($this->_getPlugins() as $name => $plugin) {
	        	$plugin->routeStartup($request);
	        }
        }
    }
    
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
    	if( $this->_isEnabled() ){
    		foreach($this->_getPlugins() as $name => $plugin) {
	        	$plugin->routeShutdown($request);
	        }
        }
    }
    
	public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	if( $this->_isEnabled() ){
    		foreach($this->_getPlugins() as $name => $plugin) {
	        	$plugin->routeShutdown($request);
	        }
        }
    }

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
    	if( $this->_isEnabled() ){
    		foreach($this->_getPlugins() as $name => $plugin) {
	        	$plugin->postDispatch($request);
	        }
        }
    }
}
