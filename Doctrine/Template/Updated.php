<?php

class Shaw_Doctrine_Template_Creation extends Doctrine_Template
{
    
    public function setTableDefinition()
    {
        // Does not work...
        $this->addListener(new Shaw_Doctrine_Listener_Updated());
    }
    
    	// même que pour Creation mais Updated :)
    
    
    
    /*
     Cannot use get/set
    public function setCreation($value, $format = 'U')
    {
        if ($value instanceof DateTime) {
            $value = $value->toString($format);
        } else if (null !== $value && false === is_string($value)) {
            throw new Exception('Unsupported type or format.');
        }
 
        $this->_set('creation', $value);
    }
 
    public function getCreation()
    {
        $value = $this->_get('creation');
        return ($value) ? DateTime::createFromFormat('Y-m-d H:i:s', $value) : $value;
    }
    C
    */
}