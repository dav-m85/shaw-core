<?php

class Shaw_Form extends Zend_Form
{
    public function __construct($options = null)
    {
        // Adding prefix paths.
        $this->setName('mainForm')
            ->addPrefixPath('Shaw_Form_Element',
                'Shaw/Form/Element',
                'element')
            ->addPrefixPath('Shaw_Form_Decorator',
            'Shaw/Form/Decorator',
            'decorator');
        
        parent::__construct($options);
    }
    
    public function fromFormattedValidValues($values, $model)
    {
        $values = $this->getValidValues($values);
        foreach($model as $key => $value){
            if(array_key_exists(strtolower($key), $values)){
                $value =  $values[strtolower($key)];
                if(empty($value)) $value = null;
                
                $model[$key] = $value;
                unset($values[strtolower($key)]);
            }
        }
        $model->fromArray($values);
    }
    
    public function makeSelect($name, $table, $prefix)
    {
        $options = Shaw_core::getConstantsLookup($table, $prefix, true);
        $required = $this->$name->isRequired();
        $default = $this->$name->getValue();
        $this->removeElement($name);
        
        $elt = $this->createElement('select', $name, array('disableLoadDefaultDecorators' => true));
        $elt->addMultiOptions($options);
        $elt->setLabel($name);
        $elt->isRequired($required);
        $elt->setValue($default);

        if(! $required){
            $elt->addMultiOptions(array(0 => 'NULL'));
        }
        
        $this->_customDecorator($elt);
        
        $this->addElement($elt);
    }
    
    /**
     * Load the default decorators
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }
        
        // We're changing form a bit.
        // Default Zend_Form decorators are FormElementsHtmlTag inside dl,
        
        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('FormElements')
                 //->addDecorator('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form'))
                 ->addDecorator('Form')
                 ;
        }
    }
    
    /**
     * Use a model for default values in form.
     * 
     * @param Doctrine_Record $model
     */
    public function loadFromModel(Doctrine_Record $model)
    {
        $data = $model->getData();
        foreach($data as $key=>$value){
            $key = strtolower($key);
            $this->setDefault($key, $value);
        }
    }
    
    public function createElement($type, $name, $options = null)
    {
        $shawOptions = array(
            'decorators' => array(
                    array('decorator' => ($type == 'uploader' ? 'Uploader' : 'ViewHelper') ),
                    array('decorator' => 'Errors' ),
                    array('decorator' => 'Description', 'options' => array('tag' => 'p', 'class' => 'description') ),
                    array('decorator' => 'Label' ),
                    array('decorator' => 'HtmlTag', 'options' => array('tag' => 'div','id'  => array('callback' => array('Zend_Form_Element', 'resolveElementId')),'class' => 'form-element') ) ,      
                )
        );
        
        if(is_array($options)){
            $shawOptions = array_merge($shawOptions, $options);
        }
        
        return parent::createElement($type, $name, $shawOptions);
    }
    
    
    /**
     * Use a Doctrine Table to setup form fields.
     * 
     * @param Doctrine_Table $table Table to use.
     */
    public function createFromTable(Doctrine_Table $table){
        foreach($table->getColumns() as $column => $definition){
            // Skip PK AI fields.
            if($definition["primary"] && $definition["autoincrement"]) continue;
            
            if(!in_array($definition["type"], array('string','integer')))continue;
            
            switch($definition["type"]){
                case "string":
                    $elt = $this->createElement("text", $column);
                    //$elt->addValidator('StringLength', false, array(0, $definition["length"] - 1));
                    break;
                case "integer":
                    $elt = $this->createElement("text", $column);
                    //$elt->addFilter('Int');
                    break;
            }
            
            $elt->setLabel($column);
            
            if($definition["notnull"])
                $elt->isRequired(true);
                
            if(isset($definition["default"])){
                $elt->setValue($definition["default"]);
            }
            
           // $elt = $this->_customDecorator($elt);
            $this->addElement($elt);
        }
    }
    /*
    private function _customDecorator($element)
    {
        $element->setDisableLoadDefaultDecorators(true);
        $element->addDecorator('ViewHelper')
            ->addDecorator('Errors')
            ->addDecorator('Description', array('tag' => 'p', 'class' => 'description'))
            ->addDecorator('Label')
             ->addDecorator('HtmlTag', array(
                 'tag' => 'div',
                 'id'  => array('callback' => array(get_class($element), 'resolveElementId')),
             	 'class' => 'form-element',
        ));
        return $element;
    }
    */
    /**
     * Quickly add a submit button.
     * 
     * @param string $name Id of the button
     * @param string $label Trivial
     * @param array $attribs Attributes to set to the element. Use $elt->setAttribs()
     */
    public function addSubmit($name = 'submit', $label = 'Valider ⇨', $attribs = array()){
        $submit = $this->createElement('submit', $name);
        $submit->setLabel($label);
	$submit->setAttribs($attribs);
        $this->addElements(array($submit));
        return $this; //fluent
    }
    
    
    /**
     * Insert a decorator inside the decorator chain after another one.
     *
     * @param string|Zend_Form_Decorator $after
     * @param  string|Zend_Form_Decorator_Interface $decorator
     * @param  array|Zend_Config $options Options with which to initialize decorator
     * @return Zend_Form_Element
     *
     * @author : David Moreau
     */
    public function insertDecorator($after, $decorator, $options = null){
        // Check if after exist.
        if(! array_key_exists($after, $this->_decorators)){
            throw new Zend_Form_Exception('Decorator provided to insertDecorator does not exist in decorator chain.');
        }
        
        // Check if parameters are OK.
        if ($decorator instanceof Zend_Form_Decorator_Interface) {
            $name = get_class($decorator);
        } elseif (is_string($decorator)) {
            $name      = $decorator;
            $decorator = array(
                'decorator' => $name,
                'options'   => $options,
            );
        } elseif (is_array($decorator)) {
            foreach ($decorator as $name => $spec) {    /// ???????
                break;
            }
            if (is_numeric($name)) {
                require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('Invalid alias provided to insertDecorator; must be alphanumeric string');
            }
            if (is_string($spec)) {
                $decorator = array(
                    'decorator' => $spec,
                    'options'   => $options,
                );
            } elseif ($spec instanceof Zend_Form_Decorator_Interface) {
                $decorator = $spec;
            }
        } else {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid decorator provided to insertDecorator; must be string or Zend_Form_Decorator_Interface');
        }
        
        // Do the actual insertion.
        $newDecorators = array();
        
        foreach($this->_decorators as $key => $value){
            $newDecorators[$key] = $value;
            
            if($key == $after){
                $newDecorators[$name] = $decorator;
            }
        }
        
        $this->_decorators = $newDecorators;
        
        return $this;
    }
    
	/**
     * Set default values for elements
     *
     * Sets values for all elements specified in the array of $defaults.
     *
     * @param  array $defaults
     * @return Zend_Form
     */
    public function setDefaults($defaults)
    {
        if(is_object($defaults) && method_exists($defaults, 'toArray')){
        	$defaults = $defaults->toArray();
        }
        parent::setDefaults($defaults);
        return $this;
    }
}