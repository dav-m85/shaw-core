<?php

class Shaw_Email_Layout
	extends Zend_Layout
{
	/**
	* Render layout
	*
	* Sets internal script path as last path on script path stack, assigns
	* layout variables to view, determines layout name using inflector, and
	* renders layout view script.
	*
	* $name will be passed to the inflector as the key 'script'.
	*
	* @param  mixed $name
	* @return mixed
	*/
	public function render($name = null, $renderMode = null)
	{
		if (null === $name) {
			$name = $this->getLayout();
		}
	
		if ($this->inflectorEnabled() && (null !== ($inflector = $this->getInflector())))
		{
			$name = $this->_inflector->filter(array('script' => $name));
		}
	
		$view = $this->getView();
	
		if (null !== ($path = $this->getViewScriptPath())) {
			if (method_exists($view, 'addScriptPath')) {
				$view->addScriptPath($path);
			} else {
				$view->setScriptPath($path);
			}
		} elseif (null !== ($path = $this->getViewBasePath())) {
			$view->addBasePath($path, $this->_viewBasePrefix);
		}
		
		$result = $view->render($name, $renderMode);
		
		return $result;
	}
}