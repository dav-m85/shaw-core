<?php

/**
 * Like Lookup, but cache results here :)
 * @author davidmoreau
 *
 */
class Shaw_Doctrine_Template_CachedLookup 
	extends Doctrine_Template
{
	private $_lookup = array();
    private $_reverseLookup = array();
    private static $_manager = null;
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
            
            // Shaw_Log::debug('Adding value to lookup, ' . $model);
            
            
            $this->_lookup[$model] = $type->id;
            
            $this->_getCacheManager()->getCache('default')->save($this->_lookup, $this->_getCacheId());
            
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
    
    private function _getCacheId()
    {
        return 'CachedLookup'.$this->getTable()->getTableName();
    }
    
    private function _loadLookups()
    {
    	if(empty($this->_lookup)){
    	    // Shaw_Log::debug('lookup to load');
    	    // try cache
    	    $cacheId = $this->_getCacheId();
    	    $cache = $this->_getCacheManager()->getCache('default');
    	    
    	    $lookup = array();
    	    
    	    if(($cache->test($cacheId)) === false){
        	    // fallback to database :/
        	    // Shaw_Log::debug('lookup from database');
    	        $tmp = $this->getTable()->findAll(Doctrine_Core::HYDRATE_ARRAY);
        		
        	    foreach($tmp as $type){
        			$lookup[$type['name']] = $type['id'];
        		}
    	        
    	        $cache->save($lookup, $cacheId);
    	    }
    	    else{
    	        // Shaw_Log::debug('lookup from cache ' . $cacheId);
    	        $lookup = $cache->load($cacheId);
    	        // Shaw_Log::debug(var_export($lookup, true));
    	    }
    	    
    	    $this->_lookup = $lookup;
    		$this->_reverseLookup = array_flip($lookup);
    	}
    	else{
    	    // Shaw_Log::debug('lookup already in class');
    	}
    }
    
    private function _reloadLookups()
    {
        $this->_lookup = array();
        $this->_loadLookups();
    }
    
	/**
     * Get the Cache Manager instance or instantiate the object if not
     * exists. Attempts to load from bootstrap if available.
     *
     * @return Zend_Cache_Manager
     */
    private static function _getCacheManager()
    {
        if (self::$_manager !== null) {
            return self::$_manager;
        }
        $front = Zend_Controller_Front::getInstance();
        if ($front->getParam('bootstrap')
            && $front->getParam('bootstrap')->getResource('CacheManager')) {
            return $front->getParam('bootstrap')
                ->getResource('CacheManager');
        }
        self::$_manager = new Zend_Cache_Manager;
        return self::$_manager;
    }
}