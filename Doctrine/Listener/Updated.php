<?php

class Shaw_Doctrine_Listener_Updated extends Doctrine_Record_Listener
{
	public function __construct($fieldName = 'updated'){
    	$this->setOption('fieldName', $fieldName);
    }
    
    public function preSave(Doctrine_Event $event)
    {
    	$fieldName = $this->getOption('fieldName');
        $updated = $event->getInvoker()->$fieldName;
        if(! $updated)
            $event->getInvoker()->$fieldName = date('Y-m-d H:i:s', time());
    }
}