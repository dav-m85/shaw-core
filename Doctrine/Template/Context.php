<?php
/**
 * Add model contextualisation to records inside a table.
 * 
 * Need basically two additionnal columns, one for modelId and the other for modelName
 * 
 * @todo Implement lookup table if needed
 * @author david
 *
 */
class Shaw_Doctrine_Template_Context 
extends Doctrine_Template
{
	protected $_options = array(
			'modelIdField' => 'model_id',
			'modelClassField' => 'model_class');
	
    public function getContext()
    {
    	$invoker = $this->getInvoker();
    	
    	$id = $invoker->get( $this->getOption('modelIdField') );
    	$class = $invoker->get( $this->getOption('modelClassField') );
    	
    	return Doctrine_Core::getTable($class)->find($id);
    }
    
    /**
     * Change all instance of a specific context to another one.
     * @param Doctrine_Record $from
     * @param Doctrine_Record $to
     * @return int Number of contexts impacted
     */
    public function transformContextTableProxy(Doctrine_Record $from, Doctrine_Record $to)
    {
    	$table = $this->getTable();
    	$q = Doctrine_Query::create()->update($table->getComponentName() . ' c')
    	->set('c.' . $this->getOption('modelIdField'), '?', $to->id)
    	->set('c.' . $this->getOption('modelClassField'), '?', get_class($to))
    	->andWhere('c.' . $this->getOption('modelIdField') . ' = ?', $from->id)
    	->andWhere('c.' . $this->getOption('modelClassField') . ' = ?', get_class($from));
    	
    	return $q->execute();
    }
    
    
    /**
     * Add a context to the current record.
     * 
     * @param Doctrine_Record $context
     * @throws Exception
     */
    public function setContext(Doctrine_Record $context)
    {
    	$invoker = $this->getInvoker();
    	
    	if(! $context->exists()){
    		throw new Exception('Context shall exist');
    	}
    	
    	$invoker->set( $this->getOption('modelIdField'), $context->id);
    	$invoker->set( $this->getOption('modelClassField'), get_class($context));
    	
    	// We shall remove
    }
    
    /**
     * Usefull for quick testing identity of context without fetching from database.
     * @param Doctrine_Record $context
     */
    public function isContext(Doctrine_Record $context)
    {
    	$invoker = $this->getInvoker();
    	
    	$id = $invoker->get( $this->getOption('modelIdField') );
    	$class = $invoker->get( $this->getOption('modelClassField') );
    	
    	return ($id == $context->id && $class == get_class($context));
    }
}