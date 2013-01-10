<?php

class Shaw_Doctrine_Template_Lookup 
	extends Doctrine_Template
{
    
	private $_lookup = array();
    private $_reverseLookup = array();
    private $_flatOptions = array();
    
    /**
     * 
     */ 
    public function lookupTableProxy($model, $autoInsert = true, $creationOptions = array())
    {
    	$this->_loadLookups();
    	
        //
        if(is_numeric($model)){
	        return $this->_reverseLookup[$model];
        }
    	
    	// Get model class type.
        if(is_object($model))
            $model = get_class($model);

        // Add lkp if not existing.
        if(! array_key_exists($model, $this->_lookup)){
            if(! $autoInsert){
                throw new Exception("Unknown lookup value $model in " . get_class($this->getTable()));
            }
            
            $type = $this->getTable()->create();
            $type->name = $model;
            $type->fromArray($creationOptions);
            $type->save();
            
            $this->_lookup[$model] = $type->id;
            $this->_reverseLookup[$type->id] = $model;
            
            $this->_reloadLookups();
        }
        
        return $this->_lookup[$model];
    }
    
    public function getAsOptionsTableProxy()
    {
        $this->_loadLookups();
        return $this->_reverseLookup;
    }
    
    public function getAsFlatOptionsTableProxy()
    {
        $this->_loadLookups();
        return $this->_flatOptions;
    }
    
    private function _loadLookups()
    {
    	if(empty($this->_lookup)){
    		$lookup = $this->getTable()->findAll(Doctrine_Core::HYDRATE_ARRAY);
    		foreach($lookup as $type){
    			$this->_lookup[$type['name']] = $type['id'];
    			$this->_flatOptions[$type['name']] = $type['name'];
    		}
    		$this->_reverseLookup = array_flip($this->_lookup);
    		 
    	}
    }
    
    private function _reloadLookups()
    {
        $this->_lookup = array();
        $this->_loadLookups();
    }
}