<?php

class Shaw_Controller_Plugin_Trace_Request
	extends Shaw_Controller_Plugin_Trace_Abstract
{
	public $description = 'Basic request informations';
	
	public $title = 'Request';
	
	public function render()
	{
		$front = Zend_Controller_Front::getInstance();
		
		$layout = Zend_Layout::getMvcInstance();
		
		$renderer = false;
		if (Zend_Controller_Action_HelperBroker::hasHelper('viewRenderer')) {
		    $renderer = Zend_Controller_Action_HelperBroker::getExistingHelper('viewRenderer');
		}
		
		$request = $front->getRequest();
        $req = $request->getModuleName() .'/'
            . $request->getControllerName() .'/'
            . $request->getActionName() . ' ('
            . $front->getRouter()->getCurrentRouteName(). ')';
        
        $markup .= '<div class="shaw-trace-name">'.$req.'</div>';
        $markup .= sprintf('<div class="shaw-trace-hostname">%s at %s</div>',
            $_SERVER['SERVER_NAME'],
            Shaw_DateTime::now()->format('Y-m-d H:i:s\Z')
        );
        
        $markup .= '<div class="kpi">' . Shaw_Core::format_bytes(memory_get_peak_usage()) . '&nbsp<span>/' .ini_get('memory_limit'). '</span></div>';
        
        if($renderer){
            $layoutPath = $layout->getViewScriptPath() . '<b style="color: orange">' .$layout->getLayout() . '.' . $layout->getViewSuffix() . '</b>';
            $layoutPath = substr($layoutPath, strlen(APPLICATION_PATH));
            $markup .= '<div class="kpi">' .  $layoutPath  . ' => <b style="color: green;">' . $renderer->getViewScript() .  '</b></div>';
            
            // Title ? Meta ? Others ?
            if($layout){
            	$view = $layout->getView();
            	$arr = array();
            	// var_dump($view->headMeta()->getContainer()->getValue()); die;
            	$arr['title'] = $view->headTitle()->getContainer()->getValue();
            	foreach($view->headMeta()->getContainer()->getValue() as $meta){
            		$type = $meta->type;
            		$arr[$meta->$type] .= (string) $meta->content;
            	}
            	$markup .= '<h4>Meta &amp; Title</h4>';
            	$markup .= $this->_renderItemTable($arr, array(), true);
            }
        }
        
        if ($renderer) {
            $markup .= '<h4>View scope vars</h4>';
            $vars = $renderer->view->getVars();
            $markup .= $this->_renderItemTable($vars, array(), false);
        }
        
        return $markup;
	}
}