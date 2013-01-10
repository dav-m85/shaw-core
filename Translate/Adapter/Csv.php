<?php

class Shaw_Translate_Adapter_Csv extends Shaw_Translate_Adapter_WritableAdapter {
	protected $_data = array();
	protected $_untranslated = array();
	protected $_files = array();
	/**
	 * Generates the adapter
	 *
	 * @param  array|Zend_Config $options Translation content
	 */
	public function __construct($options = array()) {
		$this->_options['delimiter'] = ";";
		$this->_options['length'] = 0;
		$this->_options['enclosure'] = '"';
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
		parent::__construct($options);
	}
	/**
	 * Load translation data
	 *
	 * // Added: translation files tracking for adding
	 *
	 * @param  string|array  $filename  Filename and full path to the translation source
	 * @param  string        $locale    Locale/Language to add data for, identical with locale identifier,
	 *                                  see Zend_Locale for more information
	 * @param  array         $option    OPTIONAL Options to use
	 * @return array
	 */
	protected function _loadTranslationData($filename, $locale, array $options = array()) {
		$this->_data = array();
		$options = $options + $this->_options;
		$this->_file = @fopen($filename, 'rb');
		if (!$this->_file) {
			require_once 'Zend/Translate/Exception.php';
			throw new Zend_Translate_Exception('Error opening translation file \'' . $filename . '\'.');
		}
		while (($data = fgetcsv($this->_file, $options['length'], $options['delimiter'], $options['enclosure'])) !== false) {
			if (substr($data[0], 0, 1) === '#') {
				continue;
			}
			if (!isset($data[1])) {
				continue;
			}
			if (count($data) == 2) {
				$this->_data[$locale][$data[0]] = $data[1];
			} else {
				$singular = array_shift($data);
				$this->_data[$locale][$singular] = $data;
			}
		}
		if ($this->_file) {
			fclose($this->_file);
		}
		$this->_files[$locale] = $filename;
		return $this->_data;
	}
	protected function _appendUntranslated($message, $locale) {
		if (!array_key_exists($locale, $this->_files)) {
			Shaw_Log::debug('No translation defined for file ' . $locale);
			return false;
		}
		$filename = $this->_files[$locale];
		if (!is_writable($filename)) {
			Shaw_Log::debug('Cannot write to l10n file ' . $filename);
			return false;
		}
		// Just add to csv !
		$this->_file = @fopen($filename, 'a');
		if (!@fwrite($this->_file, sprintf('%s;' . PHP_EOL, $message))) {
			Shaw_Log::debug('Cannot write to file for unknown reason: ' . $filename);
			return false;
		}
		fclose($this->_file);
		return true;
	}
	/**
	 * Shall not be run in multithread environnement !
	 *
	 * (non-PHPdoc)
	 * @see Shaw_Translate_Adapter_WritableAdapter::_updateTranslation()
	 */
	protected function _updateTranslation($message, $locale, $content, $autoInsert = true) {
		if (!array_key_exists($locale, $this->_files)) {
			Shaw_Log::debug('No translation defined for file ' . $locale);
			return false;
		}
		$filename = $this->_files[$locale];
		if (!is_writable($filename)) {
			Shaw_Log::debug('Cannot write to l10n file ' . $filename);
			return false;
		}
		//On modifie la ligne dans le fichier csv de la langue
		$contents = file_get_contents($filename);
		$arr_contents = explode(PHP_EOL, $contents);
		for ($i = 0; $i < count($arr_contents); $i++) {
			$data = explode(';', $arr_contents[$i]);
			if (substr($data[0], 0, 1) === '#') {
				continue;
			}
			if (!isset($data[1])) {
				continue;
			}
			if (count($data) == 2 && $data[0] == $message) {
				$arr_contents[$i] = sprintf('%s;%s', $message, $content);
				$autoInsert = false;
				break;
			}
		}
		if ($autoInsert) {
			$arr_contents[] = sprintf('%s;%s', $message, $content);
		}
		$new_contents = join(PHP_EOL, $arr_contents);
		if (!file_put_contents($filename, $new_contents)) {
			Shaw_Log::debug('Error while writing to file');
			return false;
		}
		return true;
	}
	/**
	 * returns the adapters name
	 *
	 * @return string
	 */
	public function toString() {
		return "Csv";
	}
}
