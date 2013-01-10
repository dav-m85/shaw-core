<?php
/**
 * 
 * @author david
 */
class Shaw_Doctrine_Template_Metadata
extends Doctrine_Template
{    
    /**
     * @var array $_options Template options
     */
    protected $_options = array('metaTable' => 'Model_Metadata');
    
    /**
     * 
     */
    public function getMetadata($name)
    {
        $invoker = $this->getInvoker();
        if(! $invoker->exists())
        {
            throw new Exception('Cannot call metadata on a non existing record.');
        }
        $table = Doctrine_Core::getTable($this->getOption('metaTable'));
        return  call_user_func(array($table, 'getMetadata'), $invoker, $name);
    }
    
    public function setMetadata($name, $value)
    {
        $invoker = $this->getInvoker();
        if(! $invoker->exists())
        {
            throw new Exception('Cannot call metadata on a non existing record.');
        }
        $table = Doctrine_Core::getTable($this->getOption('metaTable'));
        return call_user_func(array($table, 'setMetadata'), $invoker, $name, $value);
    }
    
    public function getAllMetadata()
    {
        $invoker = $this->getInvoker();
        if(! $invoker->exists())
        {
            throw new Exception('Cannot call metadata on a non existing record.');
        }
        $table = Doctrine_Core::getTable($this->getOption('metaTable'));
        return call_user_func(array($table, 'getAllMetadata'), $invoker);
    }
}