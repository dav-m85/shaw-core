<?php

$test = new OpenTest();
foreach(get_class_methods('OpenTest') as $method){
	if(strpos($method, 'test') !== 0)
		continue;
	echo '**** running '. $method . PHP_EOL;
	$test->$method();
}

// Dummy
class PHPUnit_Framework_TestCase
{
	public function assertTrue($var)
	{
		echo 'assertTrue' . PHP_EOL;
		var_dump($var);
	}
	
	public function assertEquals($var, $foo)
	{
		echo 'assertEquals' . PHP_EOL;
		var_dump($var);
//		var_dump($foo);
	}
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// See also http://www.phpunit.de/manual/current/en/writing-tests-for-phpunit.html
// date_default_timezone_set('Europe/Paris');