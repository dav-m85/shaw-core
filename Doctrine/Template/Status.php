<?php

class Shaw_Doctrine_Template_Status extends Doctrine_Template
{
    
    public function setTableDefinition()
    {
        // Does not work...
        $this->addListener(new Shaw_Doctrine_Listener_Status());
    }
    
    /**
     * Retrieve a list of all preceding statuses.
     */ 
    public function getStatusHistory(){
        $invoker = $this->getInvoker();
        
        return Doctrine_Query::create()
            ->from('Model_Status s')
            ->where('s.model = ?',get_class($invoker))
            ->andWhere('s.itemId = ?', $invoker['id'])
            ->execute(null, Doctrine_Core::HYDRATE_ARRAY);
    }
    
    /**
     * Define a new status.
     */ 
    public function status($newStatus){
        $invoker = $this->getInvoker();
        Shaw_Log::debug('Status for '.get_class($invoker).':'.$invoker->id.' changed to '.$newStatus);
        $invoker->set('status', $newStatus)->save();
        
        if(method_exists($invoker, 'onStatusChanged')){
            call_user_func(array($invoker, 'onStatusChanged'), $newStatus);
        }
        
        return $invoker;
    }
}