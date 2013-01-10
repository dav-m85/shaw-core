<?php

class Shaw_Doctrine_Listener_Creation extends Doctrine_Record_Listener
{
    public function __construct($fieldName = 'creation'){
    	$this->setOption('fieldName', $fieldName);
    }
    
	public function preInsert(Doctrine_Event $event)
    {
    	$fieldName = $this->getOption('fieldName');
        $creation = $event->getInvoker()->$fieldName;
        if(! $creation)
            $event->getInvoker()->$fieldName = date('Y-m-d H:i:s', time());
    }
}