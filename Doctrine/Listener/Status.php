<?php
// Utile pour la sat machine...

class Shaw_Doctrine_Listener_Status extends Doctrine_Record_Listener
{
    public function postInsert(Doctrine_Event $event){
        $this->_saveStatusChange($event);
    }
    
    public function postUpdate(Doctrine_Event $event){
        $this->_saveStatusChange($event);
    }
    
    private function _saveStatusChange(Doctrine_Event $event){
        $invoker = $event->getInvoker();
        
        if(array_key_exists('status', $new = $invoker->getLastModified(false))){
            $old = $invoker->getLastModified(true);
            $status = new Model_Status();
            $status->fromArray(array(
                // creation
                // session
                'itemId' => $invoker['id'],
                'model' => get_class($invoker),
                'previousStatus' => $old['status'],
                'status' => $new['status']
            ));
            $status->save();
        }
    }
}