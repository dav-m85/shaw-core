<?php

/** Zend_Controller_Router_Route_Abstract */
require_once 'Zend/Controller/Router/Route/Abstract.php';
/**
 * Regex Route with translation abilities.
 */
class Shaw_Controller_Router_Route_TranslatableRegex extends Zend_Controller_Router_Route_Abstract {
	/**
	 * Default translator
	 *
	 * @var Zend_Translate
	 */
	protected static $_defaultTranslator;
	/**
	 * Translator
	 *
	 * @var Zend_Translate
	 */
	protected $_translator;
	/**
	 * Default locale
	 *
	 * @var mixed
	 */
	protected static $_defaultLocale;
	/**
	 * Locale
	 *
	 * @var mixed
	 */
	protected $_locale;
	/**
	 * Wether this is a translated route or not
	 *
	 * @var boolean
	 */
	protected $_isTranslated = false;
	/**
	 * Translatable variables
	 *
	 * @var array
	 */
	protected $_translatable = array();
	protected $_regex = null;
	protected $_defaults = array();
	protected $_reverse = null;
	protected $_map = array();
	protected $_values = array();
	/**
	 * Instantiates route based on passed Zend_Config structure
	 *
	 * @param Zend_Config $config Configuration object
	 */
	public static function getInstance(Zend_Config $config) {
		$defs = ($config->defaults instanceof Zend_Config) ? $config->defaults->toArray() : array();
		$map = ($config->map instanceof Zend_Config) ? $config->map->toArray() : array();
		$reverse = (isset($config->reverse)) ? $config->reverse : null;
		return new self($config->route, $defs, $map, $reverse);
	}
	public function __construct($route, $defaults = array() , $map = array() , $reverse = null) {
		$this->_regex = $route;
		$this->_defaults = (array)$defaults;
		$this->_map = (array)$map;
		$this->_reverse = $reverse;
		if (!empty($map)) {
			foreach($map as $pos => $part) {
				if (substr($part, 0, 1) === '@' && substr($part, 1, 1) !== '@') {
					$this->_isTranslated = true;
				}
			}
		}
	}
	public function getVersion() {
		return 1;
	}
	/**
	 * Matches a user submitted path with a previously defined route.
	 * Assigns and returns an array of defaults on a successful match.
	 *
	 * @param  string $path Path used to match against this routing map
	 * @return array|false  An array of assigned values or a false on a mismatch
	 */
	public function match($path, $partial = false) {
		if ($this->_isTranslated) {
			$translateMessages = $this->getTranslator()->getMessages();
		}
		if (!$partial) {
			$path = trim(urldecode($path) , self::URI_DELIMITER);
			$regex = '#^' . $this->_regex . '$#i';
		} else {
			$regex = '#^' . $this->_regex . '#i';
		}
		$res = preg_match($regex, $path, $values);
		if ($res === 0) {
			return false;
		}
		if ($partial) {
			$this->setMatchedPath($values[0]);
		}
		// array_filter_key()? Why isn't this in a standard PHP function set yet? :)
		foreach($values as $i => $value) {
			if (!is_int($i) || $i === 0) {
				unset($values[$i]);
			}
		}
		$this->_values = $values;
		$values = $this->_getMappedValues($values);
		$defaults = $this->_getMappedValues($this->_defaults, false, true);
		$return = $values + $defaults;
		// Translate value if required
		if ($this->_isTranslated) {
			foreach($this->_map as $pos => $part) {
				if (substr($part, 0, 1) === '@' && substr($part, 1, 1) !== '@' && isset($return[$part])) {
					$npart = substr($part, 1);
					if (($originalPathPart = array_search($return[$part], $translateMessages)) !== false) {
						$return[$npart] = $originalPathPart;
					} else {
						$return[$npart] = $return[$part];
					}
					unset($return[$part]);
				}
			}
		}
		return $return;
	}
	/**
	 * Maps numerically indexed array values to it's associative mapped counterpart.
	 * Or vice versa. Uses user provided map array which consists of index => name
	 * parameter mapping. If map is not found, it returns original array.
	 *
	 * Method strips destination type of keys form source array. Ie. if source array is
	 * indexed numerically then every associative key will be stripped. Vice versa if reversed
	 * is set to true.
	 *
	 * @param  array   $values Indexed or associative array of values to map
	 * @param  boolean $reversed False means translation of index to association. True means reverse.
	 * @param  boolean $preserve Should wrong type of keys be preserved or stripped.
	 * @return array   An array of mapped values
	 */
	protected function _getMappedValues($values, $reversed = false, $preserve = false) {
		if (count($this->_map) == 0) {
			return $values;
		}
		$return = array();
		foreach($values as $key => $value) {
			if (is_int($key) && !$reversed) {
				if (array_key_exists($key, $this->_map)) {
					$index = $this->_map[$key];
				} elseif (false === ($index = array_search($key, $this->_map))) {
					$index = $key;
				}
				$return[$index] = $values[$key];
			} elseif ($reversed) {
				$index = $key;
				if (!is_int($key)) {
					if (array_key_exists($key, $this->_map)) {
						$index = $this->_map[$key];
					} else {
						$index = array_search($key, $this->_map, true);
					}
				}
				if (false !== $index) {
					$return[$index] = $values[$key];
				}
			} elseif ($preserve) {
				$return[$key] = $value;
			}
		}
		return $return;
	}
	/**
	 * Assembles a URL path defined by this route
	 *
	 * @param  array $data An array of name (or index) and value pairs used as parameters
	 * @return string Route path with user submitted parameters
	 */
	public function assemble($data = array() , $reset = false, $encode = false, $partial = false) {
		if ($this->_reverse === null) {
			require_once 'Zend/Controller/Router/Exception.php';
			throw new Zend_Controller_Router_Exception('Cannot assemble. Reversed route is not specified.');
		}
		$defaultValuesMapped = $this->_getMappedValues($this->_defaults, true, false);
		$matchedValuesMapped = $this->_getMappedValues($this->_values, true, false);
		$dataValuesMapped = $this->_getMappedValues($data, true, false);
		// handle resets, if so requested (By null value) to do so
		if (($resetKeys = array_search(null, $dataValuesMapped, true)) !== false) {
			foreach((array)$resetKeys as $resetKey) {
				if (isset($matchedValuesMapped[$resetKey])) {
					unset($matchedValuesMapped[$resetKey]);
					unset($dataValuesMapped[$resetKey]);
				}
			}
		}
		// merge all the data together, first defaults, then values matched, then supplied
		$mergedData = $defaultValuesMapped;
		$mergedData = $this->_arrayMergeNumericKeys($mergedData, $matchedValuesMapped);
		$mergedData = $this->_arrayMergeNumericKeys($mergedData, $dataValuesMapped);
		if ($encode) {
			foreach($mergedData as $key => & $value) {
				$value = urlencode($value);
			}
		}
		ksort($mergedData);
		$return = @vsprintf($this->_reverse, $mergedData);
		if ($return === false) {
			require_once 'Zend/Controller/Router/Exception.php';
			throw new Zend_Controller_Router_Exception('Cannot assemble. Too few arguments?');
		}
		return $return;
	}
	/**
	 * Return a single parameter of route's defaults
	 *
	 * @param string $name Array key of the parameter
	 * @return string Previously set default
	 */
	public function getDefault($name) {
		if (isset($this->_defaults[$name])) {
			return $this->_defaults[$name];
		}
	}
	/**
	 * Return an array of defaults
	 *
	 * @return array Route defaults
	 */
	public function getDefaults() {
		return $this->_defaults;
	}
	/**
	 * Get all variables which are used by the route
	 *
	 * @return array
	 */
	public function getVariables() {
		$variables = array();
		foreach($this->_map as $key => $value) {
			if (is_numeric($key)) {
				$variables[] = $value;
			} else {
				$variables[] = $key;
			}
		}
		return $variables;
	}
	/**
	 * _arrayMergeNumericKeys() - allows for a strict key (numeric's included) array_merge.
	 * php's array_merge() lacks the ability to merge with numeric keys.
	 *
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	protected function _arrayMergeNumericKeys(Array $array1, Array $array2) {
		$returnArray = $array1;
		foreach($array2 as $array2Index => $array2Value) {
			$returnArray[$array2Index] = $array2Value;
		}
		return $returnArray;
	}
	/**
	 * Set a default translator
	 *
	 * @param  Zend_Translate $translator
	 * @return void
	 */
	public static function setDefaultTranslator(Zend_Translate $translator = null) {
		self::$_defaultTranslator = $translator;
	}
	/**
	 * Get the default translator
	 *
	 * @return Zend_Translate
	 */
	public static function getDefaultTranslator() {
		return self::$_defaultTranslator;
	}
	/**
	 * Set a translator
	 *
	 * @param  Zend_Translate $translator
	 * @return void
	 */
	public function setTranslator(Zend_Translate $translator) {
		$this->_translator = $translator;
	}
	/**
	 * Get the translator
	 *
	 * @throws Zend_Controller_Router_Exception When no translator can be found
	 * @return Zend_Translate
	 */
	public function getTranslator() {
		if ($this->_translator !== null) {
			return $this->_translator;
		} else if (($translator = self::getDefaultTranslator()) !== null) {
			return $translator;
		} else {
			try {
				$translator = Zend_Registry::get('Zend_Translate');
			}
			catch(Zend_Exception $e) {
				$translator = null;
			}
			if ($translator instanceof Zend_Translate) {
				return $translator;
			}
		}
		require_once 'Zend/Controller/Router/Exception.php';
		throw new Zend_Controller_Router_Exception('Could not find a translator');
	}
	/**
	 * Set a default locale
	 *
	 * @param  mixed $locale
	 * @return void
	 */
	public static function setDefaultLocale($locale = null) {
		self::$_defaultLocale = $locale;
	}
	/**
	 * Get the default locale
	 *
	 * @return mixed
	 */
	public static function getDefaultLocale() {
		return self::$_defaultLocale;
	}
	/**
	 * Set a locale
	 *
	 * @param  mixed $locale
	 * @return void
	 */
	public function setLocale($locale) {
		$this->_locale = $locale;
	}
	/**
	 * Get the locale
	 *
	 * @return mixed
	 */
	public function getLocale() {
		if ($this->_locale !== null) {
			return $this->_locale;
		} else if (($locale = self::getDefaultLocale()) !== null) {
			return $locale;
		} else {
			try {
				$locale = Zend_Registry::get('Zend_Locale');
			}
			catch(Zend_Exception $e) {
				$locale = null;
			}
			if ($locale !== null) {
				return $locale;
			}
		}
		return null;
	}
}
