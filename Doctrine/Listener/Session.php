<?php

// Model_Session : Retrieve via Core ?
class Shaw_Doctrine_Listener_Session extends Doctrine_Record_Listener
{
    public function preInsert(Doctrine_Event $event)
    {
        $id = Shaw_Session::getId();
        if($id && ! $event->getInvoker()->session)
            $event->getInvoker()->session = $id;
    }
}