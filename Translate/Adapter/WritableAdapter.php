<?php

abstract class Shaw_Translate_Adapter_WritableAdapter extends Zend_Translate_Adapter {
	protected $_untranslated = array();
	/**
	 * Array with all options, each adapter can have own additional options
	 *   'clear'           => when true, clears already loaded translations when adding new files
	 *   'content'         => content to translate or file or directory with content
	 *   'disableNotices'  => when true, omits notices from being displayed
	 *   'ignore'          => a prefix for files and directories which are not being added
	 *   'locale'          => the actual set locale to use
	 *   'log'             => a instance of Zend_Log where logs are written to
	 *   'logMessage'      => message to be logged
	 *   'logPriority'     => priority which is used to write the log message
	 *   'logUntranslated' => when true, untranslated messages are not logged
	 *   'reload'          => reloads the cache by reading the content again
	 *   'scan'            => searches for translation files using the LOCALE constants
	 *   'tag'             => tag to use for the cache
	 *
	 * @var array
	 */
	protected $_options = array(
		'clear' => false,
		'content' => null,
		'disableNotices' => false,
		'ignore' => '.',
		'locale' => 'auto',
		'log' => null,
		'logMessage' => "Untranslated message within '%locale%': %message%",
		'logPriority' => 5,
		'logUntranslated' => false,
		'reload' => false,
		'route' => null,
		'scan' => null,
		'tag' => 'Zend_Translate'
	);
	/**
	 * Returns all untranslated translations from this adapter
	 * If no locale is given, the actual language will be used
	 * If 'all' is given the complete translation dictionary will be returned
	 *
	 * @param  string|Zend_Locale $locale (optional) Language to return the messages from
	 * @return array
	 */
	public function getUntranslatedMessageIds($locale = null) {
		if ($locale === 'all') {
			return $this->_untranslated;
		}
		if ((empty($locale) === true) or ($this->isAvailable($locale) === false)) {
			$locale = $this->_options['locale'];
		}
		return $this->_untranslated[(string)$locale];
	}
	/**
	 * Internal function for adding translation data
	 *
	 * This may be a new language or additional data for an existing language
	 * If the options 'clear' is true, then the translation data for the specified
	 * language is replaced and added otherwise
	 *
	 * @see    Zend_Locale
	 * @param  array|Zend_Config $content Translation data to add
	 * @throws Zend_Translate_Exception
	 * @return Zend_Translate_Adapter Provides fluent interface
	 */
	private function _addTranslationData($options = array()) {
		if ($options instanceof Zend_Config) {
			$options = $options->toArray();
		} else if (func_num_args() > 1) {
			$args = func_get_args();
			$options['content'] = array_shift($args);
			if (!empty($args)) {
				$options['locale'] = array_shift($args);
			}
			if (!empty($args)) {
				$options+= array_shift($args);
			}
		}
		if (($options['content'] instanceof Zend_Translate) || ($options['content'] instanceof Zend_Translate_Adapter)) {
			$options['usetranslateadapter'] = true;
			if (!empty($options['locale']) && ($options['locale'] !== 'auto')) {
				$options['content'] = $options['content']->getMessages($options['locale']);
			} else {
				$content = $options['content'];
				$locales = $content->getList();
				foreach($locales as $locale) {
					$options['locale'] = $locale;
					$options['content'] = $content->getMessages($locale);
					$this->_addTranslationData($options);
				}
				return $this;
			}
		}
		try {
			$options['locale'] = Zend_Locale::findLocale($options['locale']);
		}
		catch(Zend_Locale_Exception $e) {
			require_once 'Zend/Translate/Exception.php';
			throw new Zend_Translate_Exception("The given Language '{$options['locale']}' does not exist", 0, $e);
		}
		if ($options['clear'] || !isset($this->_translate[$options['locale']])) {
			$this->_translate[$options['locale']] = array();
		}
		$read = true;
		if (isset(self::$_cache)) {
			$id = 'Zend_Translate_' . md5(serialize($options['content'])) . '_' . $this->toString();
			$temp = self::$_cache->load($id);
			if ($temp) {
				$read = false;
			}
		}
		if ($options['reload']) {
			$read = true;
		}
		if ($read) {
			if (!empty($options['usetranslateadapter'])) {
				$temp = array(
					$options['locale'] => $options['content']
				);
			} else {
				$temp = $this->_loadTranslationData($options['content'], $options['locale'], $options);
			}
		}
		if (empty($temp)) {
			$temp = array();
		}
		// Do something with all these keys !
		$keys = array_keys($temp);
		foreach($keys as $key) {
			if (!isset($this->_translate[$key])) {
				$this->_translate[$key] = array();
			}
			if (!isset($this->_untranslated[$key])) {
				$this->_untranslated[$key] = array();
			}
			if (is_array($temp[$key])) {
				foreach($temp[$key] as $id => $content) {
					if ($content) {
						$this->_translate[$key][$id] = $content;
					} else {
						$this->_untranslated[$key][] = $id;
					}
				}
			}
		}
		if ($this->_automatic === true) {
			$find = new Zend_Locale($options['locale']);
			$browser = $find->getEnvironment() + $find->getBrowser();
			arsort($browser);
			foreach($browser as $language => $quality) {
				if (isset($this->_translate[$language])) {
					$this->_options['locale'] = $language;
					break;
				}
			}
		}
		if (($read) and (isset(self::$_cache))) {
			$id = 'Zend_Translate_' . md5(serialize($options['content'])) . '_' . $this->toString();
			if (self::$_cacheTags) {
				self::$_cache->save($temp, $id, array(
					$this->_options['tag']
				));
			} else {
				self::$_cache->save($temp, $id);
			}
		}
		return $this;
	}
	/**
	 * Add an empty trId inside the adapter media (file, db...)
	 */
	abstract protected function _appendUntranslated($message, $locale);
	/**
	 * Change an existing trId inside the adapter media. Shall exist !
	 *
	 * @param unknown_type $message
	 * @param unknown_type $locale
	 */
	abstract protected function _updateTranslation($message, $locale, $content);
	/**
	 * Logs a message when the log option is set
	 *
	 * @param string $message Message to log
	 * @param String $locale  Locale to log
	 */
	protected function _log($message, $locale) {
		if ($this->_options['logUntranslated']) {
			$message2 = str_replace('%message%', $message, $this->_options['logMessage']);
			$message2 = str_replace('%locale%', $locale, $message2);
			if ($this->_options['log']) {
				$this->_options['log']->log($message2, $this->_options['logPriority']);
			} else {
				if (!$this->_options['disableNotices']) {
					trigger_error($message2, E_USER_NOTICE);
				}
			}
		}
		if ($this->_options['appendUntranslated'] && !in_array($message, $this->_untranslated[$locale])) {
			if ($this->_appendUntranslated($message, $locale)) {
				// Add to cache if not ready yet
				if (isset(self::$_cache)) {
					$id = 'Zend_Translate_' . md5(serialize($this->_options['content'])) . '_' . $this->toString();
					$temp = self::$_cache->load($id);
					$temp[$local][$message] = '';
					if (self::$_cacheTags) {
						self::$_cache->save($temp, $id, array(
							$this->_options['tag']
						));
					} else {
						self::$_cache->save($temp, $id);
					}
				}
				// Add to untranslated messages
				$this->_untranslated[$locale][] = $message;
			}
		}
	}
	public function updateTranslation($message, $locale, $content) {
		if ($this->_updateTranslation($message, $locale, $content)) {
			if (isset(self::$_cache)) {
				$id = 'Zend_Translate_' . md5(serialize($this->_options['content'])) . '_' . $this->toString();
				$temp = self::$_cache->load($id);
				$temp[$local][$message] = $content;
				if (self::$_cacheTags) {
					self::$_cache->save($temp, $id, array(
						$this->_options['tag']
					));
				} else {
					self::$_cache->save($temp, $id);
				}
			}
			Shaw_Log::debug('success');
			$this->_translate[$locale][$message] = $content;
			return true;
		}
		Shaw_Log::debug('mmh');
		return false;
	}
	/**
	 * Add translations
	 *
	 * This may be a new language or additional content for an existing language
	 * If the key 'clear' is true, then translations for the specified
	 * language will be replaced and added otherwise
	 *
	 * @param  array|Zend_Config $options Options and translations to be added
	 * @throws Zend_Translate_Exception
	 * @return Zend_Translate_Adapter Provides fluent interface
	 */
	public function addTranslation($options = array()) {
		if ($options instanceof Zend_Config) {
			$options = $options->toArray();
		} else if (func_num_args() > 1) {
			$args = func_get_args();
			$options = array();
			$options['content'] = array_shift($args);
			if (!empty($args)) {
				$options['locale'] = array_shift($args);
			}
			if (!empty($args)) {
				$opt = array_shift($args);
				$options = array_merge($opt, $options);
			}
		} else if (!is_array($options)) {
			$options = array(
				'content' => $options
			);
		}
		if (!isset($options['content']) || empty($options['content'])) {
			require_once 'Zend/Translate/Exception.php';
			throw new Zend_Translate_Exception("Required option 'content' is missing");
		}
		$originate = null;
		if (!empty($options['locale'])) {
			$originate = (string)$options['locale'];
		}
		if ((array_key_exists('log', $options)) && !($options['log'] instanceof Zend_Log)) {
			require_once 'Zend/Translate/Exception.php';
			throw new Zend_Translate_Exception('Instance of Zend_Log expected for option log');
		}
		try {
			if (!($options['content'] instanceof Zend_Translate) && !($options['content'] instanceof Zend_Translate_Adapter)) {
				if (empty($options['locale'])) {
					$options['locale'] = null;
				}
				$options['locale'] = Zend_Locale::findLocale($options['locale']);
			}
		}
		catch(Zend_Locale_Exception $e) {
			require_once 'Zend/Translate/Exception.php';
			throw new Zend_Translate_Exception("The given Language '{$options['locale']}' does not exist", 0, $e);
		}
		$options = $options + $this->_options;
		if (is_string($options['content']) and is_dir($options['content'])) {
			$options['content'] = realpath($options['content']);
			$prev = '';
			$iterator = new RecursiveIteratorIterator(new RecursiveRegexIterator(new RecursiveDirectoryIterator($options['content'], RecursiveDirectoryIterator::KEY_AS_PATHNAME) , '/^(?!.*(\.svn|\.cvs)).*$/', RecursiveRegexIterator::MATCH) , RecursiveIteratorIterator::SELF_FIRST);
			foreach($iterator as $directory => $info) {
				$file = $info->getFilename();
				if (is_array($options['ignore'])) {
					foreach($options['ignore'] as $key => $ignore) {
						if (strpos($key, 'regex') !== false) {
							if (preg_match($ignore, $directory)) {
								// ignore files matching the given regex from option 'ignore' and all files below
								continue2;
							}
						} else if (strpos($directory, DIRECTORY_SEPARATOR . $ignore) !== false) {
							// ignore files matching first characters from option 'ignore' and all files below
							continue2;
						}
					}
				} else {
					if (strpos($directory, DIRECTORY_SEPARATOR . $options['ignore']) !== false) {
						// ignore files matching first characters from option 'ignore' and all files below
						continue;
					}
				}
				if ($info->isDir()) {
					// pathname as locale
					if (($options['scan'] === self::LOCALE_DIRECTORY) and (Zend_Locale::isLocale($file, true, false))) {
						$options['locale'] = $file;
						$prev = (string)$options['locale'];
					}
				} else if ($info->isFile()) {
					// filename as locale
					if ($options['scan'] === self::LOCALE_FILENAME) {
						$filename = explode('.', $file);
						array_pop($filename);
						$filename = implode('.', $filename);
						if (Zend_Locale::isLocale((string)$filename, true, false)) {
							$options['locale'] = (string)$filename;
						} else {
							$parts = explode('.', $file);
							$parts2 = array();
							foreach($parts as $token) {
								$parts2+= explode('_', $token);
							}
							$parts = array_merge($parts, $parts2);
							$parts2 = array();
							foreach($parts as $token) {
								$parts2+= explode('-', $token);
							}
							$parts = array_merge($parts, $parts2);
							$parts = array_unique($parts);
							$prev = '';
							foreach($parts as $token) {
								if (Zend_Locale::isLocale($token, true, false)) {
									if (strlen($prev) <= strlen($token)) {
										$options['locale'] = $token;
										$prev = $token;
									}
								}
							}
						}
					}
					try {
						$options['content'] = $info->getPathname();
						$this->_addTranslationData($options);
					}
					catch(Zend_Translate_Exception $e) {
						// ignore failed sources while scanning
						
					}
				}
			}
			unset($iterator);
		} else {
			$this->_addTranslationData($options);
		}
		if ((isset($this->_translate[$originate]) === true) and (count($this->_translate[$originate]) > 0)) {
			$this->setLocale($originate);
		}
		return $this;
	}
}
