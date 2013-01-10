<?php

class Shaw_Controller_Plugin_Trace_Abstract
extends Zend_Controller_Plugin_Abstract
{
	public $description = 'no description';
	
	public $title = 'no title';
	
	public function init()
	{}
	
	public function render()
	{}
	
	public function finish()
	{}
	
/**
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * @var Zend_Controller_Response_Abstract
     */
    protected $_response;

    /**
     * Set request object
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return Zend_Controller_Plugin_Abstract
     */
    public function setRequest(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Get request object
     *
     * @return Zend_Controller_Request_Abstract $request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Set response object
     *
     * @param Zend_Controller_Response_Abstract $response
     * @return Zend_Controller_Plugin_Abstract
     */
    public function setResponse(Zend_Controller_Response_Abstract $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Get response object
     *
     * @return Zend_Controller_Response_Abstract $response
     */
    public function getResponse()
    {
        return $this->_response;
    }
	
	// Render a table.
	protected function _renderItemTable($array, $exclude = array(), $export = true)
	{
		$markup = '<table class="shaw-trace-table">'
		. '<thead><tr><th>Item</th><th>Type</th>';
		if($export){$markup .= '<th>Value</th>';}
		$markup .= '</tr></thead><tbody>';
		$class = "even";
		foreach($array as $index => $item) {
			$class = $class == "odd" ? "even" : "odd";
			$itemType = is_object($item)?get_class($item):gettype($item);
	
			if(in_array($index, $exclude)){
				$itemValue = "N/C";
	
				if(method_exists($item, '__toString'))
				$itemValue = (string) $item;
				$itemValue = str_replace("\n",'<br />', $itemValue);
			}
			else{
				ob_start();
				$item instanceof Doctrine_Record ? var_dump($item->getData()): var_dump($item);
				$itemValue = ob_get_clean();
				$itemValue = '<div class="overflowing">' . $itemValue . '</div>';
			}
	
			$markup .= '<tr class="' . $class . '">'
			. '<td class="shaw-trace-20p">' . $index . '</td>'
			. '<td class="shaw-trace-20p">' . $itemType . '</td>';
			if($export){$markup .= '<td>' . $itemValue . '</td>';}
			$markup .= '</tr>' . "\n";
		}
		$markup .= '</tbody></table>';
		return $markup;
	}
}