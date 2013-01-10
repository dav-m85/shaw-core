<?php
/**
 * 
 * @author david
 */
class Shaw_Doctrine_Template_MetadataHolder
extends Doctrine_Template
{    
    /**
     * @var array $_options Template options
     */
    protected $_options = array(
    	'metaTableModelLookup' => 'Model_MetadataModel',
    	'metaTableNamelLookup' => 'Model_MetadataName',
    );
    
    /**
     * 
     */
    public function getMetadataTableProxy(Doctrine_Record $model, $name)
    {
        $modelId = $model->identifier();
        if(! array_key_exists('id', $modelId))
            throw new Exception('Supports only single PK "id" tables...');
        $modelId = $modelId['id'];
        
        try{
            $modelName = Doctrine_Core::getTable($this->getOption('metaTableModelLookup'))->lookup(get_class($model), false);
            $metaId = Doctrine_Core::getTable($this->getOption('metaTableNamelLookup'))->lookup($name, false);
        }
        catch(Exception $e){
            return false;
        }
        
        $meta = Doctrine_Query::create()->from($this->getTable()->getComponentName())
            ->andWhere('name_id = ?', $metaId)
            ->andWhere('model_name = ?', $modelName)
            ->andWhere('model_id = ?', $modelId)
            ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
        
        if($meta){
            $meta = unserialize($meta['value']);
        }
        return $meta;
    }
    
    public function setMetadataTableProxy(Doctrine_Record $model, $name, $value)
    {
        $modelId = $model->identifier();
        if(! array_key_exists('id', $modelId))
            throw new Exception('Supports only single PK "id" tables...');
        $modelId = $modelId['id'];
        
        $modelName = Doctrine_Core::getTable($this->getOption('metaTableModelLookup'))->lookup(get_class($model), true);
        $metaId = Doctrine_Core::getTable($this->getOption('metaTableNamelLookup'))->lookup($name, true);
        
        $className = $this->getTable()->getComponentName();
        
        $meta = Doctrine_Query::create()->from($className)
            ->andWhere('name_id = ?', $metaId)
            ->andWhere('model_name = ?', $modelName)
            ->andWhere('model_id = ?', $modelId)
            ->fetchOne(null, Doctrine_Core::HYDRATE_RECORD);
        
        if(! $meta){
            $meta = new $className();
            $meta->name_id = $metaId;
            $meta->model_name = $modelName;
            $meta->model_id = $modelId;
        }
        
        $meta->value = serialize($value);
        $meta->save();
    }
    
    public function getAllMetadataTableProxy(Doctrine_Record $model)
    {
    	$modelId = $model->identifier();
        if(! array_key_exists('id', $modelId))
            throw new Exception('Supports only single PK "id" tables...');
        $modelId = $modelId['id'];
        
        try{
            $modelName = Doctrine_Core::getTable($this->getOption('metaTableModelLookup'))->lookup(get_class($model), false);
        }
        catch(Exception $e){
            return false;
        }
        
        $className = $this->getTable()->getComponentName();
        $metas = Doctrine_Query::create()->from($className)
            ->andWhere('model_name = ?', $modelName)
            ->andWhere('model_id = ?', $modelId)
            ->execute(null, Doctrine_Core::HYDRATE_ARRAY);
        
        $result = array();
        if($metas){
            foreach($metas as $meta){
                $metaName = Doctrine_Core::getTable($this->getOption('metaTableNamelLookup'))->lookup((int)$meta['name_id'], false);
                $result[$metaName] = unserialize($meta['value']);
            }
        }
        return $result;
    }
}