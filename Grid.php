<?php

class Shaw_Grid
{
	/**
	 * Constructor
	 *
	 * Registers form view helper as decorator
	 *
	 * @param mixed $options
	 * @return void
	 */
	public function __construct($options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		} elseif ($options instanceof Zend_Config) {
			$this->setConfig($options);
		}
	
		// Extensions...
		$this->init();
	
		$this->loadDefaultDecorators();
	}
	
	/**
	 * Initialize form (used by extending classes)
	 *
	 * @return void
	 */
	public function init()
	{
	}
	
	/**
	 * Set form state from options array
	 *
	 * @param  array $options
	 * @return Zend_Form
	 */
	public function setOptions(array $options)
	{
		if (isset($options['prefixPath'])) {
			$this->addPrefixPaths($options['prefixPath']);
			unset($options['prefixPath']);
		}
	
		if (isset($options['elementPrefixPath'])) {
			$this->addElementPrefixPaths($options['elementPrefixPath']);
			unset($options['elementPrefixPath']);
		}
	
		if (isset($options['displayGroupPrefixPath'])) {
			$this->addDisplayGroupPrefixPaths($options['displayGroupPrefixPath']);
			unset($options['displayGroupPrefixPath']);
		}
	
		if (isset($options['elementDecorators'])) {
			$this->_elementDecorators = $options['elementDecorators'];
			unset($options['elementDecorators']);
		}
	
		if (isset($options['elements'])) {
			$this->setElements($options['elements']);
			unset($options['elements']);
		}
	
		if (isset($options['defaultDisplayGroupClass'])) {
			$this->setDefaultDisplayGroupClass($options['defaultDisplayGroupClass']);
			unset($options['defaultDisplayGroupClass']);
		}
	
		if (isset($options['displayGroupDecorators'])) {
			$displayGroupDecorators = $options['displayGroupDecorators'];
			unset($options['displayGroupDecorators']);
		}
	
		if (isset($options['elementsBelongTo'])) {
			$elementsBelongTo = $options['elementsBelongTo'];
			unset($options['elementsBelongTo']);
		}
	
		if (isset($options['attribs'])) {
			$this->addAttribs($options['attribs']);
			unset($options['attribs']);
		}
	
		$forbidden = array(
				'Options', 'Config', 'PluginLoader', 'SubForms', 'Translator',
				'Attrib', 'Default',
		);
	
		foreach ($options as $key => $value) {
			$normalized = ucfirst($key);
			if (in_array($normalized, $forbidden)) {
				continue;
			}
	
			$method = 'set' . $normalized;
			if (method_exists($this, $method)) {
				if($normalized == 'View' && !($value instanceof Zend_View_Interface)) {
					continue;
				}
				$this->$method($value);
			} else {
				$this->setAttrib($key, $value);
			}
		}
	
		if (isset($displayGroupDecorators)) {
			$this->setDisplayGroupDecorators($displayGroupDecorators);
		}
	
		if (isset($elementsBelongTo)) {
			$this->setElementsBelongTo($elementsBelongTo);
		}
	
		return $this;
	}
	
	/**
	 * Set form state from config object
	 *
	 * @param  Zend_Config $config
	 * @return Zend_Form
	 */
	public function setConfig(Zend_Config $config)
	{
		return $this->setOptions($config->toArray());
	}
	
	/**
	 * Render form
	 *
	 * @param  Zend_View_Interface $view
	 * @return string
	 */
	public function render(Zend_View_Interface $view = null)
	{
		if (null !== $view) {
			$this->setView($view);
		}
	
		$content = '';
		foreach ($this->getDecorators() as $decorator) {
			$decorator->setElement($this);
			$content = $decorator->render($content);
		}
		$this->_setIsRendered();
		return $content;
	}
}