<?php
/**
 * Event generation / management.
 * 
 * In order to be working, the target class needs the following fields :
 * - id (shall be the unique identifier)
 * - action
 * - group, taht shall be foreignkeyed to a lookup table.
 * - parent
 * - options
 * 
 * @author david
 */
class Shaw_Doctrine_Template_Event
extends Doctrine_Template
{    
    /**
    * Helper for constructing an event object.
    * 
    * @param string $group
    * @param string $action
    * @param mixed $parent
    */
    public function spawnTableProxy($action = null, $group = null, $parent = null)
    {
        try{
            $table = $this->getTable();
            $event = $table->create();
            $event->action = substr($action, 0, 5);
            
            $defs = $table->getRelations();
            if($group){
                $ok = false;
                foreach($defs as $name => $def){
                    if($def->getLocal() == 'group'){
                        $event->group = $def->getTable()->lookup($group);
                        $ok = true;
                        break;
                    }
                }
                if(!$ok){
                    throw new Exception('Cannot find group lookup.');
                }
            }
            
            if($action){
            	$defs = $table->getRelations();
            	$ok = false;
            	foreach($defs as $name => $def){
            		if($def->getLocal() == 'action'){
            			$event->action = $def->getTable()->lookup($action);
            			$ok = true;
            			break;
            		}
            	}
            	if(!$ok){
            		throw new Exception('Cannot find action lookup.');
            	}
            }
            else{
            	throw new Exception('Please define an action.');
            }
             
            if($parent)
            {
                if($parent instanceof Doctrine_Record){
                    $parent = $parent->toArray();
                }
                else if(! is_array($parent)){
                    throw new Exception('parent shall be either Doctrine_Record or array.');
                }
                $event->parent = $parent['id'];
                unset($parent['id']);
                unset($parent['action']);
                unset($parent['creation']);
                unset($parent['parent']);
                unset($parent['group']);
                $event->fromArray($parent, false);
            }
            
            return $event;
        }
        catch(Exception $e){
            Shaw_Log::warn($e, 'Cannot generate event.');
            return false;
        }
    }
    
    /**
     * Retrieve an event based on its hash value.
     * @param unknown_type $hash
     */
    public function byHashTableProxy($hash)
    {
        $array = unserialize(Shaw_Core::base64url_decode($hash));
        $event = false;
        if(isset($array['_e'])){
            $event = $this->getTable()->find($array['_e']);
        }
        return $event;
    }
    
    /**
     * Unique representation of the record.
     */
    public function getHash()
    {
        $invoker = $this->getInvoker();
        if(! $invoker->exists())
        {
            throw new Exception('Cannot call getHash on a non existing event.');
        }
        
        $hash = array(
                '_e' => $invoker->id,
        );
    
        return Shaw_Core::base64url_encode(serialize($hash));
    }
}