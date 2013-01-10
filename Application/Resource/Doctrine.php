<?php
/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

class Shaw_Application_Resource_Doctrine
    extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        // @seealso http://www.doctrine-project.org/jira/browse/DC-288
        $autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->registerNamespace('sfYaml')->pushAutoloader(array('Doctrine_Core', 'autoload'), 'sfYaml');
        
        $options = $this->getOptions();
        
		Doctrine_Core::debug($options['debug']);
	
        $manager = Doctrine_Manager::getInstance();
        $manager->setAttribute ( Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_ALL );
        $manager->setAttribute ( Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, true );
        $manager->setAttribute ( Doctrine_Core::ATTR_MODEL_LOADING, $options['model_autoloading'] );
        $manager->setAttribute ( Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true );
        
        $manager->registerHydrator('Options', 'Shaw_Doctrine_Hydrator_Options');
        
        Doctrine_Core::loadModels($options['models_path']);
        $conn = Doctrine_Manager::connection($options['dsn'], 'doctrine');
        $conn->setAttribute(Doctrine_Core::ATTR_USE_NATIVE_ENUM, true);
		$conn->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);
		
        $conn->setCharset('utf8');
        $conn->setCollate('utf8_general_ci');
        
        // benchmarking profiler
        // $profiler = new Shaw_Benchmark_DoctrineProfiler();
        // $conn = Doctrine_Manager::connection();
        // $conn->setListener($profiler);
	
        return $conn;
    }
    /*
     * $autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->registerNamespace('sfYaml')->pushAutoloader(array('Doctrine', 'autoload'), 'sfYaml');

		$doctrineConfig = $this->getOption('doctrine');
		Doctrine_Core::debug($doctrineConfig['debug']);

		$manager = Doctrine_Manager::getInstance();
		$manager->setAttribute ( Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_ALL );
		$manager->setAttribute ( Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, true );
		$manager->setAttribute ( Doctrine_Core::ATTR_MODEL_LOADING, $doctrineConfig['model_autoloading'] );
		$manager->setAttribute ( Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true );

		Doctrine_Core::loadModels($doctrineConfig['models_path']);
		$conn = Doctrine_Manager::connection($doctrineConfig['dsn'], 'doctrine');
		$conn->setAttribute(Doctrine_Core::ATTR_USE_NATIVE_ENUM, true);
		$conn->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);
		$conn->setCharset('utf8');
		$conn->setCollate('utf8_general_ci');
     */
}
