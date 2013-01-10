<?php
/*

<!-- Google Analytics -->
<script type="text/javascript">

</script>

*/

/**
 * Controller plugin that sets the correct paths to the Zend_Layout and
 * Zend_Controller_Plugin_errorHandler instances
 *
 * @package    Skyrocket_Plugin
 * @copyright  Copyright (c) 2005-2008 Skyrocket Concepts (http://www.skyrocket.be)
 */
class Shaw_Controller_Plugin_GoogleAnalytics
	extends Shaw_Controller_Plugin_Abstract
{
	const DISABLED = 'Shaw_Controller_Plugin_GoogleAnalytics_DISABLED';
	
	protected $_optionsKey = 'googleAnalytics';
	
	public function postDispatch(Zend_Controller_Request_Abstract $request)
	{
		$options = $this->getOptions();
		
		// The response shall be html
		$display = true;
		$headers = $this->getResponse()->getHeaders();
		foreach($headers as $header){
			if($header['value'] != 'text/html'){
				Shaw_Log::debug('Not html response');
				return;
			}
		}
		
		// Check if we have a layout
		if (! Zend_Controller_Action_HelperBroker::hasHelper('layout')) {
			Shaw_Log::debug('No layout');
			return;
		}
		$layout = Zend_Controller_Action_HelperBroker::getExistingHelper('layout');
		$view = $layout->getView();
		
		// Need a token
		if(! isset($options['token'])){
			Shaw_Log::warn('No google account providen');
			return;
		}
		
		$token = $options['token'];
		
		// Maybe we deactivated this plugin through Registry
		$disabled = (Zend_Registry::isRegistered(self::DISABLED) && Zend_Registry::get(self::DISABLED));
		if($disabled){
			Shaw_Log::debug('Shaw_Controller_Plugin_GoogleAnalytics DISABLED');
		}
		if($disabled || $token == false){
			$script = 'var _gaq = _gaq || [];';
		}
		else{
			Shaw_Log::debug('Google Analytics code inserted.');
/*		$script = <<<EOF
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
try{
	var pageTracker = _gat._getTracker("$token");
	pageTracker._trackPageview();
} catch(err) {}
EOF;
*/
			$script = <<<EOF
_gaq.push(['_setAccount', '$token']);
_gaq.push(['_trackPageview']);

(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www' ) + '.google-analytics.com/ga.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
EOF;
		}
		
		$view->headScript()->appendScript($script);
	}
}