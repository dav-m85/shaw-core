<?php

class Shaw_Controller_Plugin_Trace_Image
	extends Shaw_Controller_Plugin_Trace_Abstract
{
	protected $logWriter = null;
	
	public $description = 'arf';
	
	public $title = 'Image';
	
	public function render()
	{
		$html = $this->getResponse()->getBody();
        $reqrot = $this->_getPagename();
        
        $images = $this->_unstash('images');
        
        // Extract images (only from code)
        preg_match_all('!src=["\'](.*)["\']!U', $html, $matches);
        
        $images = array();
        for($i = 0, $size = sizeof($matches[0]); $i < $size; ++$i)
            if(! preg_match('!^/js!', $matches[1][$i]))
                $images[] = $matches[1][$i];
                
        $images = array_values(array_unique($images));
        
        // render
        $markup = '';
        foreach($images as $image)
            $markup .= $image . '<br />';
        
        return $markup;
	}
	
	private $_stashPath = '/../var/tmp/';
	
	private function _unstash($name){
		$path = APPLICATION_PATH . $this->_stashPath . $name . '.json';
		$json = @file_get_contents($path);
		if($json)
		$data = Zend_Json::decode($json);
		else
		$data = array();
		return $data;
	}
	
	private function _getPagename(){
		$request = $this->getRequest();
		$reqName = $request->getModuleName() .'-'. $request->getControllerName() .'-'. $request->getActionName();
		$rotName = Zend_Controller_Front::getInstance()->getRouter()->getCurrentRouteName();
		return $reqName . ':' . $rotName;
	}
	
	private function _stash($name,$data){
		$path = APPLICATION_PATH . $this->_stashPath . $name . '.json';
		$json = Zend_Json::encode($data);
		file_put_contents($path, $json);
		return $this;
	}
}