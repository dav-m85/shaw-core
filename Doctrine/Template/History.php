<?php

class Shaw_Doctrine_Template_History
	extends Doctrine_Template
{    
	// need to define : which table to use for historizing
	// which type table to use for identifying things
	
	// need to use the identifiers columns of a record...
	// would be more portable than just using "id" (that really not portable)
    // faire ptet un CLI du genre php shaw.php install type
    // de la même facon que doctrine.php generate-models-table...
    
    // ptet le faire plus comme task.php :) finaliser le CLI
    // ou carrément une tache ?
    
    // carrément faire un include.php pour tout les fichiers
    // TODO : install/unisntall functions for adding/removing the historying table :)
    
    /**
     * Carbon freeze a record for history purpose, Luke.
     *
     * @param Doctrine_Record $model Model to be freezed.
     * @see http://starwars.wikia.com/wiki/Carbon_freezing
     */ 
    public function carbonfreeze($blob = null)
    {
        $hansolo = $this->getInvoker()->getData();
        $name = $this->getTable()->getTableName();
        
        $lookup = $this->getOption('lookupTable');
        if($lookup == null){
            $lookup = 'Db_ShawLookup';
        }
        
        // Prepare and save the carbonite case.
        $case = new Db_ShawHistory();
        $case->type = Doctrine_Core::getTable($lookup)->lookup($name); // Shall create a template for this.
        $case->parent = $hansolo['id'];
        
        if(! $blob)
            $case->datum = serialize($hansolo);
        else
            $case->datum = serialize($blob);
        
        $case->save();
    }
    
    /**
     * Dethaw all previously carbonfreezed ancestors.
     */ 
    public static function findCarbonfreezedAncestors(){
        throw new Exception('Not Implemented');
    }
}