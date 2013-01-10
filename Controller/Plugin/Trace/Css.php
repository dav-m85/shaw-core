<?php

class Shaw_Controller_Plugin_Trace_Css
	extends Shaw_Controller_Plugin_Trace_Abstract
{
	protected $logWriter = null;
	
	public $description = 'arf';
	
	public $title = 'CSS';
	
	public function render()
	{
		$html = $this->getResponse()->getBody();
        $reqrot = $this->_getPagename();
        $css = $this->_unstash('css');
        $currentCss = array();

        // Extract class and ids
        preg_match_all('!(class|id)=["\'](.*)["\']!U', $html, $matches);
        for($i = 0, $size = sizeof($matches[0]); $i < $size; ++$i){
            if($matches[1][$i] == 'class'){
                $mtch = explode(' ', $matches[2][$i]);
                foreach($mtch as $mt)
                    if($mt != ' ' && $mt != '' && $mt !== null){
                        $currentCss[] = '.' . $mt;
                        if(!is_array($css['.' . $mt])){
                            $css['.' . $mt] = array($reqrot);
                        }
                        else if(! in_array($reqrot, $css['.' . $mt])){ 
                            array_push($css['.' . $mt], $reqrot);
                        }
                    }
            }
            else{
                $mt = $matches[2][$i];
                $currentCss[] = '#' . $mt;
                if(!is_array($css['#' . $mt])){
                    $css['#' . $mt] = array($reqrot);
                }
                else if(! in_array($reqrot, $css['#' . $mt])){ 
                    array_push($css['#' . $mt], $reqrot);
                }
            }
        }
        
        $currentCss = array_values(array_unique($currentCss));
        $this->_stash('css', $css);
        
        $markup = '';
        foreach($currentCss as $identifier)
            $markup .= $identifier . '<br />';
            
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